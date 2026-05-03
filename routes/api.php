<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Webhook to update transaction status to COMPLETED (4)
    // URL: /api/v1/webhook/transaction/complete
    Route::post('/webhook/transaction/complete', [WebhookController::class, 'updateTransactionCompleted'])
        ->name('api.v1.webhook.transaction.complete');
});
