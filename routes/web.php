<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SasaranStrategisController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\AnggaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);      // Tampilkan dashboard

Route::prefix('sasaran-strategis')->group(function () {
    Route::get('/', [SasaranStrategisController::class, 'index'])->name('sasaran-strategis.index');      // Tampilkan semua produk
    Route::get('/create', [SasaranStrategisController::class, 'create'])->name('sasaran-strategis.create'); // Form tambah produk
    Route::post('/', [SasaranStrategisController::class, 'store'])->name('sasaran-strategis.store');     // Simpan produk baru
    Route::get('/{id}', [SasaranStrategisController::class, 'show'])->name('sasaran-strategis.show');    // Detail produk
    Route::get('/{id}/edit', [SasaranStrategisController::class, 'edit'])->name('sasaran-strategis.edit'); // Form edit produk
    Route::put('/{id}', [SasaranStrategisController::class, 'update'])->name('sasaran-strategis.update'); // Update produk
    Route::delete('/{id}', [SasaranStrategisController::class, 'destroy'])->name('sasaran-strategis.destroy'); // Hapus produk
});

Route::prefix('kpi')->group(function () {
    Route::get('/', [KpiController::class, 'index'])->name('kpi.index');      // Tampilkan semua produk
    Route::get('/create', [KpiController::class, 'create'])->name('kpi.create'); // Form tambah produk
    Route::post('/', [KpiController::class, 'store'])->name('kpi.store');     // Simpan produk baru
    Route::get('/{id}', [KpiController::class, 'show'])->name('kpi.show');    // Detail produk
    Route::get('/{id}/edit', [KpiController::class, 'edit'])->name('kpi.edit'); // Form edit produk
    Route::put('/{id}', [KpiController::class, 'update'])->name('kpi.update'); // Update produk
    Route::delete('/{id}', [KpiController::class, 'destroy'])->name('kpi.destroy'); // Hapus produk
});

Route::prefix('anggaran')->group(function () {
    Route::get('/', [AnggaranController::class, 'index'])->name('anggaran.index');      // Tampilkan semua produk
    Route::get('/create', [AnggaranController::class, 'create'])->name('anggaran.create'); // Form tambah produk
    Route::post('/', [AnggaranController::class, 'store'])->name('anggaran.store');     // Simpan produk baru
    Route::get('/{id}', [AnggaranController::class, 'show'])->name('anggaran.show');    // Detail produk
    Route::get('/{id}/edit', [AnggaranController::class, 'edit'])->name('anggaran.edit'); // Form edit produk
    Route::put('/{id}', [AnggaranController::class, 'update'])->name('anggaran.update'); // Update produk
    Route::delete('/{id}', [AnggaranController::class, 'destroy'])->name('anggaran.destroy'); // Hapus produk
});

Route::get('/company-policy', [CompanyPolicyController::class, 'index'])->name('company-policy.index');
