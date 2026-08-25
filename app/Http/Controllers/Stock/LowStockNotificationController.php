<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\LowStockNotification;
use App\Models\Material;
use App\Models\Setting;
use App\Models\TelegramGroup;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LowStockNotificationController extends Controller
{
    public function __construct(
        private AlertService $alertService
    ) {}

    /**
     * API: Check stock items submitted in daily update and identify low stock items.
     */
    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.material_id'   => 'required|exists:materials,id',
            'items.*.current_stock' => 'required|numeric|min:0',
        ]);

        $materialIds = collect($data['items'])->pluck('material_id');
        $materials = Material::whereIn('id', $materialIds)->get()->keyBy('id');

        $lowStockItems = [];

        foreach ($data['items'] as $itemData) {
            $m = $materials->get($itemData['material_id']);
            if (!$m) continue;

            $stock = (float)$itemData['current_stock'];
            $threshold = (float)$m->min_stock;

            if ($stock <= $threshold) {
                $lowStockItems[] = [
                    'id'                  => $m->id,
                    'name'                => $m->name,
                    'name_km'             => $m->name_km,
                    'category'            => $m->category,
                    'category_label'      => $m->categoryLabelShort(),
                    'sub_type'            => $m->sub_type,
                    'size'                => $m->size,
                    'unit'                => $m->unit,
                    'current_stock'       => $stock,
                    'low_stock_threshold' => $threshold,
                    'critical_stock'      => $m->critical_stock !== null ? (float)$m->critical_stock : null,
                    'status'              => $m->stockStatus($stock),
                    'status_label'        => $m->stockStatusLabel($stock),
                    'status_badge'        => $m->stockStatusBadge($stock),
                ];
            }
        }

        return response()->json([
            'has_low_stock' => count($lowStockItems) > 0,
            'items'         => $lowStockItems,
            'count'         => count($lowStockItems),
        ]);
    }

    /**
     * API: Generate preview of formatted message and list destination groups.
     */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'        => 'required|array',
            'report_date'  => 'required|date',
            'performed_by' => 'nullable|string|max:100',
            'group_ids'    => 'nullable|array',
        ]);

        $previewText = $this->alertService->formatLeaderLowStockMessage(
            $data['items'],
            $data['report_date'],
            $data['performed_by'] ?? null
        );

        $selectedGroupIds = $data['group_ids'] ?? json_decode(Setting::get('low_stock_notification_groups', '[]'), true) ?: [];
        $groups = TelegramGroup::whereIn('id', $selectedGroupIds)->get();

        if ($groups->isEmpty()) {
            $alertChatId = Setting::get('alert_chat_id');
            if ($alertChatId) {
                $groups = collect([(object)[
                    'id' => $alertChatId,
                    'name' => 'Default Alert Chat (' . $alertChatId . ')',
                    'displayLabel' => fn() => 'Default Alert Chat',
                ]]);
            }
        }

        return response()->json([
            'preview_text'       => $previewText,
            'destination_groups' => $groups->map(fn($g) => [
                'id'    => $g->id,
                'name'  => is_callable([$g, 'displayLabel']) ? $g->displayLabel() : ($g->name ?? 'Group'),
            ]),
        ]);
    }

    /**
     * API / Action: Send Low Stock Notification to leaders.
     */
    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items'        => 'required|array',
            'report_date'  => 'required|date',
            'performed_by' => 'nullable|string|max:100',
            'category'     => 'nullable|string|max:50',
            'group_ids'    => 'nullable|array',
        ]);

        $selectedGroupIds = $data['group_ids'] ?? json_decode(Setting::get('low_stock_notification_groups', '[]'), true) ?: [];

        $result = $this->alertService->sendLeaderLowStockNotification(
            $data['items'],
            $selectedGroupIds,
            $data['report_date'],
            $data['performed_by'] ?? null,
            $data['category'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Admin: Low Stock Notification History view.
     */
    public function history(Request $request): View
    {
        $query = LowStockNotification::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        $notifications = $query->paginate(20);

        return view('stock.low_stock.history', compact('notifications'));
    }

    /**
     * Admin: Show single notification details API.
     */
    public function show($id): JsonResponse
    {
        $notification = LowStockNotification::with('destinationGroup')->findOrFail($id);

        return response()->json([
            'id'                     => $notification->id,
            'report_date'            => $notification->report_date->format('d/m/Y'),
            'category'               => $notification->category,
            'destination_group_name' => $notification->destination_group_name,
            'items_count'            => $notification->items_count,
            'items_payload'          => $notification->items_payload,
            'message'                => $notification->message,
            'status'                 => $notification->status,
            'status_badge'           => $notification->statusBadge(),
            'sent_by'                => $notification->sent_by,
            'sent_at'                => $notification->sent_at?->format('d/m/Y H:i:s'),
            'error_message'          => $notification->error_message,
        ]);
    }

    /**
     * Admin: Retry sending a failed notification.
     */
    public function retry($id): RedirectResponse
    {
        $notification = LowStockNotification::findOrFail($id);

        if (!$notification->items_payload) {
            return back()->with('error', 'មិនមានព័ត៌មានទំនិញសម្រាប់ផ្ញើឡើងវិញទេ (No items payload found)');
        }

        $groupIds = $notification->destination_group_id ? [$notification->destination_group_id] : [];

        $result = $this->alertService->sendLeaderLowStockNotification(
            $notification->items_payload,
            $groupIds,
            $notification->report_date->format('Y-m-d'),
            $notification->sent_by,
            $notification->category,
            true // forceRetry
        );

        if ($result['success']) {
            $notification->update(['status' => 'SENT', 'sent_at' => now(), 'error_message' => null]);
            return back()->with('success', '✅ ផ្ញើសារឡើងវិញបានសម្រេច (Successfully retried sending notification)');
        }

        return back()->with('error', '❌ ផ្ញើសារឡើងវិញបានបរាជ័យ: ' . ($result['message'] ?? 'Unable to connect to Telegram'));
    }

    /**
     * Admin: Low Stock Configuration Page.
     */
    public function settings(): View
    {
        $enabled           = Setting::get('low_stock_alert_enabled', '1') === '1';
        $defaultThreshold  = Setting::get('low_stock_default_threshold', '3');
        $defaultCritical   = Setting::get('low_stock_default_critical', '1');
        $selectedGroupIds  = json_decode(Setting::get('low_stock_notification_groups', '[]'), true) ?: [];
        $customTemplate    = Setting::get('low_stock_message_template', '');
        $telegramGroups    = TelegramGroup::orderBy('name')->get();

        return view('stock.low_stock.settings', compact(
            'enabled',
            'defaultThreshold',
            'defaultCritical',
            'selectedGroupIds',
            'customTemplate',
            'telegramGroups'
        ));
    }

    /**
     * Admin: Save Low Stock Configuration.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'low_stock_alert_enabled'     => 'nullable|boolean',
            'low_stock_default_threshold' => 'required|numeric|min:0',
            'low_stock_default_critical'  => 'nullable|numeric|min:0',
            'group_ids'                   => 'nullable|array',
            'low_stock_message_template'  => 'nullable|string|max:4096',
        ]);

        Setting::set('low_stock_alert_enabled', $request->has('low_stock_alert_enabled') ? '1' : '0');
        Setting::set('low_stock_default_threshold', (string)$data['low_stock_default_threshold']);
        Setting::set('low_stock_default_critical', (string)($data['low_stock_default_critical'] ?? '1'));
        Setting::set('low_stock_notification_groups', json_encode($data['group_ids'] ?? []));
        Setting::set('low_stock_message_template', $data['low_stock_message_template'] ?? '');

        return back()->with('success', '✅ ការកំណត់ស្តុកជិតអស់ត្រូវបានរក្សាទុក (Low Stock settings updated successfully)');
    }
}
