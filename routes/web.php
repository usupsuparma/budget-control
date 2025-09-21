<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SasaranStrategisController;

Route::get('/', function () {
    return view('pages.dashboard');
});
// Route::middleware(['guest'])->group(function () {
//     Route::get('/login', [AuthController::class, 'login'])->name('login');
//     Route::post('/login', [AuthController::class, 'authenticating']);
// });

Route::get('{any}', [DashboardController::class, 'index'])->where('any', '.*'); // Catch-all route for the dashboard.

Route::prefix('sasaran-strategis')->group(function () {
    Route::get('/', [SasaranStrategisController::class, 'index'])->name('SasaranStrategis.index');
    Route::post('/', [SasaranStrategisController::class, 'store'])->name('SasaranStrategis.store');
    Route::put('/{id}', [SasaranStrategisController::class, 'update'])->name('SasaranStrategis.update');
    Route::delete('/{id}', [SasaranStrategisController::class, 'destroy'])->name('SasaranStrategis.destroy');
});
