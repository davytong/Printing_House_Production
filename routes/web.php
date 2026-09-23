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
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Stock\MaterialController;
use App\Http\Controllers\Stock\MovementController;
use App\Http\Controllers\Stock\StockReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\DailyReportTrackingController;
use App\Http\Controllers\SearchController;

// ── Entry Screen (name + position) ────────────────────────
Route::get('/entry', [App\Http\Controllers\EntryController::class, 'show'])->name('entry');
Route::post('/entry', [App\Http\Controllers\EntryController::class, 'login'])->name('entry.login');
Route::post('/logout', [App\Http\Controllers\EntryController::class, 'logout'])->name('entry.logout');

// ── Language Switcher ────────────────────────────────────
Route::get('/lang/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');

// ── Session Keep-Alive / CSRF Refresh ─────────────────────
Route::get('/ping', fn() => response()->json(['ok' => true, 'csrf_token' => csrf_token()]))->name('ping');

// ── Root → Executive Dashboard ────────────────────────────
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// ── Production ────────────────────────────────────────────
Route::get('/production',        [PrintingController::class, 'index'])->name('printing.index');
Route::post('/books/import',     [PrintingController::class, 'importCsv'])->name('books.import');
Route::post('/printing/store',   [PrintingController::class, 'store'])->name('printing.store');
Route::delete('/printing/daily-print/{print}', [PrintingController::class, 'destroyDailyPrint'])->name('printing.daily-print.destroy');
Route::post('/printing/batch',   [PrintingController::class, 'batchUpdate'])->name('printing.batch');
Route::post('/printing/new-batch', [PrintingController::class, 'startNewBatch'])->name('printing.new-batch');
Route::get('/printing/batch/{batch}', [PrintingController::class, 'showBatch'])->name('printing.batch-history');
Route::post('/printing/batch/{batch}/restore', [PrintingController::class, 'restoreBatch'])->name('printing.batch-restore');
Route::delete('/printing/batch/{batch}', [PrintingController::class, 'deleteBatch'])->name('printing.batch-delete');
Route::get('/report',            [PrintingController::class, 'report'])->name('printing.report');
Route::get('/report/daily',      [PrintingController::class, 'generateDailyReport'])->name('printing.daily-report');
Route::post('/report/telegram',  [PrintingController::class, 'sendDailyReportTelegram'])->name('printing.send-telegram');
Route::get('/production/export', [PrintingController::class, 'exportExcel'])->name('printing.export');
// Book CRUD (individual, no CSV)
Route::post('/books',            [PrintingController::class, 'storeBook'])->name('books.store');
Route::put('/books/{book}',      [PrintingController::class, 'updateBook'])->name('books.update');
Route::delete('/books/{book}',   [PrintingController::class, 'destroyBook'])->name('books.destroy');

