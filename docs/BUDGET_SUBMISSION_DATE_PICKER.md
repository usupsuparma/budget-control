# Budget Submission Date Picker

## Ringkasan

Modal `Add Budget Movement` pada halaman `resources/views/pages/budget/budget-submission.blade.php` memiliki input tanggal `#submission_date`.

Sebelumnya, date picker browser hanya terbuka ketika user menekan ikon kalender bawaan input. Setelah perubahan ini, klik pada area input tanggal juga akan membuka date picker.

## Behavior

Saat user klik input:

```text
#submission_date -> showPicker()
```

Jika browser belum mendukung `HTMLInputElement.showPicker()`, input tetap diberi fokus sebagai fallback.

## File Yang Terlibat

- `resources/views/pages/budget/budget-submission.blade.php`

## Catatan Implementasi

- `#submission_date` diberi `cursor: pointer` agar terlihat bisa diklik.
- Handler diinisialisasi melalui `initSubmissionDatePicker()` saat `DOMContentLoaded`.
- Handler tidak berjalan jika input sedang `disabled` atau `readOnly`.
