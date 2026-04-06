# Planning Implementation: Fix & Refactor Organization Master Data Viewer

## 1. Analisis & Identifikasi Masalah
Saat membuka halaman `http://127.0.0.1:8001/master-data` dan memilih tab **Organization**, struktur organisasi tidak tampil dengan rapi / *layout broken*. Setelah menelusuri source code berdasar laporan, masalah bersumber pada:
- **Tampilan Rusak (UI/UX)**: Ditemukan kebocoran raw string *JavaScript* (`childUl.style.display...`) yang nyasar di antara tag HTML di `resources/views/pages/settings/organization.blade.php`. Terdapat juga *unclosed tags* dan struktur tag `<ul>` / `<li>` yang tidak pada tempatnya (looping penutup *tag* berantakan), sehingga CSS *Tree* gagal me-_render_ garis penghubung antar organisasi.
- **Pelanggaran Arsitektur**: Pada `app/Http/Controllers/MasterController.php`, proses pemanggilan *nested relation* Model Database Eager Loading untuk struktur organisasi secara spesifik dilakukan secara *hardcode* di dalam Controller (Line 27+). Menurut aturan baku pada **`GEMINI.md`**, logic pengambilan relasi Model harus selalu mendelegasikan ke **Service Layer**.

---

## 2. Rencana Eksekusi (Langkah demi Langkah)

### A. Refactor Controller ke Service Layer
Buat atau gunakan kembali module Service Layer untuk menarik data Organisasi agar terpisah dari Controller, dan mencegah Fat-Controller.

**1. Membuat Service & Implementasi (`app/Services/MasterDataService`)**
- Buat interface file: `MasterDataService.php`
- Buat file implementasi: `MasterDataServiceImpl.php`

```php
namespace App\Services\MasterDataService;
use App\Models\Director;
use Illuminate\Database\Eloquent\Collection;

class MasterDataServiceImpl implements MasterDataService 
{
    /**
     * Eager-load full org structure: directors -> divisions -> departments -> sections
     */
    public function getOrganizationTree(): Collection 
    {
        return Director::where('status', 'Active')
            ->with([
                'divisions' => fn($q) => $q->where('status', 'Active')->orderBy('name'),
                'divisions.departments' => fn($q) => $q->where('status', 'Active')->orderBy('name'),
                'divisions.departments.sections' => fn($q) => $q->where('status', 'Active')->orderBy('name')
            ])
            ->orderBy('name', 'asc')
            ->get();
    }
}
```

**2. Binding di Provider (`app/Providers/CustomServiceProvider.php`)**
```php
$this->app->bind(
    \App\Services\MasterDataService\MasterDataService::class,
    \App\Services\MasterDataService\MasterDataServiceImpl::class
);
```

**3. Inject di Controller (`app/Http/Controllers/MasterController.php`)**
```php
class MasterController extends Controller
{
    public function __construct(
        protected \App\Services\MasterDataService\MasterDataService $masterDataService
    ) {}

    public function index()
    {
        // ... (data lainnya)
        
        // Panggil melalui Service Layer!
        $directors = $this->masterDataService->getOrganizationTree();

        // ...
    }
}
```

### B. Perbaikan Semantic HTML & CSS Tree (`organization.blade.php`)
Re-struktur kode Blade loop `<ul>` dan `<li>` agar murni *hierarkis (nested)* dan buang sisa teks javascript di baris tengah. Gunakan pola *parent-child* berikut:

```html
<!-- ... style existing dipertahankan ... -->
<div class="org-tree">
    <ul> <!-- ROOT LEVEL (Director) -->
        @forelse($directors as $director)
        <li>
            <div class="org-node" data-toggle>
                <div class="title">{{ $director->name }}</div>
                <div class="meta">{{ $director->code ? $director->code . ' · ' : '' }}{{ $director->status }}</div>
            </div>

            <!-- LEVEL 2 (Divisions) -->
            <ul class="children">
                @forelse($director->divisions as $division)
                <li>
                    <div class="org-node" data-toggle>
                        <div class="title">{{ $division->name }}</div>
                        <div class="meta">{{ $division->code ?? '' }} · {{ $division->status }}</div>
                    </div>

                    <!-- LEVEL 3 (Departments) -->
                    <ul class="children">
                        @forelse($division->departments as $department)
                        <li>
                            <div class="org-node" data-toggle>
                                <div class="title">{{ $department->name }}</div>
                                <div class="meta">{{ $department->code ?? '' }} · {{ $department->status }}</div>
                            </div>

                            <!-- LEVEL 4 (Sections) -->
                            <ul class="children">
                                @forelse($department->sections as $section)
                                <li>
                                    <div class="org-node">
                                        <div class="title">{{ $section->name }}</div>
                                        <div class="meta">{{ $section->code ?? '' }} · {{ $section->status ?? '' }}</div>
                                    </div>
                                </li>
                                @empty
                                <li><div class="org-node"><div class="meta">No sections</div></div></li>
                                @endforelse
                            </ul>
                        </li>
                        @empty
                        <li><div class="org-node"><div class="meta">No departments</div></div></li>
                        @endforelse
                    </ul>
                </li>
                @empty
                <li><div class="org-node"><div class="meta">No divisions</div></div></li>
                @endforelse
            </ul>
        </li>
        @empty
        <li><div class="org-node"><div class="title">No directors found</div></div></li>
        @endforelse
    </ul>
</div>
<!-- ... script js toggle (tetap dipertahankan di bawah) ... -->
```
*(Dengan mengganti `foreach` ke `forelse`, kita tidak perlu membuka-tutup `<li>` dua kali yang sering menyebabkan tag unclosed.)*

---

## 3. Checklist Eksekusi & QA (Untuk Dev/AI Agent)

1. [ ] Buat Folder `app/Services/MasterDataService` dan inisiasi Interface & Implementasinya.
2. [ ] Pindahkan logic DB Query `$directors` dari Controller ke `getOrganizationTree()`.
3. [ ] Register Service baru binding tersebut ke dalam `CustomServiceProvider.php`.
4. [ ] Bersihkan kekacauan *tag structure* dan teks mentah javascript pada `organization.blade.php`.
5. [ ] (QA Test Visual): Akses `http://127.0.0.1:8001/master-data` -> Buka tab *Organization*, pastikan CSS Tree tampil bercabang secara apik ke bawah. Menu dapat di-klik untuk disembunyikan/dimunculkan cabangnya (Toggle).