// ── Print Requests ────────────────────────────────────────
Route::prefix('requests')->name('requests.')->group(function () {
    Route::get('/',                       [PrintRequestController::class, 'index'])->name('index');
    Route::get('/create',                 [PrintRequestController::class, 'create'])->name('create');
    Route::post('/',                      [PrintRequestController::class, 'store'])->name('store');
    Route::get('/{printRequest}',         [PrintRequestController::class, 'show'])->name('show');
    Route::get('/{printRequest}/edit',    [PrintRequestController::class, 'edit'])->name('edit');
    Route::put('/{printRequest}',         [PrintRequestController::class, 'update'])->name('update');
    Route::post('/{printRequest}/approve',[PrintRequestController::class, 'approve'])->name('approve');
    Route::post('/{printRequest}/reject', [PrintRequestController::class, 'reject'])->name('reject');
    Route::post('/{printRequest}/status', [PrintRequestController::class, 'updateStatus'])->name('status');
    Route::delete('/{printRequest}/attachment',[PrintRequestController::class, 'removeAttachment'])->name('remove-attachment');
    Route::delete('/{printRequest}',      [PrintRequestController::class, 'destroy'])->name('destroy');
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
    Route::get('/export',                        [PurchaseOrderController::class, 'exportExcel'])->name('export');
    Route::get('/{purchaseOrder}',               [PurchaseOrderController::class, 'show'])->name('show');
    Route::get('/{purchaseOrder}/edit',          [PurchaseOrderController::class, 'edit'])->name('edit');
    Route::put('/{purchaseOrder}',               [PurchaseOrderController::class, 'update'])->name('update');
    Route::post('/{purchaseOrder}/status',       [PurchaseOrderController::class, 'updateStatus'])->name('status');
    Route::post('/{purchaseOrder}/receive',      [PurchaseOrderController::class, 'receive'])->name('receive');
    Route::post('/{purchaseOrder}/attachments',  [PurchaseOrderController::class, 'addAttachments'])->name('attachments.add');
    Route::delete('/{purchaseOrder}/attachments',[PurchaseOrderController::class, 'removeAttachment'])->name('attachments.remove');
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

// ── Production Schedule ───────────────────────────────────
Route::prefix('schedule')->name('schedule.')->group(function () {
    // Schedule Routes
    Route::get('/',               [ScheduleController::class, 'index'])->name('index');
    Route::post('/store',         [ScheduleController::class, 'store'])->name('store');
    Route::post('/bulk',          [ScheduleController::class, 'bulkSave'])->name('bulk');
    Route::get('/export',         [ScheduleController::class, 'exportCalendar'])->name('export');
    Route::post('/export-telegram', [ScheduleController::class, 'sendExportTelegram'])->name('export-telegram');
    Route::post('/alert',         [ScheduleController::class, 'sendTelegramAlert'])->name('alert');
    Route::post('/copy',          [ScheduleController::class, 'copyToMonth'])->name('copy');
    Route::post('/move',          [ScheduleController::class, 'moveCell'])->name('move');
    Route::post('/clear',         [ScheduleController::class, 'clearMonth'])->name('clear');

    // Weekly Schedule Report
    Route::get('/report/weekly', [ScheduleController::class, 'weeklyReport'])->name('weekly-report');
    Route::get('/report/weekly/json', [ScheduleController::class, 'generateWeeklyReportJson'])->name('weekly-report.json');
    Route::post('/report/weekly/telegram', [ScheduleController::class, 'sendWeeklyReportTelegram'])->name('send-weekly-telegram');
    Route::post('/urgent',        [ScheduleController::class, 'urgentTask'])->name('urgent');
    Route::post('/downtime',      [ScheduleController::class, 'machineDowntime'])->name('downtime');
    Route::get('/delay-report',   [ScheduleController::class, 'delayReport'])->name('delay-report');
    Route::get('/delay-json',     [ScheduleController::class, 'delayReportJson'])->name('delay-json');
    Route::delete('/delay-log/{id}', [ScheduleController::class, 'destroyDelayLog'])->name('delay-log.delete');
    // Smart Planning & Tracking Routes
    Route::post('/plan/preview',           [ScheduleController::class, 'previewPlan'])->name('plan.preview');
    Route::post('/plan/confirm',           [ScheduleController::class, 'confirmPlan'])->name('plan.confirm');
    Route::post('/plan/simulate',          [ScheduleController::class, 'simulatePlan'])->name('plan.simulate');
    Route::get('/plan/resources',          [ScheduleController::class, 'getPlanResources'])->name('plan.resources');
    Route::post('/actual/record',          [ScheduleController::class, 'recordActualOutput'])->name('actual.record');
    Route::post('/plan/reschedule-suggest',[ScheduleController::class, 'rescheduleSuggestion'])->name('plan.reschedule-suggest');
    Route::post('/plan/reschedule-apply',  [ScheduleController::class, 'applyReschedule'])->name('plan.reschedule-apply');
    Route::post('/cell/lock',              [ScheduleController::class, 'toggleLock'])->name('cell.lock');
    Route::post('/bulk-import',            [ScheduleController::class, 'bulkImport'])->name('bulk-import');
    Route::post('/templates',              [ScheduleController::class, 'saveTemplate'])->name('templates.save');
});

// ── Analytics ─────────────────────────────────────────────
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

// ── Production Tasks (advanced scheduling) ────────────────
Route::get('/tasks', [App\Http\Controllers\ProductionTaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/kanban', [App\Http\Controllers\ProductionTaskController::class, 'kanban'])->name('tasks.kanban');

// ── Stock Management ──────────────────────────────────────
Route::prefix('stock')->name('stock.')->group(function () {
    // Low Stock Alert & Leader Notification Routes
    Route::post('/low-stock/check',                  [App\Http\Controllers\Stock\LowStockNotificationController::class, 'check'])->name('low-stock.check');
    Route::post('/low-stock/preview',                [App\Http\Controllers\Stock\LowStockNotificationController::class, 'preview'])->name('low-stock.preview');
    Route::post('/low-stock/send',                   [App\Http\Controllers\Stock\LowStockNotificationController::class, 'send'])->name('low-stock.send');
    Route::get('/low-stock/history',                [App\Http\Controllers\Stock\LowStockNotificationController::class, 'history'])->name('low-stock.history');
    Route::get('/low-stock/notifications/{id}',     [App\Http\Controllers\Stock\LowStockNotificationController::class, 'show'])->name('low-stock.show');
    Route::post('/low-stock/notifications/{id}/retry', [App\Http\Controllers\Stock\LowStockNotificationController::class, 'retry'])->name('low-stock.retry');
    Route::get('/low-stock/settings',               [App\Http\Controllers\Stock\LowStockNotificationController::class, 'settings'])->name('low-stock.settings');
    Route::post('/low-stock/settings',              [App\Http\Controllers\Stock\LowStockNotificationController::class, 'updateSettings'])->name('low-stock.update-settings');

    // Materials CRUD
    Route::post('/materials/alert',       [MaterialController::class, 'sendLowStockAlert'])->name('materials.alert');
    Route::resource('materials', MaterialController::class);
    Route::get('/materials-export',       [MaterialController::class, 'exportExcel'])->name('materials.export');
    // Daily Update (simple current-qty form)
    Route::get('/movements/daily',        [MovementController::class, 'dailyUpdate'])->name('movements.daily');
    Route::get('/movements/daily-stats',  [MovementController::class, 'dailyStats'])->name('movements.daily-stats');
    Route::post('/movements/daily',       [MovementController::class, 'dailyStore'])->name('movements.daily-store');
    // Stock Movements
    Route::get('/movements',              [MovementController::class, 'index'])->name('movements.index');
    Route::get('/movements/create',       [MovementController::class, 'create'])->name('movements.create');
    Route::post('/movements',             [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/export',       [MovementController::class, 'exportExcel'])->name('movements.export');
    Route::get('/movements/person-report', [MovementController::class, 'personReport'])->name('person-report');
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
    Route::post('/clear-all',                [NotificationController::class, 'clearAll'])->name('clear-all');
    Route::post('/{notification}/read',      [NotificationController::class, 'markRead'])->name('read');
    Route::delete('/{notification}',         [NotificationController::class, 'destroy'])->name('destroy');
});

// ── Telegram Mini App ─────────────────────────────────────
Route::get('/telegram/app', [App\Http\Controllers\TelegramMiniAppController::class, 'index'])->name('telegram.mini-app');

// ── Telegram ─────────────────────────────────────────────
Route::prefix('telegram')->name('telegram.')->middleware('admin')->group(function () {
    Route::get('/',                          [TelegramSetupController::class, 'index'])->name('setup');
    Route::post('/set-webhook',              [TelegramSetupController::class, 'setWebhook'])->name('set-webhook');
    Route::post('/set-menu-button',          [TelegramSetupController::class, 'setMenuButton'])->name('set-menu-button');
    Route::post('/delete-webhook',           [TelegramSetupController::class, 'deleteWebhook'])->name('delete-webhook');
    Route::post('/poll',                     [TelegramSetupController::class, 'pollNow'])->name('poll');
    Route::post('/add-group',                [TelegramSetupController::class, 'addGroup'])->name('add-group');
    Route::delete('/groups/{group}',         [TelegramSetupController::class, 'removeGroup'])->name('remove-group');
    Route::post('/groups/{group}/test',      [TelegramSetupController::class, 'testGroup'])->name('test-group');
    Route::post('/groups/{group}/purpose',   [TelegramSetupController::class, 'updatePurpose'])->name('update-purpose');
    Route::post('/alert-template',            [TelegramSetupController::class, 'saveAlertTemplate'])->name('alert-template');
    Route::post('/alert-template/reset',      [TelegramSetupController::class, 'resetAlertTemplate'])->name('alert-template-reset');
    Route::post('/alert-test',                [TelegramSetupController::class, 'sendTestAlert'])->name('alert-test');
    Route::post('/alert-config',              [TelegramSetupController::class, 'saveAlertConfig'])->name('alert-config');
    Route::post('/daily-usage-config',        [TelegramSetupController::class, 'saveDailyUsageConfig'])->name('daily-usage-config');
    Route::post('/stock-out-config',          [TelegramSetupController::class, 'saveStockOutConfig'])->name('stock-out-config');
    Route::post('/daily-report-template',     [TelegramSetupController::class, 'saveDailyReportTemplate'])->name('daily-report-template');
    Route::post('/daily-report-template/reset', [TelegramSetupController::class, 'resetDailyReportTemplate'])->name('daily-report-template-reset');
    Route::post('/stock-out-template',        [TelegramSetupController::class, 'saveStockOutTemplate'])->name('stock-out-template');
    Route::post('/stock-out-template/reset',  [TelegramSetupController::class, 'resetStockOutTemplate'])->name('stock-out-template-reset');
    Route::post('/category-labels',           [TelegramSetupController::class, 'saveCategoryLabels'])->name('category-labels');
    Route::post('/category-labels/reset',     [TelegramSetupController::class, 'resetCategoryLabels'])->name('category-labels-reset');
    Route::post('/item-name-format',          [TelegramSetupController::class, 'saveItemNameFormat'])->name('item-name-format');
});

// ── Settings & Profile ────────────────────────────────────
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings/report-tracking', [SettingsController::class, 'saveReportSettings'])->name('settings.report-tracking.save');
Route::post('/settings/report-tracking/check', [DailyReportTrackingController::class, 'checkNow'])->name('settings.report-tracking.check');
Route::post('/settings/report-tracking/sync', [DailyReportTrackingController::class, 'syncToday'])->name('settings.report-tracking.sync');
Route::post('/settings/report-tracking/reset-alerts', [DailyReportTrackingController::class, 'resetTodayAlerts'])->name('settings.report-tracking.reset-alerts');
Route::post('/settings/report-tracking/fetch-users', [DailyReportTrackingController::class, 'fetchTelegramUsers'])->name('settings.report-tracking.fetch-users');

// ── Enterprise Audit Trail ────────────────────────────────
Route::get('/audit-logs',        [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index');
Route::get('/audit-logs/export', [App\Http\Controllers\AuditLogController::class, 'exportCsv'])->name('audit.export');
Route::post('/audit-logs/clean', [App\Http\Controllers\AuditLogController::class, 'cleanOld'])->name('audit.clean');



// ── Daily Production Report Tracking ─────────────────────


Route::prefix('reports/tracking')->name('reports.tracking.')->group(function () {
    Route::get('/',                          [DailyReportTrackingController::class, 'index'])->name('index');
    Route::post('/check-now',                [DailyReportTrackingController::class, 'checkNow'])->name('check-now');
    Route::post('/send-summary',             [DailyReportTrackingController::class, 'sendSummaryNow'])->name('send-summary');
    Route::get('/submission/{submission}',  [DailyReportTrackingController::class, 'getSubmission'])->name('submission');
});

Route::prefix('reports/requirements')->name('reports.requirements.')->group(function () {
    Route::get('/',                          [DailyReportTrackingController::class, 'requirements'])->name('index');
    Route::post('/fetch-users',              [DailyReportTrackingController::class, 'fetchTelegramUsers'])->name('fetch-users');
    Route::post('/',                         [DailyReportTrackingController::class, 'storeRequirement'])->name('store');
    Route::put('/{requirement}',             [DailyReportTrackingController::class, 'updateRequirement'])->name('update');
    Route::patch('/{requirement}/toggle',    [DailyReportTrackingController::class, 'toggleRequirement'])->name('toggle');
    Route::delete('/{requirement}',          [DailyReportTrackingController::class, 'destroyRequirement'])->name('destroy');
});
