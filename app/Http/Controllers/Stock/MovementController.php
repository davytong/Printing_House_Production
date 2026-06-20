<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\StockMovement;
use App\Services\AlertService;
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
        );

        // Check low stock alert
        $this->alertService->checkAndAlert($material);

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

        $materials = Material::where('status', 'active')
            ->where('category', $category)
            ->orderBy('sub_type')
            ->orderBy('name')
            ->get()
            ->map(function ($m) {
                $m->calculated_stock = $m->currentStock();
                return $m;
            });

        $telegramGroups = \App\Models\TelegramGroup::orderBy('name')->get();

        // Auto-select the right group for this category
        $defaultGroup = \App\Models\TelegramGroup::forCategory($category);

        return view('stock.movements.daily', compact('materials', 'category', 'telegramGroups', 'defaultGroup'));
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
                $this->alertService->checkAndAlert($material);
                $count++;
            }

            $updated[] = [
                'name'    => $material->name,
                'name_km' => $material->name_km,
                'unit'    => $material->unit,
                'qty'     => $newQty,
            ];
        }

        // Store uploaded images
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->imageService->store($file, 'daily-reports');
                if ($path) $imagePaths[] = $path;
            }
        }

        // Send to Telegram
        if ($request->filled('chat_id') && $request->input('send_telegram')) {
            $threadId = $request->integer('message_thread_id') ?: null;
            $this->sendDailyTelegram(
                $data['category'],
                $data['update_date'],
                $data['performed_by'] ?? null,
                $updated,
                $request->input('chat_id'),
                $imagePaths,
                $threadId,
            );
        }

        // Clean up temp image files from storage after sending
        // (keep them — they are evidence/audit trail)

        $catLabel = match($data['category']) {
            'paper'      => 'ក្រដាស (Paper)',
            'film'       => 'Film (ហ្វីល)',
            'consumable' => 'Consumable (សម្ភារៈប្រើប្រាស់)',
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
        $catEmoji = match($category) {
            'paper'      => '📄',
            'film'       => '🎞️',
            'consumable' => '🧴',
            default      => '📦',
        };
        $catLabel = match($category) {
            'paper'      => 'ក្រដាស (Paper)',
            'film'       => 'Film (ហ្វីល)',
            'consumable' => 'Consumable (សម្ភារៈប្រើប្រាស់)',
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

        $lines = [
            "សូមគោរពរាយការណ៍ជូនបង ពូ 📩",
            $dateStr,
            "",
            "{$catEmoji} {$catLabel} នៅសល់មានចំនួន:",
        ];

        foreach ($items as $it) {
            // Bilingual: "Plate Cleaner — សាប៊ូជូតប្លាក : 23 bottle"
            $nameDisplay = $it['name_km']
                ? "{$it['name']} — {$it['name_km']}"
                : $it['name'];
            $lines[] = "- {$nameDisplay} : " . number_format($it['qty'], 0) . " {$it['unit']}";
        }

        if ($by) { $lines[] = ""; $lines[] = "👤 {$by}"; }
        $lines[] = $catTag;

        $message = mb_substr(implode("\n", $lines), 0, 4096);

        if (!empty($imagePaths)) {
            if (count($imagePaths) === 1) {
                $this->telegramService->sendPhoto($chatId, $imagePaths[0], $message, $threadId);
            } else {
                $this->telegramService->sendMediaGroup($chatId, $imagePaths, $message, $threadId);
            }
        } else {
            $this->telegramService->sendMessage($chatId, $message, $threadId);
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
        }

        $label = $data['type'] === 'in' ? 'Stock In' : 'Stock Out';
        return redirect()->route('stock.movements.index')
            ->with('success', "{$label}: {$count} items recorded");
    }
}
