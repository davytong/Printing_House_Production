<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintingController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramSetupController;

Route::get('/',              [PrintingController::class, 'index'])->name('printing.index');
Route::post('/books/import', [PrintingController::class, 'importCsv'])->name('books.import');
Route::post('/printing/store', [PrintingController::class, 'store'])->name('printing.store');
Route::get('/report',        [PrintingController::class, 'report'])->name('printing.report');

// Telegram setup & management
Route::prefix('telegram')->name('telegram.')->group(function () {
    Route::get('/',                              [TelegramSetupController::class, 'index'])->name('setup');
    Route::post('/set-webhook',                  [TelegramSetupController::class, 'setWebhook'])->name('set-webhook');
    Route::post('/delete-webhook',               [TelegramSetupController::class, 'deleteWebhook'])->name('delete-webhook');
    Route::post('/poll',                         [TelegramSetupController::class, 'pollNow'])->name('poll');
    Route::post('/add-group',                    [TelegramSetupController::class, 'addGroup'])->name('add-group');
    Route::delete('/groups/{group}',             [TelegramSetupController::class, 'removeGroup'])->name('remove-group');
    Route::post('/groups/{group}/test',          [TelegramSetupController::class, 'testGroup'])->name('test-group');
});
