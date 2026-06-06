# Budget Code & Stock Code — Server-Side Search Dropdown

## Latar Belakang

Sebelumnya, dropdown Budget Code dan Stock Code memuat **seluruh data** (puluhan ribu record) sekaligus ke DOM saat modal dibuka. Ini menyebabkan:

- Modal lambat terbuka (blocking render)
- Memory browser membengkak (20k `<option>` nodes)
- UX buruk karena data terlalu banyak di-scroll

Fitur ini mengganti pendekatan tersebut menjadi **server-side AJAX search dengan infinite scroll** untuk kedua dropdown, identik polanya.

---

## Arsitektur Solusi

### Alur Data

```
User klik dropdown
       │
       ▼
containerOuter click listener (Fallback)
       │
       ▼ (choices.isOpen === true)
_triggerLoad() / showDropdown event
       │
       ▼
fetchBudgetCodes("", page=1, replace=true)
       │
       ▼
GET /budget-user/budget-codes/search?q=&limit=10&page=1
       │
       ▼
BudgetUserController::searchBudgetCodes()
       │
       ▼
BudgetUserServiceImpl::searchBudgetCodes()
       │
       ▼
SELECT … FROM budget_codes LIMIT 10 OFFSET 0
       │
       ▼
{ success, data[], has_more, page, total }
       │
       ▼
choices.setChoices(newChoices, "value", "label", replace=true)
       │
       ▼
List muncul di dropdown
```

### Infinite Scroll

```
User scroll ke bawah (threshold 60px dari bottom)
       │
       ▼
scroll listener pada choices.choiceList.element
       │
       ▼
_bcPage++
fetchBudgetCodes(_bcQuery, _bcPage, replace=false)
       │
       ▼
GET /budget-user/budget-codes/search?q=…&limit=10&page=N
       │
       ▼
choices.setChoices(newChoices, replace=false)  ← APPEND, tidak replace
```

### Ketik untuk Filter

```
User mengetik di search input
       │
       ▼
Choices.js "search" custom event
       │
       ▼
_budgetCodeSearchHandler (debounce 300ms)
       │
       ▼
_bcQuery = query, _bcPage = 1
fetchBudgetCodes(_bcQuery, 1, replace=true)
       │
       ▼
GET /budget-user/budget-codes/search?q=<keyword>&limit=10&page=1
       │
       ▼
List diganti (replace=true) dengan hasil filter
```

---

## File yang Terlibat

### Backend

