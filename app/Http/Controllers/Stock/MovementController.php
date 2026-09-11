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

    public function index(): View
    {
        $movements = StockMovement::with('material')
            ->orderByDesc('movement_date')
            ->orderByDesc('created_at')
            ->paginate(30);

        $todayMovements = $this->stockService->getTodayMovements();

        return view('stock.movements.index', compact('movements', 'todayMovements'));
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

        $movementsToday = StockMovement::whereIn('material_id', $materials->pluck('id'))
            ->where(function($q) use ($updateDate) {
                $q->whereDate('movement_date', $updateDate)
                  ->orWhereDate('created_at', $updateDate);
            })
            ->get()
            ->groupBy('material_id');

        $materials->map(function ($m) use ($movementsToday) {
            $m->calculated_stock = $m->currentStock();
            $moves = $movementsToday->get($m->id, collect());
            $m->today_in  = (float) $moves->where('type', 'in')->sum('quantity');
            $m->today_out = (float) $moves->where('type', 'out')->sum('quantity');
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

        $materialIds = Material::where('status', 'active')
            ->where('category', $category)
            ->pluck('id');

        $movements = StockMovement::whereIn('material_id', $materialIds)
            ->where(function($q) use ($date) {
                $q->whereDate('movement_date', $date)
                  ->orWhereDate('created_at', $date);
            })
            ->get()
            ->groupBy('material_id');

        $stats = [];
        foreach ($materialIds as $id) {
            $moves = $movements->get($id, collect());
            $stats[$id] = [
                'today_in'  => (float) $moves->where('type', 'in')->sum('quantity'),
                'today_out' => (float) $moves->where('type', 'out')->sum('quantity'),
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
                foreach ($data['items'] as $item) {
                    $material = Material::find($item['material_id']);
                    if (! $material) continue;

                    $newQty = (float) $item['current_stock'];
                    $oldQty = $material->currentStock();

                    if (abs($newQty - $oldQty) >= 0.01) {
                        $this->stockService->recordMovement(
                            $material, 'adjust', $newQty,
                            'Daily update', $data['performed_by'] ?? null,
                            null, $data['update_date'],
                        );
                        $count++;
                    }

                    $updated[] = [
                        'id'      => $material->id,
                        'name'    => $material->name,
                        'name_km' => $material->name_km,
                        'size'    => $material->size,
                        'unit'    => $material->unit,
                        'qty'     => $newQty,
                        'old_qty' => $oldQty,
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

                $targetDate = \Carbon\Carbon::parse($date)->toDateString();
                $delta = (float)($it['qty']) - (float)($it['old_qty'] ?? $it['qty']);
                
                // Check actual in/out movements for the target date
                $todayMovements = \App\Models\StockMovement::where('material_id', $it['id'])
                    ->where(function($q) use ($targetDate) {
                        $q->whereDate('movement_date', $targetDate)
                          ->orWhereDate('created_at', $targetDate);
                    })
                    ->get();
                
                $todayInRecorded  = (float) $todayMovements->where('type', 'in')->sum('quantity');
                $todayOutRecorded = (float) $todayMovements->where('type', 'out')->sum('quantity');
                
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
