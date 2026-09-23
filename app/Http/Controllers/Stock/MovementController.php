<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\StockMovement;
use App\Services\AlertService;
use App\Services\ExcelExportService;
use App\Services\ImageService;
use App\Services\StockService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function __construct(
        private StockService  $stockService,
        private AlertService  $alertService,
        private ImageService  $imageService,
        private TelegramService $telegramService,
    ) {}

    public function index(Request $request): View
    {
        $query = StockMovement::with('material');

        // Filter by movement type
        if ($request->filled('type') && in_array($request->type, ['in', 'out', 'adjust'])) {
            $query->where('type', $request->type);
        }

        // Filter by material category
        if ($request->filled('category')) {
            $query->whereHas('material', fn($q) => $q->where('category', $request->category));
        }

        // Filter by search keyword
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('performed_by', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('material', fn($mq) => 
                      $mq->where('name', 'like', "%{$search}%")
                         ->orWhere('name_km', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%")
                  );
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('movement_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('movement_date', '<=', $request->end_date);
        }

        $movements = $query->orderByDesc('movement_date')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $todayMovements = $this->stockService->getTodayMovements();

        // Calculate today's summary metrics
        $stats = [
            'today_total'  => $todayMovements->count(),
            'today_in'     => $todayMovements->where('type', 'in')->count(),
            'today_in_qty' => (float)$todayMovements->where('type', 'in')->sum('quantity'),
            'today_out'    => $todayMovements->where('type', 'out')->count(),
            'today_out_qty'=> (float)$todayMovements->where('type', 'out')->sum('quantity'),
            'today_adjust' => $todayMovements->where('type', 'adjust')->count(),
        ];

        $categories = Material::whereNotNull('category')->distinct()->pluck('category')->filter();

        return view('stock.movements.index', compact('movements', 'todayMovements', 'stats', 'categories'));
    }

    public function create(): View
    {
        $materials = Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($m) {
                $m->calculated_stock = $m->currentStock();
                return $m;
            });

        return view('stock.movements.create', compact('materials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_id'   => 'required|exists:materials,id',
            'type'          => 'required|in:in,out,adjust',
            'reason'        => 'nullable|string|max:100',
            'quantity'      => 'required|numeric|min:0.01',
            'reference'     => 'nullable|string|max:255',
            'performed_by'  => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
            'movement_date' => 'required|date',
        ]);

        $material = Material::findOrFail($data['material_id']);

        // Prevent stock going negative on OUT
        if ($data['type'] === 'out') {
            $current = $material->currentStock();
            if ($data['quantity'] > $current) {
                return back()->with('error',
                    "Stock មិនគ្រប់! {$material->name} មាន {$current} {$material->unit} ប៉ុណ្ណោះ")
                    ->withInput();
            }
        }

        $this->stockService->recordMovement(
            $material,
            $data['type'],
            $data['quantity'],
            $data['reference'] ?? null,
            $data['performed_by'] ?? null,
            $data['notes'] ?? null,
            $data['movement_date'],
            $data['reason'] ?? null,
        );

        // Check low stock alert
        $this->alertService->checkAndAlert($material);

        if ($data['type'] === 'out') {
            $chatId = \App\Models\Setting::get('daily_usage_chat_id');
            if ($chatId) {
                $threadId = \App\Models\Setting::get('daily_usage_thread_id');
                $itemName = $material->name_km ?: $material->name;
                $qty = number_format($data['quantity'], 2) + 0; // Strip trailing zeroes
                $by = $data['performed_by'] ? " 👤 {$data['performed_by']}" : '';
                
                $msg = "📉 <b>Live Stock Usage</b>\n";
                $msg .= "• " . htmlspecialchars($itemName) . ": បានប្រើ <b>-{$qty} {$material->unit}</b>\n";
                $msg .= "• សល់ក្នុងស្តុក: <b>" . $material->currentStock() . " {$material->unit}</b>{$by}";
                
                if (!empty($data['notes'])) {
                    $msg .= "\n📝 Note: " . htmlspecialchars($data['notes']);
                }
                
                $this->telegramService->sendMessage($chatId, $msg, $threadId ? (int)$threadId : null, 'HTML');
            }
        }

        $label = match($data['type']) {
            'in'     => 'Stock In',
            'out'    => 'Stock Out',
            'adjust' => 'Adjustment',
        };

        \App\Models\ActivityLog::record(
            $label,
            "{$data['quantity']} {$material->unit} of '{$material->name}' (By: " . ($data['performed_by'] ?? session('user_name', 'Unknown')) . ")",
            'inventory'
        );

        return redirect()->route('stock.movements.index')
            ->with('success', "{$label}: {$data['quantity']} {$material->unit} — {$material->name}");
    }

    /**
     * Daily update: user enters CURRENT quantity for each item in a category.
     * System auto-creates ADJUST movements and optionally sends Telegram report.
     */
    public function dailyUpdate(Request $request): View
    {
        $category = $request->query('category', 'paper');
        // Accept any valid category string (not just known ones)
        if (!preg_match('/^[a-z0-9\-_]{1,50}$/', $category)) $category = 'paper';

        $updateDate = $request->query('date', now()->toDateString());

        $materials = Material::where('status', 'active')
            ->where('category', $category)
            ->orderBy('sub_type')
            ->orderBy('name')
            ->get();

        $allMovements = StockMovement::whereIn('material_id', $materials->pluck('id'))
            ->orderBy('movement_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('material_id');

        $materials->map(function ($m) use ($allMovements, $updateDate) {
            $moves = $allMovements->get($m->id, collect());
            $m->calculated_stock = Material::currentStockFromMovements($moves);

            // Find latest adjust on or before updateDate
            $lastAdjust = $moves->where('type', 'adjust')
                ->filter(fn($mv) => \Carbon\Carbon::parse($mv->movement_date)->toDateString() <= $updateDate)
                ->last();

            // Movements on updateDate that occurred after the last adjustment
            $activeMoves = $moves->filter(function ($mv) use ($updateDate, $lastAdjust) {
                $mvDate = \Carbon\Carbon::parse($mv->movement_date)->toDateString();
                $crDate = $mv->created_at ? $mv->created_at->toDateString() : $mvDate;
                $isDateMatch = ($mvDate === $updateDate || $crDate === $updateDate);
                if (!$isDateMatch) return false;
                if ($lastAdjust && $mv->created_at && $lastAdjust->created_at) {
                    if ($mv->created_at != $lastAdjust->created_at) {
                        return $mv->created_at > $lastAdjust->created_at;
                    }
                    return $mv->id > $lastAdjust->id;
                }
                return true;
            });

            $m->today_in  = (float) $activeMoves->where('type', 'in')->sum('quantity');
            $m->today_out = (float) $activeMoves->where('type', 'out')->sum('quantity');
            return $m;
        });

        $telegramGroups = \App\Models\TelegramGroup::orderBy('name')->get();

        // Auto-select the right group for this category
        $defaultGroup = \App\Models\TelegramGroup::forCategory($category);

        // Get language format setting for this category
        $nameFormat = \App\Models\Setting::get("telegram_item_name_format_{$category}", 'both');

        return view('stock.movements.daily', compact('materials', 'category', 'telegramGroups', 'defaultGroup', 'nameFormat', 'updateDate'));
    }

    /**
     * Get JSON stats (today_in, today_out) for materials in a category for a specific date
     */
    public function dailyStats(Request $request): \Illuminate\Http\JsonResponse
    {
        $date = $request->query('date', now()->toDateString());
        $category = $request->query('category', 'paper');

        $materials = Material::where('status', 'active')
            ->where('category', $category)
            ->get();

        $allMovements = StockMovement::whereIn('material_id', $materials->pluck('id'))
            ->orderBy('movement_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('material_id');

        $stats = [];
        foreach ($materials as $m) {
            $moves = $allMovements->get($m->id, collect());
            $lastAdjust = $moves->where('type', 'adjust')
                ->filter(fn($mv) => \Carbon\Carbon::parse($mv->movement_date)->toDateString() <= $date)
                ->last();

            $activeMoves = $moves->filter(function ($mv) use ($date, $lastAdjust) {
                $mvDate = \Carbon\Carbon::parse($mv->movement_date)->toDateString();
                $crDate = $mv->created_at ? $mv->created_at->toDateString() : $mvDate;
                $isDateMatch = ($mvDate === $date || $crDate === $date);
                if (!$isDateMatch) return false;
                if ($lastAdjust && $mv->created_at && $lastAdjust->created_at) {
                    if ($mv->created_at != $lastAdjust->created_at) {
                        return $mv->created_at > $lastAdjust->created_at;
                    }
                    return $mv->id > $lastAdjust->id;
                }
                return true;
            });

            $stats[$m->id] = [
                'today_in'  => (float) $activeMoves->where('type', 'in')->sum('quantity'),
                'today_out' => (float) $activeMoves->where('type', 'out')->sum('quantity'),
            ];
        }

        return response()->json(['ok' => true, 'stats' => $stats]);
    }

    public function dailyStore(Request $request): RedirectResponse
    {
        // Parse "chatId|threadId" composite value from the group/topic selector
        $rawChatId = $request->input('chat_id', '');
        if (str_contains($rawChatId, '|')) {
            [$chatId, $threadIdStr] = explode('|', $rawChatId, 2);
            $request->merge([
                'chat_id'           => $chatId,
                'message_thread_id' => $threadIdStr !== '' ? (int) $threadIdStr : null,
            ]);
        }

        $data = $request->validate([
            'category'           => 'required|string|max:50',
            'performed_by'       => 'nullable|string|max:100',
            'update_date'        => 'required|date',
            'send_telegram'      => 'nullable|boolean',
            'chat_id'            => 'nullable|string',
            'message_thread_id'  => 'nullable|integer',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|mimes:jpg,jpeg,png,webp|max:10240',
            'items'              => 'required|array|min:1',
            'items.*.material_id'   => 'required|exists:materials,id',
            'items.*.current_stock' => 'required|numeric|min:0',
        ]);

        $count   = 0;
        $updated = [];
        $imagePaths = [];

        try {
            $count = \DB::transaction(function() use ($data, $request, &$updated, &$imagePaths) {
                $count = 0;
                $materialIds = collect($data['items'])->pluck('material_id');
                $allMovements = StockMovement::whereIn('material_id', $materialIds)
                    ->orderBy('movement_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->groupBy('material_id');

                foreach ($data['items'] as $item) {
                    $material = Material::find($item['material_id']);
                    if (! $material) continue;

                    $moves = $allMovements->get($material->id, collect());
                    $oldQty = Material::currentStockFromMovements($moves);
                    $newQty = (float) $item['current_stock'];

                    // Calculate active movements for this item today prior to saving
                    $lastAdjust = $moves->where('type', 'adjust')
                        ->filter(fn($mv) => \Carbon\Carbon::parse($mv->movement_date)->toDateString() <= $data['update_date'])
                        ->last();

                    $activeMoves = $moves->filter(function ($mv) use ($data, $lastAdjust) {
                        $mvDate = \Carbon\Carbon::parse($mv->movement_date)->toDateString();
                        $crDate = $mv->created_at ? $mv->created_at->toDateString() : $mvDate;
                        $isDateMatch = ($mvDate === $data['update_date'] || $crDate === $data['update_date']);
                        if (!$isDateMatch) return false;
                        if ($lastAdjust && $mv->created_at && $lastAdjust->created_at) {
                            if ($mv->created_at != $lastAdjust->created_at) {
                                return $mv->created_at > $lastAdjust->created_at;
                            }
                            return $mv->id > $lastAdjust->id;
                        }
                        return true;
                    });

                    $todayIn  = (float) $activeMoves->where('type', 'in')->sum('quantity');
                    $todayOut = (float) $activeMoves->where('type', 'out')->sum('quantity');

                    $hasQtyChange = (abs($newQty - $oldQty) >= 0.01);
                    $hasMovements = ($todayIn > 0 || $todayOut > 0);

                    // Record adjustment if quantity changed or if item had active movements today to finalize and reset
                    if ($hasQtyChange || $hasMovements) {
                        $this->stockService->recordMovement(
                            $material, 'adjust', $newQty,
                            'Daily update', $data['performed_by'] ?? null,
                            null, $data['update_date'],
                        );
                        if ($hasQtyChange) {
                            $count++;
                        }
                    }

                    $updated[] = [
                        'id'        => $material->id,
                        'name'      => $material->name,
                        'name_km'   => $material->name_km,
                        'size'      => $material->size,
                        'unit'      => $material->unit,
                        'qty'       => $newQty,
                        'old_qty'   => $oldQty,
                        'today_out' => $todayOut,
                        'today_in'  => $todayIn,
                    ];
                }

                // Store uploaded images
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $path = $this->imageService->store($file, 'daily-reports');
                        if ($path) $imagePaths[] = $path;
                    }
                }

                return $count;
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'ការធ្វើបច្ចុប្បន្នភាពបានបរាជ័យ: ' . $e->getMessage())->withInput();
        }

        // Send to Telegram (after response so browser redirects instantly)
        if ($request->filled('chat_id') && $request->input('send_telegram')) {
            $chatId = $request->input('chat_id');
            $threadId = $request->integer('message_thread_id') ?: null;
            $category = $data['category'];
            $updateDate = $data['update_date'];
            $performedBy = $data['performed_by'] ?? null;

            dispatch(function() use ($category, $updateDate, $performedBy, $updated, $chatId, $imagePaths, $threadId) {
                app(\App\Http\Controllers\Stock\MovementController::class)->sendDailyTelegram(
                    $category,
                    $updateDate,
                    $performedBy,
                    $updated,
                    $chatId,
                    $imagePaths,
                    $threadId
                );
            })->afterResponse();
        }

        // Get category label from settings for success message
        $catLabel = match($data['category']) {
            'paper'      => \App\Models\Setting::get('category_label_paper', 'ក្រដាស (Paper)'),
            'film'       => \App\Models\Setting::get('category_label_film', 'Lamination Film (ស្គុត)'),
            'consumable' => \App\Models\Setting::get('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)'),
            default      => ucfirst($data['category']),
        };

        \App\Models\ActivityLog::record(
            'Daily Stock Update',
            "Adjusted {$count} items in category '{$data['category']}' on {$data['update_date']}",
            'inventory'
        );

        return redirect()->route('stock.movements.daily', ['category' => $data['category']])
            ->with('success', "✅ ធ្វើបច្ចុប្បន្នភាព {$catLabel} — {$count} items changed" .
                (count($imagePaths) ? ", " . count($imagePaths) . " photos sent" : ""));
    }

    private function sendDailyTelegram(
        string $category,
        string $date,
        ?string $by,
        array $items,
        string $chatId,
        array $imagePaths = [],
        ?int $threadId = null,
    ): void {
        $headerEmoji = match($category) {
            'paper'      => '❖',
            'film'       => '❖',
            'consumable' => '❖',
            default      => '❖',
        };
        
        // Get category label from settings
        $catLabel = match($category) {
            'paper'      => \App\Models\Setting::get('category_label_paper', 'ក្រដាស (Paper)'),
            'film'       => \App\Models\Setting::get('category_label_film', 'Lamination Film (ស្គុត)'),
            'consumable' => \App\Models\Setting::get('category_label_consumable', 'Consumable (សម្ភារៈប្រើប្រាស់)'),
            default      => ucfirst($category),
        };
        
        $catTag = match($category) {
            'paper'      => '#Paper_Stock',
            'film'       => '#Film_Stock',
            'consumable' => '#Consumable_Stock',
            default      => '#Stock',
        };

        $d = \Carbon\Carbon::parse($date);
        $km = ['','មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
        $dateStr = "ថ្ងៃទី {$d->day} ខែ{$km[$d->month]} ឆ្នាំ {$d->year}";

        // Build items list with category-specific language format
        $nameFormat = \App\Models\Setting::get("telegram_item_name_format_{$category}", 'both');
        $itemLines = [];
        $groupedItems = collect($items)->groupBy(fn($i) => $i['size'] ?? '');

        foreach ($groupedItems as $size => $group) {
            if ($size !== '' && $category !== 'paper') {
                $itemLines[] = "\n◎ {$size}:";
            }
            foreach ($group as $it) {
                if ($nameFormat === 'khmer') {
                    $nameDisplay = $it['name_km'] ?: $it['name'];
                } elseif ($nameFormat === 'english') {
                    $nameDisplay = $it['name'];
                } else {
                    $nameDisplay = $it['name_km']
                        ? "{$it['name']} — {$it['name_km']}"
                        : $it['name'];
                }

                if ($size !== '') {
                    // Remove redundant size text like (Large), (ធំ) from names
                    $nameDisplay = preg_replace('/ \((Large|Small|ធំ|តូច|Large Roll|Small Roll)\)/ui', '', $nameDisplay);
                }

                $delta = (float)($it['qty']) - (float)($it['old_qty'] ?? $it['qty']);
                
                if (isset($it['today_out']) || isset($it['today_in'])) {
                    $todayInRecorded  = (float)($it['today_in'] ?? 0);
                    $todayOutRecorded = (float)($it['today_out'] ?? 0);
                } else {
                    $targetDate = \Carbon\Carbon::parse($date)->toDateString();
                    $todayMovements = \App\Models\StockMovement::where('material_id', $it['id'])
                        ->where(function($q) use ($targetDate) {
                            $q->whereDate('movement_date', $targetDate)
                              ->orWhereDate('created_at', $targetDate);
                        })
                        ->get();
                    $todayInRecorded  = (float) $todayMovements->where('type', 'in')->sum('quantity');
                    $todayOutRecorded = (float) $todayMovements->where('type', 'out')->sum('quantity');
                }
                
                $totalOut = $todayOutRecorded + ($delta < 0 ? abs($delta) : 0);
                $totalIn  = $todayInRecorded  + ($delta > 0 ? $delta : 0);
                
                $usageText = '';
                if ($totalOut > 0 || $totalIn > 0) {
                    $parts = [];
                    if ($totalOut > 0) {
                        $parts[] = "បានប្រើ " . ($totalOut + 0) . " {$it['unit']}";
                    }
                    if ($totalIn > 0) {
                        $parts[] = "ចូលស្តុក " . ($totalIn + 0) . " {$it['unit']}";
                    }
                    $usageText = " (" . implode(', ', $parts) . ")";
                }

                $qtyStr = (number_format($it['qty'], 2) + 0);
                $itemLines[] = "- {$nameDisplay} : {$qtyStr} {$it['unit']}{$usageText}";
            }
        }
        $itemsText = trim(implode("\n", $itemLines));

        // Get template from settings
        $template = \App\Models\Setting::get('daily_report_template', 
            "សូមគោរពរាយការណ៍ជូនបង ពូ 📩\n{date}\n\n{emoji} {category} នៅសល់មានចំនួន:\n{items}\n{person}\n{hashtag}"
        );

        // Replace placeholders
        $message = str_replace([
            '{date}',
            '{emoji}',
            '{category}',
            '{items}',
            '{person}',
            '{hashtag}'
        ], [
            $dateStr,
            $headerEmoji,
            $catLabel,
            $itemsText,
            $by ? "\n👤 {$by}" : '',
            $catTag
        ], $template);

        $message = mb_substr($message, 0, 4096);

        if (!empty($imagePaths)) {
            if (count($imagePaths) === 1) {
                \App\Jobs\SendTelegramPhotoJob::dispatch($chatId, $imagePaths[0], $message, $threadId, true);
            } else {
                \App\Jobs\SendTelegramMediaGroupJob::dispatch($chatId, $imagePaths, $message, $threadId, true);
            }
        } else {
            \App\Jobs\SendTelegramMessageJob::dispatch($chatId, $message, $threadId);
        }
    }

    /**
     * Quick bulk entry — multiple materials at once.
     */
    public function bulkCreate(): View
    {
        $materials = Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('stock.movements.bulk', compact('materials'));
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'           => 'required|in:in,out',
            'movement_date'  => 'required|date',
            'performed_by'   => 'nullable|string|max:100',
            'reference'      => 'nullable|string|max:255',
            'items'          => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.notes'       => 'nullable|string|max:255',
        ]);

        $count = 0;
        $outItems = [];
        
        foreach ($data['items'] as $item) {
            $material = Material::find($item['material_id']);
            if (! $material) continue;

            if ($data['type'] === 'out' && $item['quantity'] > $material->currentStock()) {
                continue; // skip items that would go negative
            }

            $this->stockService->recordMovement(
                $material,
                $data['type'],
                $item['quantity'],
                $data['reference'] ?? null,
                $data['performed_by'] ?? null,
                $item['notes'] ?? null,
                $data['movement_date'],
            );

            $this->alertService->checkAndAlert($material);
            $count++;
            
            if ($data['type'] === 'out') {
                $outItems[] = [
                    'material' => $material,
                    'qty'      => $item['quantity'],
                    'notes'    => $item['notes'] ?? null
                ];
            }
        }

        if ($data['type'] === 'out' && !empty($outItems)) {
            $chatId = \App\Models\Setting::get('daily_usage_chat_id');
            if ($chatId) {
                $threadId = \App\Models\Setting::get('daily_usage_thread_id');
                $by = $data['performed_by'] ? " 👤 {$data['performed_by']}" : '';
                
                $msg = "📉 <b>Live Stock Usage (Bulk)</b>\n";
                foreach ($outItems as $out) {
                    $mat = $out['material'];
                    $itemName = $mat->name_km ?: $mat->name;
                    $qty = number_format($out['qty'], 2) + 0;
                    $msg .= "• " . htmlspecialchars($itemName) . ": បានប្រើ <b>-{$qty} {$mat->unit}</b> (សល់ <b>" . $mat->currentStock() . " {$mat->unit}</b>)\n";
                    if (!empty($out['notes'])) {
                        $msg .= "   📝 Note: " . htmlspecialchars($out['notes']) . "\n";
                    }
                }
                if ($by) {
                    $msg .= "\n$by";
                }
                
                $this->telegramService->sendMessage($chatId, $msg, $threadId ? (int)$threadId : null, 'HTML');
            }
        }

        $label = $data['type'] === 'in' ? 'Stock In' : 'Stock Out';
        return redirect()->route('stock.movements.index')
            ->with('success', "{$label}: {$count} items recorded");
    }

    /**
     * Person Report — who took what items in a given period.
     */
    public function personReport(Request $request): View
    {
        // ── Date range resolution ──────────────────────────────
        $period   = $request->query('period', 'this_week');
        $category = $request->query('category', '');
        $type     = $request->query('type', '');

        $today = now()->toDateString();

        switch ($period) {
            case 'last_week':
                $startDate = now()->subWeek()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
                $endDate   = now()->subWeek()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString();
                break;
            case 'this_month':
                $startDate = now()->startOfMonth()->toDateString();
                $endDate   = $today;
                break;
            case 'last_month':
                $startDate = now()->subMonth()->startOfMonth()->toDateString();
                $endDate   = now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'custom':
                $startDate = $request->query('start_date', now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString());
                $endDate   = $request->query('end_date', $today);
                break;
            default: // this_week
                $period    = 'this_week';
                $startDate = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
                $endDate   = $today;
                break;
        }

        // ── Query movements ────────────────────────────────────
        $query = StockMovement::with('material')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->whereNotNull('performed_by')
            ->where('performed_by', '!=', '');

        if ($category) {
            $query->whereHas('material', fn($q) => $q->where('category', $category));
        }
        if ($type) {
            $query->where('type', $type);
        }

        $movements = $query->orderBy('movement_date')->get();

        $totalMovements = $movements->count();
        $totalOut       = $movements->where('type', 'out')->count();
        $totalIn        = $movements->where('type', 'in')->count();

        // ── Normalize names for Khmer Unicode support ──────────
        // Problem: same Khmer name typed differently can produce different byte
        // sequences (extra spaces, invisible chars like zero-width space).
        // Solution: strip invisible chars + trim, then use that as the group key.
        $normalizedMovements = $movements->groupBy(function ($m) {
            $name = $m->performed_by;
            // Remove invisible/zero-width Unicode characters
            $name = preg_replace('/[\x{00AD}\x{200B}\x{200C}\x{200D}\x{200E}\x{200F}\x{FEFF}\x{2028}\x{2029}]/u', '', $name);
            // Collapse multiple whitespace (including non-breaking spaces) to single space
            $name = preg_replace('/[\s\x{00A0}\x{3000}]+/u', ' ', $name);
            // Trim and lowercase (only affects Latin chars, safe for Khmer)
            return mb_strtolower(trim($name), 'UTF-8');
        });

        // ── Group by person, then by material ─────────────────
        $personData = [];

        foreach ($normalizedMovements as $nameKey => $personMoves) {
            // Pick the most-used version of the name as the display name
            $displayName = $personMoves
                ->groupBy(fn($m) => trim($m->performed_by))
                ->map->count()
                ->sortDesc()
                ->keys()
                ->first() ?? $nameKey;

            $itemMap = [];

            foreach ($personMoves as $mv) {
                $mat = $mv->material;
                if (!$mat) continue;

                $key = $mat->id;
                if (!isset($itemMap[$key])) {
                    $itemMap[$key] = [
                        'name'      => $mat->name,
                        'name_km'   => $mat->name_km ?? '',
                        'category'  => $mat->category ?? 'other',
                        'unit'      => $mat->unit ?? '',
                        'out_qty'   => 0,
                        'in_qty'    => 0,
                        'adj_qty'   => 0,
                        'last_date' => null,
                        'reference' => null,
                    ];
                }

                match ($mv->type) {
                    'out'    => $itemMap[$key]['out_qty'] += (float) $mv->quantity,
                    'in'     => $itemMap[$key]['in_qty']  += (float) $mv->quantity,
                    'adjust' => $itemMap[$key]['adj_qty'] += (float) $mv->quantity,
                    default  => null,
                };

                // Track latest movement date + reference
                if (is_null($itemMap[$key]['last_date']) || $mv->movement_date > $itemMap[$key]['last_date']) {
                    $itemMap[$key]['last_date'] = $mv->movement_date;
                    $itemMap[$key]['reference'] = $mv->reference;
                }
            }

            // Sort items: most taken (out) first, then by name
            usort($itemMap, fn($a, $b) => $b['out_qty'] <=> $a['out_qty'] ?: strcmp($a['name'], $b['name']));

            $personData[] = [
                'name'           => $displayName,
                'movement_count' => $personMoves->count(),
                'item_count'     => count($itemMap),
                'days_active'    => $personMoves->groupBy(fn($m) => $m->movement_date->toDateString())->count(),
                'total_out'      => $personMoves->where('type', 'out')->count(),
                'total_in'       => $personMoves->where('type', 'in')->count(),
                'total_out_qty'  => $personMoves->where('type', 'out')->sum('quantity'),
                'total_in_qty'   => $personMoves->where('type', 'in')->sum('quantity'),
                'items'          => array_values($itemMap),
            ];
        }

        // Sort persons: most active first
        usort($personData, fn($a, $b) => $b['movement_count'] <=> $a['movement_count']);

        return view('stock.person-report', compact(
            'personData', 'period', 'category', 'type',
            'startDate', 'endDate',
            'totalMovements', 'totalOut', 'totalIn'
        ));
    }

    /**
     * Export stock movements to Excel
     */
    public function exportExcel(Request $request, ExcelExportService $exportService)
    {
        try {
            $query = StockMovement::with('material');
            
            // Apply filters if provided
            if ($request->filled('start_date')) {
                $query->where('movement_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('movement_date', '<=', $request->end_date);
            }
            if ($request->filled('material_id')) {
                $query->where('material_id', $request->material_id);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('category')) {
                $query->whereHas('material', fn($q) => $q->where('category', $request->category));
            }
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('performed_by', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('material', fn($mq) => 
                          $mq->where('name', 'like', "%{$search}%")
                             ->orWhere('name_km', 'like', "%{$search}%")
                             ->orWhere('code', 'like', "%{$search}%")
                      );
                });
            }
            
            $movements = $query->orderByDesc('movement_date')
                ->orderByDesc('created_at')
                ->get();
            
            $filename = 'stock_movements_' . now()->format('Y-m-d') . '.xlsx';
            
            return $exportService->exportStockMovements($movements, $filename);
        } catch (\Throwable $e) {
            \Log::error('Stock movements export failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return back()->with('error', 'មិនអាចទាញយក Excel បានទេ។ សូមព្យាយាមម្តងទៀត។ / Unable to export Excel. Please try again.');
        }
    }
}