| File                                                       | Perubahan                                                                                                                                            |
| ---------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Services/BudgetUserService/BudgetUserService.php`     | Signature `searchBudgetCodes` diubah: tambah parameter `$page`, return value tambah `has_more`, `page`, `total`                                      |
| `app/Services/BudgetUserService/BudgetUserServiceImpl.php` | Implementasi pagination menggunakan `offset/limit`, query kosong tidak memfilter (tampilkan semua)                                                   |
| `app/Http/Controllers/BudgetUserController.php`            | Method `searchBudgetCodes`: tambah parameter `$page`, fix null-coalescing untuk `$query` (guard dari Laravel `ConvertEmptyStringsToNull` middleware) |

### Frontend

| File                              | Perubahan                                                                                                                        |
| --------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| `public/assets/js/budget-user.js` | Fungsi `_initBudgetCodeSearchDropdown` ditulis ulang dengan pola infinite scroll (identik dengan `_initStockCodeSearchDropdown`) |

---

## Detail Implementasi Backend

### Interface (`BudgetUserService.php`)

```php
public function searchBudgetCodes(string $query, int $limit = 10, int $page = 1): array;
```

Return:

```php
[
    'success'  => true,
    'data'     => Collection,  // 10 item per page
    'has_more' => bool,        // apakah masih ada halaman berikutnya
    'page'     => int,         // halaman saat ini
    'total'    => int,         // total record yang cocok
]
```

### Service Implementation (`BudgetUserServiceImpl.php`)

```php
public function searchBudgetCodes(string $query, int $limit = 10, int $page = 1): array
{
    $deptCodes = session('department_codes', []);
    $query     = trim($query);

    $dbQuery = BudgetCode::active()
        ->select('id', 'budget_code', 'name', 'inchargeCode')
        ->where(function ($q) use ($query) {
            if ($query !== '') {
                $q->where('budget_code', 'LIKE', "%{$query}%")
                    ->orWhere('name', 'LIKE', "%{$query}%");
            }
            // query kosong = tidak ada filter WHERE (tampilkan semua)
        })
        ->orderBy('budget_code');

    if (!empty($deptCodes)) {
        $dbQuery->whereIn('inchargeCode', $deptCodes);
    }

    $total  = $dbQuery->count();
    $offset = ($page - 1) * $limit;
    $data   = $dbQuery->offset($offset)->limit($limit)->get();

    return [
        'success'  => true,
        'data'     => $data,
        'has_more' => ($offset + $limit) < $total,
        'page'     => $page,
        'total'    => $total,
    ];
}
```

### Controller (`BudgetUserController.php`)

```php
public function searchBudgetCodes(Request $request)
{
    try {
        $query  = $request->input('q') ?? '';   // null-safe: ConvertEmptyStringsToNull middleware
        $limit  = min((int) $request->input('limit', 10), 100);
        $page   = max(1, (int) $request->input('page', 1));
        $result = $this->budgetUserService->searchBudgetCodes($query, $limit, $page);

        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success'  => false,
            'message'  => 'Failed to search budget codes',
            'data'     => [],
            'has_more' => false,
        ], 500);
    }
}
```

> **Catatan penting:** Gunakan `$request->input('q') ?? ''` bukan `$request->input('q', '')`.
> Laravel middleware `ConvertEmptyStringsToNull` mengubah `q=` (string kosong) menjadi `null` **sebelum** sampai ke controller.
> Default value di `input('q', '')` hanya berlaku jika key **tidak ada sama sekali**, bukan jika nilainya `null`.

---

## Detail Implementasi Frontend

### State Internal per Instance

```javascript
let _bcQuery = ""; // query aktif saat ini
let _bcPage = 1; // halaman aktif
let _bcLoading = false; // guard agar tidak double-fetch
let _bcHasMore = true; // apakah masih ada data berikutnya
let _bcScrollBound = false; // agar scroll listener hanya di-bind sekali
```

### Trigger Pertama Kali (saat user klik dropdown)

```javascript
// Primary: Choices.js custom event
select.addEventListener("showDropdown", function () {
    _triggerLoad();
});

// Fallback: click pada containerOuter (lebih reliable pada klik pertama)
containerEl.addEventListener("click", function () {
    if (choices.isOpen) {
        _triggerLoad();
    }
});
```

> **Mengapa dua trigger?**
> `showDropdown` event dari Choices.js kadang tidak firing secara konsisten di klik pertama (terutama saat modal baru dibuka / focus state belum stabil). Click fallback pada `containerOuter` memastikan fetch selalu terjadi.

### Auto-fill Cost Center

Saat user memilih budget code, field **Cost Center** otomatis terisi dari cache:

```javascript
const _budgetCodeChangeHandler = function (e) {
    const val = e.detail?.value ?? this.value;
    if (!val) return;
    const cached = _budgetCodeCache.get(val);
    const inchargeCode = cached ? cached.inchargeCode || "" : "";

    $("#costCenter").val(inchargeCode);
    const costCenterEl = document.getElementById("costCenter");
    if (costCenterEl?.choicesInstance) {
        costCenterEl.choicesInstance.setChoiceByValue(inchargeCode);
    }
};
```

Cache `_budgetCodeCache` (Map) diisi setiap kali ada response dari server, sehingga auto-fill tidak memerlukan request tambahan.

### Pre-select pada Mode Edit

```javascript
// di populateItemForm()
if (item.budget_code) {
    const bcRelation = item.budget_code_relation;
    const bcLabel = bcRelation
        ? item.budget_code + " - " + bcRelation.name
        : item.budget_code;
    _initBudgetCodeSearchDropdown(item.budget_code, bcLabel);
    if (bcRelation) {
        _budgetCodeCache.set(item.budget_code, bcRelation);
    }
}
```

Pada mode edit, `_initBudgetCodeSearchDropdown(preselectedCode, preselectedLabel)` menyisipkan satu `<option>` terpilih sebelum Choices.js diinisialisasi, sehingga nilai lama tetap tampil meskipun belum ada fetch.

---

## Bug yang Diperbaiki

### 1. `null` query menyebabkan TypeError

**Error:** `searchStockCodes(): Argument #1 ($query) must be of type string, null given`

