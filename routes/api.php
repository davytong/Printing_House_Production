<?php

use App\Http\Controllers\TelegramController;
use Illuminate\Support\Facades\Route;

// Telegram webhook (called by Telegram servers — no CSRF needed, exempt from auth)
Route::post('/telegram/webhook',     [TelegramController::class, 'webhook']);

// Telegram outbound (called by the front-end report page)
Route::post('/telegram/send-image',  [TelegramController::class, 'sendImage'])->name('telegram.send.image');
Route::post('/telegram/send-report', [TelegramController::class, 'sendReport'])->name('telegram.send');
