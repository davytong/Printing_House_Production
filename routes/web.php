<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrintingController;
use App\Http\Controllers\PrintRequestController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TelegramSetupController;
use App\Http\Controllers\Stock\MaterialController;
use App\Http\Controllers\Stock\MovementController;
use App\Http\Controllers\Stock\StockReportController;

// ── Entry Screen (name + position) ────────────────────────
Route::get('/entry', [App\Http\Controllers\EntryController::class, 'show'])->name('entry');
Route::post('/entry', [App\Http\Controllers\EntryController::class, 'login'])->name('entry.login');
Route::post('/logout', [App\Http\Controllers\EntryController::class, 'logout'])->name('entry.logout');

// ── Root → Executive Dashboard ────────────────────────────
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ── Production ────────────────────────────────────────────
Route::get('/production',        [PrintingController::class, 'index'])->name('printing.index');
Route::post('/books/import',     [PrintingController::class, 'importCsv'])->name('books.import');
Route::post('/printing/store',   [PrintingController::class, 'store'])->name('printing.store');
Route::get('/report',            [PrintingController::class, 'report'])->name('printing.report');
// Book CRUD (individual, no CSV)
Route::post('/books',            [PrintingController::class, 'storeBook'])->name('books.store');
Route::put('/books/{book}',      [PrintingController::class, 'updateBook'])->name('books.update');
Route::delete('/books/{book}',   [PrintingController::class, 'destroyBook'])->name('books.destroy');

// ── Print Requests ────────────────────────────────────────
Route::prefix('requests')->name('requests.')->group(function () {
    Route::get('/',                       [PrintRequestController::class, 'index'])->name('index');
    Route::get('/create',                 [PrintRequestController::class, 'create'])->name('create');
    Route::post('/',                      [PrintRequestController::class, 'store'])->name('store');
    Route::get('/{request}',              [PrintRequestController::class, 'show'])->name('show');
    Route::get('/{request}/edit',         [PrintRequestController::class, 'edit'])->name('edit');
    Route::put('/{request}',              [PrintRequestController::class, 'update'])->name('update');
    Route::post('/{request}/approve',     [PrintRequestController::class, 'approve'])->name('approve');
    Route::post('/{request}/reject',      [PrintRequestController::class, 'reject'])->name('reject');
    Route::post('/{request}/status',      [PrintRequestController::class, 'updateStatus'])->name('status');
    Route::delete('/{request}/attachment',[PrintRequestController::class, 'removeAttachment'])->name('remove-attachment');
    Route::delete('/{request}',           [PrintRequestController::class, 'destroy'])->name('destroy');
});

// ── Suppliers ─────────────────────────────────────────────
Route::resource('suppliers', SupplierController::class);

// ── Procurement Requests ──────────────────────────────────
Route::prefix('procurement')->name('procurement.')->group(function () {
    Route::get('/',                              [App\Http\Controllers\ProcurementController::class, 'index'])->name('index');
    Route::get('/create',                        [App\Http\Controllers\ProcurementController::class, 'create'])->name('create');
    Route::post('/',                             [App\Http\Controllers\ProcurementController::class, 'store'])->name('store');
    Route::get('/analytics',                     [App\Http\Controllers\ProcurementController::class, 'analytics'])->name('analytics');
    Route::get('/quick-entry',                   [App\Http\Controllers\ProcurementController::class, 'quickEntry'])->name('quick-entry');
    Route::post('/quick-entry',                  [App\Http\Controllers\ProcurementController::class, 'quickStore'])->name('quick-store');
    Route::get('/{procurement}',                 [App\Http\Controllers\ProcurementController::class, 'show'])->name('show');
    Route::get('/{procurement}/edit',            [App\Http\Controllers\ProcurementController::class, 'edit'])->name('edit');
    Route::put('/{procurement}',                 [App\Http\Controllers\ProcurementController::class, 'update'])->name('update');
    Route::post('/{procurement}/status',         [App\Http\Controllers\ProcurementController::class, 'updateStatus'])->name('status');
    Route::delete('/{procurement}',              [App\Http\Controllers\ProcurementController::class, 'destroy'])->name('destroy');
    Route::delete('/attachment/{attachment}',     [App\Http\Controllers\ProcurementController::class, 'deleteAttachment'])->name('delete-attachment');
});

// ── Purchase Orders ───────────────────────────────────────
Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
    Route::get('/',                              [PurchaseOrderController::class, 'index'])->name('index');
    Route::get('/create',                        [PurchaseOrderController::class, 'create'])->name('create');
    Route::post('/',                             [PurchaseOrderController::class, 'store'])->name('store');
    Route::get('/{purchaseOrder}',               [PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{purchaseOrder}/edit',          [PurchaseOrderController::class, 'edit'])->name('edit');
    Route::put('/{purchaseOrder}',               [PurchaseOrderController::class, 'update'])->name('update');
    Route::post('/{purchaseOrder}/status',       [PurchaseOrderController::class, 'updateStatus'])->name('status');
    Route::post('/{purchaseOrder}/receive',      [PurchaseOrderController::class, 'receive'])->name('receive');
    Route::delete('/{purchaseOrder}',            [PurchaseOrderController::class, 'destroy'])->name('destroy');
});

