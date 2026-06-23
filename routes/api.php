<?php

use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ProductionTaskController;
use Illuminate\Support\Facades\Route;

// Telegram webhook (called by Telegram servers — no CSRF needed, exempt from auth)
Route::post('/telegram/webhook',     [TelegramController::class, 'webhook']);

// Telegram outbound (called by the front-end report page)
Route::post('/telegram/send-image',  [TelegramController::class, 'sendImage'])->name('telegram.send.image');
Route::post('/telegram/send-report', [TelegramController::class, 'sendReport'])->name('telegram.send');

// ── Production Task Scheduling API ────────────────────────
Route::prefix('tasks')->group(function () {
    Route::get('/',              [ProductionTaskController::class, 'timeline']);
    Route::post('/',             [ProductionTaskController::class, 'store']);
    Route::put('/{task}',        [ProductionTaskController::class, 'update']);
    Route::post('/{task}/complete', [ProductionTaskController::class, 'complete']);
    Route::get('/today',         [ProductionTaskController::class, 'todayQueue']);
    Route::get('/upcoming',      [ProductionTaskController::class, 'upcoming']);
    Route::get('/track/{jobName}', [ProductionTaskController::class, 'trackJob']);
});

Route::post('/downtime',             [ProductionTaskController::class, 'logDowntime']);
Route::post('/schedule/recalculate', [ProductionTaskController::class, 'recalculate']);
Route::get('/schedule/report',       [ProductionTaskController::class, 'report']);
Route::get('/schedule/shift-log',    [ProductionTaskController::class, 'shiftLog']);
Route::get('/machines/utilization',  [ProductionTaskController::class, 'machineUtilization']);