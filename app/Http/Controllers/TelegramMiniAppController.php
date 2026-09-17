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

        // Retrieve frequent stock receivers for quick-tap chips with clean fallback
        $defaultTakers = collect(['Davy', 'សាន ចន្ថា', 'លាប ឡុង', 'Niza', 'សួស្ដី']);
        $dbTakers = StockMovement::select('performed_by')
            ->whereNotNull('performed_by')
            ->where('performed_by', '!=', '')
            ->where('performed_by', '!=', 'System')
            ->where('performed_by', '!=', 'System Admin')
            ->groupBy('performed_by')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(15)
            ->pluck('performed_by')
            ->map(function ($name) {
                // Strip HTML, newlines, redundant '(អ្នកកត់ត្រា:...)' and any dangling parentheses
                $clean = strip_tags($name);
                $clean = preg_replace('/\s*\(អ្នកកត់ត្រា:.*$/u', '', $clean);
                $clean = preg_replace('/ - TONG DAVY.*/u', '', $clean);
                $clean = preg_replace('/[\r\n].*/u', '', $clean);
                $clean = trim($clean, " \t\n\r\0\x0B()[]");

                // Canonical alias normalization (e.g. Khmer Coeng Da vs Coeng Ta, nick vs full name)
                return match ($clean) {
                    'Keo pholchomruen Niza' => 'Niza',
                    'តុង ដាវី'              => 'Davy',
                    'ចន្ថា'                 => 'សាន ចន្ថា',
                    'ឡុង'                   => 'លាប ឡុង',
                    'សួស្តី'                => 'សួស្ដី', // Normalize U+178f (Coeng Ta) to U+178a (Coeng Da)
                    default                 => $clean,
                };
            })
            ->filter(fn($n) => !empty($n) && !str_contains($n, '?') && $n !== 'System Admin' && $n !== 'System')
            ->unique()
            ->values();

        $colors = ['#2563eb', '#059669', '#7c3aed', '#d97706', '#e11d48', '#0891b2'];
        $frequentTakers = $dbTakers->merge($defaultTakers)->unique()->values()->take(6)->map(function ($name, $idx) use ($colors) {
            $trimmed = trim($name);
            $initial = mb_substr($trimmed, 0, 1, 'UTF-8');
            return [
                'name'    => $trimmed,
                'initial' => $initial,
                'color'   => $colors[$idx % count($colors)],
            ];
        });

        return view('telegram.mini_app', compact('materials', 'categories', 'frequentTakers'));
    }

    /**
     * Get JSON data for SPA
     */
    public function getData(Request $request): JsonResponse
    {
        $materials = $this->stockService->getAllStockLevels();

        $query = StockMovement::with('material')->orderByDesc('id');

        $type = $request->query('type');
        if ($type && in_array($type, ['out', 'in', 'adjust'], true)) {
            $query->where('type', $type);
        }

        $recent = $query->limit(40)
            ->get()
            ->map(function (StockMovement $m) {
                $mat = $m->material;
                $matName = $mat ? ($mat->name_km ?: $mat->name) : 'Item';
                $unit = $mat ? ($mat->unit ?: '') : '';
                $qty = (float) $m->quantity;

                $typeLabel = match ($m->type) {
                    'out'    => 'ដកស្តុក',
                    'in'     => 'បញ្ចូលស្តុក',
                    'adjust' => 'កែតម្រូវ/បច្ចុប្បន្នភាព',
                    default  => $m->type,
                };

                $typeBadge = match ($m->type) {
                    'out'    => 'bg-danger-subtle text-danger border-danger-subtle',
                    'in'     => 'bg-success-subtle text-success border-success-subtle',
                    'adjust' => 'bg-primary-subtle text-primary border-primary-subtle',
                    default  => 'bg-secondary-subtle text-secondary',
                };

                $qtyDisplay = match ($m->type) {
                    'out'    => "-{$qty} {$unit}",
                    'in'     => "+{$qty} {$unit}",
                    'adjust' => "ស្តុក: {$qty} {$unit}",
                    default  => "{$qty} {$unit}",
                };

                $qtyClass = match ($m->type) {
                    'out'    => 'text-danger',
                    'in'     => 'text-success',
                    'adjust' => 'text-primary',
                    default  => 'text-dark',
                };

                // Performer name normalization
                $performer = $m->performed_by;
                if (!$performer) {
                    $performer = $m->type === 'adjust' ? 'ប្រព័ន្ធ (System)' : 'បុគ្គលិក (User)';
                }

                $reason = $m->reason;
                if (!$reason) {
                    $reason = match ($m->type) {
                        'adjust' => $m->reference ?: 'បច្ចុប្បន្នភាពស្តុកប្រចាំថ្ងៃ',
                        'in'     => 'បញ្ចូលស្តុក',
                        default  => 'ដកស្តុក',
                    };
                }

                $refCode = (!empty($m->reference) && str_starts_with($m->reference, 'SO-'))
                    ? $m->reference
                    : ('SO-' . ($m->created_at ? $m->created_at->format('Ymd') : date('Ymd')) . '-' . str_pad($m->id, 4, '0', STR_PAD_LEFT));

                return [
                    'id'               => $m->id,
                    'material'         => $matName,
                    'material_code'    => $mat ? $mat->code : '',
                    'type'             => $m->type,
                    'type_label'       => $typeLabel,
                    'type_badge_class' => $typeBadge,
                    'quantity'         => $qty,
                    'qty_display'      => $qtyDisplay,
                    'qty_class'        => $qtyClass,
                    'unit'             => $unit,
                    'reason'           => $reason,
                    'performed_by'     => $performer,
                    'can_resend'       => ($m->type === 'out'),
                    'ref_code'         => $refCode,
                    'time'             => $m->created_at ? $m->created_at->format('d/m/Y h:i A') : '',
                ];
            });

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
     * Process Stock Out request from Mini App (supports single item or multiple items in one transaction)
     */
    public function stockOut(Request $request): JsonResponse
    {
        $rules = [
            'reason'       => 'required|string|max:100',
            'notes'        => 'nullable|string|max:500',
            'performed_by' => 'required|string|max:100',
            'tg_init_data' => 'nullable|string',
        ];

        if ($request->has('items')) {
            $rules['items']               = 'required|array|min:1';
            $rules['items.*.material_id'] = 'required|exists:materials,id';
            $rules['items.*.quantity']    = 'required|numeric|min:0.01';
        } else {
            $rules['material_id'] = 'required|exists:materials,id';
            $rules['quantity']    = 'required|numeric|min:0.01';
        }

        $data = $request->validate($rules);

        // Normalize items to process
        $itemsToProcess = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $it) {
                $itemsToProcess[] = [
                    'material_id' => (int) $it['material_id'],
                    'quantity'    => (float) $it['quantity'],
                ];
            }
        } else {
            $itemsToProcess[] = [
                'material_id' => (int) $data['material_id'],
                'quantity'    => (float) $data['quantity'],
            ];
        }

        // Identity logic: support both physical taker (performed_by) and Telegram recorder
        $takenByInput = trim($data['performed_by'] ?? 'Davy');
        $takenByHtml  = htmlspecialchars($takenByInput);

        $tgUser = $this->validateTelegramInitData($request->input('tg_init_data', ''));
        $recorderName = '';
        if ($tgUser && !empty($tgUser['full_name'])) {
            $recorderName = $tgUser['full_name'];
            if (!empty($tgUser['username']) && !str_contains($recorderName, $tgUser['username'])) {
                $recorderName .= " (@{$tgUser['username']})";
            }
        } elseif ($request->filled('recorder_name')) {
            $recorderName = trim($request->input('recorder_name'));
        } elseif (auth()->check()) {
            $recorderName = auth()->user()->name;
        }

        $recorderHtml = $recorderName ? htmlspecialchars($recorderName) : '';

        if ($recorderName !== '') {
            $performedByDb = "{$takenByInput} (អ្នកកត់ត្រា: {$recorderName})";
            $takerLine    = "<b>អ្នកដក:</b> {$takenByHtml}";
            $recorderLine = "<b>អ្នកកត់ត្រា:</b> {$recorderHtml}";
        } else {
            $performedByDb = $takenByInput;
            $takerLine    = "<b>អ្នកដក:</b> {$takenByHtml}";
            $recorderLine = '';
        }

        // Pre-validate stock availability for all items before executing any mutations
        $materialIds = array_column($itemsToProcess, 'material_id');
        $materials = Material::whereIn('id', $materialIds)->get()->keyBy('id');

        foreach ($itemsToProcess as $it) {
            $mat = $materials->get($it['material_id']);
            if (!$mat) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'រកមិនឃើញទំនិញ (Item not found)',
                ], 404);
            }
            $currentStock = $mat->currentStock();
            if ($it['quantity'] > $currentStock) {
                $itemName = $mat->name_km ?: $mat->name;
                return response()->json([
                    'ok'      => false,
                    'message' => "Stock មិនគ្រប់គ្រាន់ទេ! {$itemName} មានត្រឹម {$currentStock} {$mat->unit} ប៉ុណ្ណោះ (ស្នើសុំ {$it['quantity']})។",
                ], 422);
            }
        }

        $reason = $data['reason'];
        $notes  = $data['notes'] ?? '';
        $fullNotes = $notes ? "{$reason} - {$notes}" : $reason;

        $processedResults = [];
        $movements = [];

        // Execute stock deductions inside database transaction
        \Illuminate\Support\Facades\DB::transaction(function () use (
            $itemsToProcess, $materials, $performedByDb, $fullNotes, $reason,
            &$processedResults, &$movements
        ) {
            foreach ($itemsToProcess as $it) {
                $mat = $materials->get($it['material_id']);
                $currentBefore = $mat->currentStock();
                $qty = $it['quantity'];

                $movement = $this->stockService->recordMovement(
                    $mat,
                    'out',
                    $qty,
                    'Mini App Stock Out',
                    $performedByDb,
                    $fullNotes,
                    now()->toDateString(),
                    $reason
                );

                $remaining = $mat->currentStock();
                $this->alertService->checkAndAlert($mat);

                $movements[] = $movement;
                $processedResults[] = [
                    'id'              => $mat->id,
                    'name'            => $mat->name_km ?: $mat->name,
                    'code'            => $mat->code,
                    'unit'            => $mat->unit ?: 'pcs',
                    'quantity'        => $qty,
                    'stock_before'    => $currentBefore,
                    'stock_remaining' => $remaining,
                    'min_stock'       => $mat->min_stock,
                ];
            }
        });

        // Audit Trail activity logging
        $itemSummaries = array_map(fn($r) => "{$r['name']}: {$r['quantity']} {$r['unit']}", $processedResults);
        \App\Models\ActivityLog::record(
            'Stock Out',
            "Stock Out by {$takenByInput} (" . count($processedResults) . " items): " . implode(', ', $itemSummaries) . " [Reason: {$reason}]",
            'inventory'
        );

        // Date & Time formatting in Khmer
        $monthsKh = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា', 5 => 'ឧសភា', 6 => 'មិថុនា',
            7 => 'កក្កដា', 8 => 'សីហា', 9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'
        ];
        $dateKhmer = now()->format('j') . ' ' . ($monthsKh[(int)now()->format('n')] ?? '') . ' ' . now()->format('Y');
        $timeStr   = now()->format('h:i A');

        $reasonMap = [
            'Production'          => 'ប្រើប្រាស់ក្នុងការបោះពុម្ព',
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

        // Prepare Telegram Notification Message in the requested professional format
        $refCode = 'SO-' . now()->format('Ymd') . '-' . str_pad($movements[0]->id ?? 1, 4, '0', STR_PAD_LEFT);
        StockMovement::whereIn('id', collect($movements)->pluck('id'))->update(['reference' => $refCode]);

        // Send live notification to Telegram group
        $this->sendStockOutTelegramNotification($refCode, now(), $takerLine, $recorderLine, $reasonKh, $processedResults);

        return response()->json([
            'ok'        => true,
            'message'   => count($processedResults) > 1 
                ? "បានដកស្តុក " . count($processedResults) . " មុខទំនិញជោគជ័យ (Stock Out Completed)"
                : "បានដកស្តុកជោគជ័យ (Stock Out Completed)",
            'items'     => $processedResults,
            // Single item backward compatibility
            'remaining' => $processedResults[0]['stock_remaining'],
            'material'  => $processedResults[0]['name'],
            'unit'      => $processedResults[0]['unit'],
            'quantity'  => $processedResults[0]['quantity'],
            'date'      => now()->format('d/m/Y'),
            'time'      => now()->format('h:i A'),
        ]);
    }

    /**
     * Resend Telegram notification for a stock out transaction.
     */
    public function resend(int $id): JsonResponse
    {
        $movement = StockMovement::with('material')->find($id);
        if (!$movement) {
            return response()->json([
                'ok'      => false,
                'message' => 'រកមិនឃើញទិន្នន័យដកស្តុកនេះទេ (Movement not found)',
            ], 404);
        }

        if ($movement->type !== 'out') {
            return response()->json([
                'ok'      => false,
                'message' => 'អនុញ្ញាតតែការផ្ញើសារឡើងវិញសម្រាប់ប្រតិបត្តិការដកស្តុកប៉ុណ្ណោះ (Only stock-out can be resent)',
            ], 422);
        }

        // Find related movements in the same batch
        $batchMovements = collect();
        if (!empty($movement->reference) && str_starts_with($movement->reference, 'SO-')) {
            $batchMovements = StockMovement::with('material')
                ->where('reference', $movement->reference)
                ->orderBy('id')
                ->get();
        }

        if ($batchMovements->isEmpty()) {
            // Match same actor, reason, and time within 15 seconds
            $start = $movement->created_at ? $movement->created_at->copy()->subSeconds(15) : now()->subSeconds(15);
            $end   = $movement->created_at ? $movement->created_at->copy()->addSeconds(15) : now()->addSeconds(15);
            $batchMovements = StockMovement::with('material')
                ->where('type', 'out')
                ->where('performed_by', $movement->performed_by)
                ->where('reason', $movement->reason)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('id')
                ->get();
        }

        if ($batchMovements->isEmpty()) {
            $batchMovements = collect([$movement]);
        }

        $refCode = (!empty($movement->reference) && str_starts_with($movement->reference, 'SO-'))
            ? $movement->reference
            : ('SO-' . ($movement->created_at ? $movement->created_at->format('Ymd') : date('Ymd')) . '-' . str_pad($batchMovements->first()->id ?? $movement->id, 4, '0', STR_PAD_LEFT));

        // Format actor lines — split taker and recorder
        $rawPerformer = $movement->performed_by ?: 'User';
        if (preg_match('/^(.*?)\s*\(អ្នកកត់ត្រា:\s*(.*?)\)$/u', $rawPerformer, $matches)) {
            $takerLine    = "<b>អ្នកដក:</b> " . htmlspecialchars(trim($matches[1]));
            $recorderLine = "<b>អ្នកកត់ត្រា:</b> " . htmlspecialchars(trim($matches[2]));
        } else {
            $takerLine    = "<b>អ្នកដក:</b> " . htmlspecialchars($rawPerformer);
            $recorderLine = '';
        }

        // Reason mapping
        $reasonMap = [
            'Production'          => 'ប្រើប្រាស់ក្នុងការបោះពុម្ព',
            'Machine maintenance' => 'ថែទាំម៉ាស៊ីន',
            'Cleaning'            => 'សម្អាត',
            'Damaged'             => 'ខូចខាត',
            'Other'               => 'ផ្សេងៗ',
        ];
        $reason = $movement->reason ?: 'Production';
        $reasonKh = $reasonMap[$reason] ?? $reason;
        $notes = $movement->notes;
        if ($notes && !str_starts_with($notes, 'Mini App Stock Out')) {
            if ($reason === 'Other' && !str_contains($reasonKh, $notes)) {
                $reasonKh .= " ({$notes})";
            } elseif ($reason !== 'Other' && !str_contains($reasonKh, $notes)) {
                $cleanNote = preg_replace('/^' . preg_quote($reason, '/') . '\s*-\s*/u', '', $notes);
                if ($cleanNote && $cleanNote !== $reason) {
                    $reasonKh .= " - {$cleanNote}";
                }
            }
        }

        $items = [];
        foreach ($batchMovements as $bm) {
            $mat = $bm->material;
            $currentStock = $mat ? $mat->currentStock() : 0;
            $items[] = [
                'name'            => $mat ? ($mat->name_km ?: $mat->name) : 'Item',
                'unit'            => $mat ? ($mat->unit ?: 'pcs') : 'pcs',
                'quantity'        => (float) $bm->quantity,
                'stock_remaining' => $currentStock,
                'min_stock'       => $mat ? $mat->min_stock : 0,
            ];
        }

        $sent = $this->sendStockOutTelegramNotification(
            $refCode,
            $movement->created_at ?: now(),
            $takerLine,
            $recorderLine,
            $reasonKh,
            $items
        );

        return response()->json([
            'ok'      => true,
            'sent'    => $sent,
            'message' => 'បានផ្ញើសាររបាយការណ៍ទៅ Telegram រួចរាល់! (Resent notification successfully)',
            'ref'     => $refCode,
            'items'   => count($items),
        ]);
    }

    /**
     * Build and send the standard Telegram stock out notification.
     *
     * New format:
     *   Header  — អ្នកដក: {taker} (alone)
     *   Footer  — អ្នកកត់ត្រា: {recorder} (before the auto-generated line)
     */
    private function sendStockOutTelegramNotification(
        string $refCode,
        \Carbon\CarbonInterface|string $dateTime,
        string $takerLine,
        string $recorderLine,
        string $reasonKh,
        array $items
    ): bool {
        $monthsKh = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា', 5 => 'ឧសភា', 6 => 'មិថុនា',
            7 => 'កក្កដា', 8 => 'សីហា', 9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ'
        ];
        $dt = $dateTime instanceof \Carbon\CarbonInterface ? $dateTime : now();
        $dateKhmer = $dt->format('j') . ' ' . ($monthsKh[(int)$dt->format('n')] ?? '') . ' ' . $dt->format('Y');
        $timeStr   = $dt->format('h:i A');

        $itemsCount = count($items);
        $itemBlocks = [];
        foreach ($items as $idx => $res) {
            $num = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
            $warn = ($res['stock_remaining'] <= 0 || (!empty($res['min_stock']) && $res['stock_remaining'] <= $res['min_stock'])) ? ' ⚠️' : '';
            $itemName = htmlspecialchars($res['name']);
            $itemUnit = htmlspecialchars($res['unit']);
            $itemBlocks[] = "<b>{$num}. {$itemName}</b>\n" .
                            "• យកប្រើប្រាស់: <b>{$res['quantity']} {$itemUnit}</b>\n" .
                            "• ស្តុកនៅសល់: <b>{$res['stock_remaining']} {$itemUnit}</b>{$warn}";
        }
        $itemsText = implode("\n\n", $itemBlocks);

        $divider  = "━━━━━━━━━━━━━━━";
        $footerLine = $recorderLine ? "{$recorderLine}\n" : '';

        $msg = "<b>របាយការណ៍ដកស្តុកប្រើប្រាស់</b>\n" .
               "{$divider}\n" .
               "<b>លេខយោង:</b> <code>{$refCode}</code>\n" .
               "<b>កាលបរិច្ឆេទ:</b> {$dateKhmer} | {$timeStr}\n" .
               "{$takerLine}\n" .
               "<b>គោលបំណង:</b> " . htmlspecialchars($reasonKh) . "\n" .
               "{$divider}\n\n" .
               "<b>សម្ភារៈដែលបានយកប្រើប្រាស់ ({$itemsCount} មុខ)</b>\n\n" .
               $itemsText . "\n\n" .
               "{$divider}\n" .
               "{$footerLine}" .
               "<b>កំណត់ត្រាត្រូវបានបង្កើតដោយស្វ័យប្រវត្តិ</b>";

        $targetChatId = \App\Models\Setting::get('stock_out_chat_id') ?: \App\Models\Setting::get('daily_usage_chat_id');
        if ($targetChatId) {
            $targetThread = \App\Models\Setting::get('stock_out_thread_id') ?: \App\Models\Setting::get('daily_usage_thread_id');
            return (bool) $this->telegramService->sendMessage($targetChatId, $msg, $targetThread ? (int)$targetThread : null, 'HTML');
        }

        return false;
    }
}