// ── Inventory ─────────────────────────────────────────────
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/',                              [InventoryController::class, 'index'])->name('index');
    Route::get('/create',                        [InventoryController::class, 'create'])->name('create');
    Route::post('/',                             [InventoryController::class, 'store'])->name('store');
    Route::get('/{inventoryItem}',               [InventoryController::class, 'show'])->name('show');
    Route::get('/{inventoryItem}/edit',          [InventoryController::class, 'edit'])->name('edit');
    Route::put('/{inventoryItem}',               [InventoryController::class, 'update'])->name('update');
    Route::post('/{inventoryItem}/adjust',       [InventoryController::class, 'adjust'])->name('adjust');
    Route::delete('/{inventoryItem}',            [InventoryController::class, 'destroy'])->name('destroy');
});

// ── Machines & Maintenance ────────────────────────────────
Route::prefix('machines')->name('machines.')->group(function () {
    Route::get('/',                              [MachineController::class, 'index'])->name('index');
    Route::get('/create',                        [MachineController::class, 'create'])->name('create');
    Route::post('/',                             [MachineController::class, 'store'])->name('store');
    Route::get('/{machine}',                     [MachineController::class, 'show'])->name('show');
    Route::get('/{machine}/edit',                [MachineController::class, 'edit'])->name('edit');
    Route::put('/{machine}',                     [MachineController::class, 'update'])->name('update');
    Route::delete('/{machine}',                  [MachineController::class, 'destroy'])->name('destroy');
    Route::post('/{machine}/schedule',           [MachineController::class, 'scheduleMaintenance'])->name('schedule');
    Route::post('/maintenance/{schedule}/complete', [MachineController::class, 'completeMaintenance'])->name('complete');
});

// ── Analytics ─────────────────────────────────────────────
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

// ── Stock Management ──────────────────────────────────────
Route::prefix('stock')->name('stock.')->group(function () {
    // Materials CRUD
    Route::resource('materials', MaterialController::class);
    // Daily Update (simple current-qty form)
    Route::get('/movements/daily',        [MovementController::class, 'dailyUpdate'])->name('movements.daily');
    Route::post('/movements/daily',       [MovementController::class, 'dailyStore'])->name('movements.daily-store');
    // Stock Movements
    Route::get('/movements',              [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create',       [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements',             [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/bulk',         [MovementController::class, 'bulkCreate'])->name('movements.bulk');
    Route::post('/movements/bulk',        [MovementController::class, 'bulkStore'])->name('movements.bulk-store');
    // Stock Reports
    Route::get('/reports',                [StockReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create',         [StockReportController::class, 'create'])->name('reports.create');
    Route::post('/reports',               [StockReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{stockReport}',  [StockReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{stockReport}/send', [StockReportController::class, 'sendTelegram'])->name('reports.send');
    Route::delete('/reports/{stockReport}', [StockReportController::class, 'destroy'])->name('reports.destroy');
});

// ── Notifications ─────────────────────────────────────────
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/',                          [NotificationController::class, 'index'])->name('index');
    Route::get('/count',                     [NotificationController::class, 'count'])->name('count');
    Route::post('/mark-all-read',            [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    Route::post('/{notification}/read',      [NotificationController::class, 'markRead'])->name('read');
    Route::delete('/{notification}',         [NotificationController::class, 'destroy'])->name('destroy');
});

// ── Telegram ─────────────────────────────────────────────
Route::prefix('telegram')->name('telegram.')->group(function () {
    Route::get('/',                          [TelegramSetupController::class, 'index'])->name('setup');
    Route::post('/set-webhook',              [TelegramSetupController::class, 'setWebhook'])->name('set-webhook');
    Route::post('/delete-webhook',           [TelegramSetupController::class, 'deleteWebhook'])->name('delete-webhook');
    Route::post('/poll',                     [TelegramSetupController::class, 'pollNow'])->name('poll');
    Route::post('/add-group',                [TelegramSetupController::class, 'addGroup'])->name('add-group');
    Route::delete('/groups/{group}',         [TelegramSetupController::class, 'removeGroup'])->name('remove-group');
    Route::post('/groups/{group}/test',      [TelegramSetupController::class, 'testGroup'])->name('test-group');
    Route::post('/groups/{group}/purpose',   [TelegramSetupController::class, 'updatePurpose'])->name('update-purpose');
});