**Root cause:** Laravel `ConvertEmptyStringsToNull` middleware mengubah `q=""` → `null`. Ketika `null` diteruskan ke service yang type-hint `string`, terjadi `TypeError`.

**Fix:** `$request->input('q') ?? ''` menggantikan `$request->input('q', '')`.

---

### 2. Dropdown kosong saat pertama kali diklik

**Gejala:** User klik dropdown budget code, list tidak muncul.

**Root cause (iterasi 1):** `showDropdown` event tidak reliable di klik pertama.

**Fix (iterasi 1):** Pre-fetch `fetchBudgetCodes("", 1, true)` saat init.

**Root cause (iterasi 2):** Pre-fetch menyebabkan `_bcLoading = true`. Ketika `showDropdown` fire, guard `if (_bcLoading) return` memblokir fetch.

**Fix (iterasi 2):** Hapus pre-fetch. Tambah click listener pada `containerOuter.element` sebagai fallback yang reliable, dilindungi `if (choices.isOpen)`.

---

### 3. Dropdown hilang setelah klik Reset

**Gejala:** User klik tombol "Reset" di modal, lalu budget code / stock code dropdown menjadi plain `<select>` biasa tanpa Choices.js — tidak ada list saat diklik.

**Root cause:** `resetItemForm()` hanya melakukan `destroy()` tanpa re-init.

**Fix:** Tambahkan re-init di akhir `resetItemForm()`:

```javascript
// Re-init budget code & stock code dropdowns
_initBudgetCodeSearchDropdown(null, null);
_initStockCodeSearchDropdown(null, null);
```

---

## Konfigurasi Choices.js yang Digunakan

```javascript
new Choices(select, {
    searchEnabled: true,
    searchChoices: false, // PENTING: matikan client-side filter, server yang filter
    searchFloor: 1, // mulai search dari 1 karakter
    searchResultLimit: 10, // batas tampil (server juga limit 10)
    searchPlaceholderValue: "Search budget code...",
    itemSelectText: "",
    noResultsText: "No results found.",
    noChoicesText: "Loading...",
    shouldSort: false, // urutan dari server (ORDER BY budget_code)
    removeItemButton: false,
});
```

---

## Perbedaan Budget Code vs Stock Code

| Aspek                | Budget Code                         | Stock Code                                  |
| -------------------- | ----------------------------------- | ------------------------------------------- |
| Endpoint             | `/budget-user/budget-codes/search`  | `/budget-user/stock-codes/search`           |
| Filter session       | `department_codes` → `inchargeCode` | `department_codes` → via `budget_code` join |
| Auto-fill pada pilih | Cost Center (`inchargeCode`)        | Product Line + Unit                         |
| Cache variable       | `_budgetCodeCache`                  | `_stockCodeCache`                           |
| Scroll state prefix  | `_bc*`                              | `_sc*`                                      |

---

## Route yang Terlibat

```php
// routes/web.php (di dalam prefix 'budget-user')
Route::get('/budget-codes/search', [BudgetUserController::class, 'searchBudgetCodes'])
    ->name('budget-user.budget-codes.search');

Route::get('/stock-codes/search', [BudgetUserController::class, 'searchStockCodes'])
    ->name('budget-user.stock-codes.search');
```
