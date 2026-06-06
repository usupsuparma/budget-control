# Laravel Continuous Integration Workflow

## Ringkasan

Workflow `.github/workflows/ci.yml` menjalankan pipeline Continuous Integration untuk aplikasi Laravel Budget Control. Workflow lama berasal dari project Node.js/PostgreSQL dan sudah diganti menjadi pipeline Laravel/PHP/MySQL untuk validasi backend.

## Trigger

Workflow berjalan pada:

- `pull_request` ke branch `main`
- `push` ke branch `main`

Workflow memakai concurrency berdasarkan Git ref agar run lama dibatalkan ketika ada commit baru pada branch yang sama.

## Runtime

CI menggunakan:

- Ubuntu latest
- PHP 8.2
- MySQL 8.0 service

PHP extensions yang disiapkan:

- `bcmath`
- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `gd`
- `intl`
- `mbstring`
- `pdo_mysql`
- `tokenizer`
- `xml`
- `zip`

## Environment Testing

Database testing dibuat dari service MySQL:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=budget_control_testing
DB_USERNAME=budget_control
DB_PASSWORD=secret
```

Environment runtime juga memaksa driver non-persistent untuk CI:

- `CACHE_STORE=array`
- `MAIL_MAILER=array`
- `QUEUE_CONNECTION=sync`
- `SESSION_DRIVER=array`

## Pipeline Steps

1. Checkout repository.
2. Setup PHP.
3. Cache Composer dependencies.
4. Install Composer dependencies.
5. Prepare Laravel `.env` and app key.
6. Run database migrations.
7. Run PHP tests via `composer test`.

CI ini sengaja tidak menjalankan `npm install` atau `npm run build` karena targetnya hanya test PHP/Laravel.

## Operational Caveat

Saat dokumentasi ini dibuat, `composer test` masih gagal sebelum test berjalan karena `tests/Unit/Services/DashboardServiceTest.php` memakai fungsi Pest `uses()`, sementara dependency Pest tidak ada di `composer.json`.

Pilihan perbaikannya:

- ubah test tersebut ke style PHPUnit, atau
- tambahkan Pest sebagai dev dependency secara eksplisit.

CI tetap menjalankan `composer test` agar masalah test suite tidak tersembunyi.
