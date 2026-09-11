<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\StockMovement;
use App\Services\AlertService;
use App\Services\StockService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TelegramMiniAppController extends Controller
{
    public function __construct(
        private StockService $stockService,
        private AlertService $alertService,
        private TelegramService $telegramService
    ) {}

    /**
     * Render Mini App SPA view
     */
    public function index(): View
    {
        $materials = Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function (Material $m) {
                $m->calculated_stock = $m->currentStock();
                return $m;
            });

        $categories = [
            'consumable' => '🧴 Consumables (សម្ភារៈប្រើប្រាស់)',
            'paper'      => '📄 ក្រដាស (Paper)',
            'film'       => '🎞️ Film / ស្គុត',
            'offset'     => '🖨️ Offset Supplies',
        ];

        return view('telegram.mini_app', compact('materials', 'categories'));
    }

    /**
     * Get JSON data for SPA
     */
    public function getData(): JsonResponse
    {
        $materials = $this->stockService->getAllStockLevels();

        $recent = StockMovement::with('material')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'material'     => $m->material ? ($m->material->name_km ?: $m->material->name) : 'Item',
                'type'         => $m->type,
                'quantity'     => (float) $m->quantity,
                'unit'         => $m->material ? $m->material->unit : '',
                'reason'       => $m->reason ?: ($m->notes ?: 'Stock Out'),
                'performed_by' => $m->performed_by ?: 'User',
                'time'         => $m->created_at ? $m->created_at->format('d/m/Y h:i A') : '',
            ]);

        return response()->json([
            'ok'        => true,
            'materials' => $materials,
            'recent'    => $recent,
        ]);
    }

    /**
     * Validate Telegram WebApp initData query string and return parsed user data.
     */
    private function validateTelegramInitData(?string $initData): ?array
    {
        if (empty($initData)) {
            return null;
        }

        parse_str($initData, $data);

        $userRaw = $data['user'] ?? null;
        if (!$userRaw) {
            return null;
        }

        $user = is_array($userRaw) ? $userRaw : json_decode($userRaw, true);
        if (!is_array($user)) {
            return null;
        }

        $firstName = $user['first_name'] ?? '';
        $lastName  = $user['last_name'] ?? '';
        $username  = $user['username'] ?? '';
        $fullName  = trim("{$firstName} {$lastName}");

        $hash = $data['hash'] ?? null;
        $isValid = false;

        if ($hash) {
            $dataCopy = $data;
            unset($dataCopy['hash']);
            ksort($dataCopy);
            $dataCheckArr = [];
            foreach ($dataCopy as $key => $val) {
                if (is_array($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
                $dataCheckArr[] = $key . '=' . $val;
            }
            $dataCheckString = implode("\n", $dataCheckArr);
            $botToken = config('services.telegram.bot_token', '');
            if ($botToken) {
                $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
                $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);
                $isValid = hash_equals($calculatedHash, $hash);
            }
        }

        return [
            'id'        => $user['id'] ?? null,
            'full_name' => $fullName ?: ($username ?: 'Telegram User'),
            'username'  => $username,
            'is_valid'  => $isValid,
        ];
    }

    /**
     * Process Stock Out request from Mini App
     */
    public function stockOut(Request $request): JsonResponse
    {
        $data = $request->validate([
            'material_id'  => 'required|exists:materials,id',
            'quantity'     => 'required|numeric|min:0.01',
            'reason'       => 'required|string|max:100',
            'notes'        => 'nullable|string|max:500',
            'performed_by' => 'required|string|max:100',
            'tg_init_data' => 'nullable|string',
        ]);

        // Identity verification logic: support both physical receiver (performed_by) and Telegram recorder
        $takenByInput = htmlspecialchars(trim($data['performed_by']));
        $tgUser = $this->validateTelegramInitData($request->input('tg_init_data', ''));

        if ($tgUser) {
            $unameStr = $tgUser['username'] ? " (@" . htmlspecialchars($tgUser['username']) . ")" : "";
            $badge = $tgUser['is_valid'] ? " 🟢" : " 📱";
            $recorderStr = htmlspecialchars($tgUser['full_name']) . "{$unameStr}{$badge}";

            if ($takenByInput && strcasecmp($takenByInput, $tgUser['full_name']) !== 0) {
                $performedByHtml = "{$takenByInput}\n<b>អ្នកកត់ត្រា:</b> {$recorderStr}";
                $performedByDb   = "{$takenByInput} (អ្នកកត់ត្រា: {$tgUser['full_name']})";
            } else {
                $performedByHtml = $recorderStr;
                $performedByDb   = $tgUser['full_name'];
            }
        } elseif (auth()->check()) {
            $webUserStr = htmlspecialchars(auth()->user()->name) . " 💻";
            if ($takenByInput && strcasecmp($takenByInput, auth()->user()->name) !== 0) {
                $performedByHtml = "{$takenByInput}\n<b>អ្នកកត់ត្រា:</b> {$webUserStr}";
                $performedByDb   = "{$takenByInput} (អ្នកកត់ត្រា: " . auth()->user()->name . ")";
            } else {
                $performedByHtml = $webUserStr;
                $performedByDb   = auth()->user()->name;
            }
        } else {
            $performedByHtml = $takenByInput ?: 'Unspecified';
            $performedByDb   = $takenByInput ?: 'Unspecified';
        }

        $material = Material::findOrFail($data['material_id']);
        $current  = $material->currentStock();
        $qty      = (float) $data['quantity'];

        if ($qty > $current) {
            return response()->json([
                'ok'      => false,
                'message' => "Stock មិនគ្រប់គ្រាន់ទេ! {$material->name} មានត្រឹម {$current} {$material->unit} ប៉ុណ្ណោះ។",
            ], 422);
        }

        $reason = $data['reason'];
        $notes  = $data['notes'] ?? '';
        $fullNotes = $notes ? "{$reason} - {$notes}" : $reason;

        // Record stock movement
        $movement = $this->stockService->recordMovement(
            $material,
            'out',
            $qty,
            'Mini App Stock Out',
            $performedByDb,
            $fullNotes,
            now()->toDateString(),
            $reason
        );

        $remaining = $material->currentStock();

        // Trigger Low Stock Check
        $this->alertService->checkAndAlert($material);

        // Helper to format Khmer date
        $monthsKh = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា', 5 => 'ឧសភា', 6 => 'មិថុនា',
            7 => 'កក្កដា', 8 => 'សីហា', 9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'
        ];
        $dateKhmer = now()->format('j') . ' ' . ($monthsKh[(int)now()->format('n')] ?? '') . ' ' . now()->format('Y');
        $timeStr   = now()->format('h:i A');

        $reasonMap = [
            'Production'          => 'ប្រើប្រាស់ក្នុងការផលិត',
            'Machine maintenance' => 'ថែទាំម៉ាស៊ីន',
            'Cleaning'            => 'សម្អាត',
            'Damaged'             => 'ខូចខាត',
            'Other'               => 'ផ្សេងៗ',
        ];
        $reasonKh = $reasonMap[$reason] ?? $reason;
        if ($notes && $reason === 'Other') {
            $reasonKh .= " ({$notes})";
        } elseif ($notes && $reason !== 'Other') {
            $reasonKh .= " - {$notes}";
        }

        $refCode  = 'SO-' . now()->format('Ymd') . '-' . str_pad($movement->id ?? 1, 4, '0', STR_PAD_LEFT);
        $itemName = $material->name_km ?: $material->name;
        $unitStr  = $material->unit ?: 'ដប';

        $defaultTemplate = "<b>របាយការណ៍ដកស្តុកប្រើប្រាស់</b>\n" .
               "━━━━━━━━━━━━━━\n\n" .
               "<b>មុខទំនិញ:</b> {name}\n" .
               "<b>ចំនួនដក:</b> {quantity} {unit}\n" .
               "<b>គោលបំណង:</b> {reason}\n\n" .
               "<b>អ្នកដក:</b> {performed_by}\n" .
               "<b>កាលបរិច្ឆេទ:</b> {date}\n" .
               "<b>ម៉ោង:</b> {time}\n\n" .
               "<b>ស្តុកមុនដក:</b> {stock_before} {unit}\n" .
               "<b>ស្តុកនៅសល់:</b> {stock_remaining} {unit}\n\n" .
               "<b>លេខប្រតិបត្តិការ:</b> {ref_code}\n" .
               "━━━━━━━━━━━━━━\n" .
               "🤖 <b>ប្រព័ន្ធបានកត់ត្រាដោយស្វ័យប្រវត្តិ</b>";

        $template = \App\Models\Setting::get('stock_out_template', $defaultTemplate);

        $replacements = [
            '{name}'            => htmlspecialchars($itemName),
            '{quantity}'        => $qty,
            '{unit}'            => htmlspecialchars($unitStr),
            '{reason}'          => htmlspecialchars($reasonKh),
            '{performed_by}'    => $performedByHtml,
            '{date}'            => $dateKhmer,
            '{time}'            => $timeStr,
            '{stock_before}'    => ($current + 0),
            '{stock_remaining}' => ($remaining + 0),
            '{ref_code}'        => $refCode,
        ];

        $msg = str_replace(array_keys($replacements), array_values($replacements), $template);

        // Send live notification to Telegram group
        $targetChatId = \App\Models\Setting::get('stock_out_chat_id') ?: \App\Models\Setting::get('daily_usage_chat_id');
        if ($targetChatId) {
            $targetThread = \App\Models\Setting::get('stock_out_thread_id') ?: \App\Models\Setting::get('daily_usage_thread_id');
            $this->telegramService->sendMessage($targetChatId, $msg, $targetThread ? (int)$targetThread : null, 'HTML');
        }

        return response()->json([
            'ok'        => true,
            'message'   => 'បានដកស្តុកជោគជ័យ (Stock Out Completed Successfully)',
            'remaining' => $remaining,
            'material'  => $material->name_km ?: $material->name,
            'unit'      => $material->unit,
            'quantity'  => $qty,
            'date'      => now()->format('d/m/Y'),
            'time'      => now()->format('h:i A'),
        ]);
    }
}
