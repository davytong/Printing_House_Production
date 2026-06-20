<?php

namespace App\Services;

use App\Models\Material;
use App\Models\StockReport;

class ReportService
{
    public function __construct(
        private StockService $stockService,
        private TelegramService $telegramService,
    ) {}

    /**
     * Create a stock report with snapshot of current stock.
     */
    public function createReport(
        string $date,
        ?string $title = null,
        ?string $notes = null,
        ?string $imagePath = null,
        ?string $createdBy = null,
        ?string $category = null,
    ): StockReport {
        // Build summary snapshot (filtered to category if given)
        $summary = $this->buildSummary($category);

        return StockReport::create([
            'report_date'  => $date,
            'category'     => $category,
            'title'        => $title,
            'notes'        => $notes,
            'image_path'   => $imagePath,
            'summary_data' => $summary,
            'created_by'   => $createdBy,
        ]);
    }

    /**
     * Build the summary data array.
     *
     * @param  string|null  $category  paper|film|offset, or null for all
     */
    public function buildSummary(?string $category = null): array
    {
        $materials = Material::where('status', 'active')->get();

        $cats = $category ? [$category] : ['paper', 'film', 'offset', 'consumable'];

        $categories = [];
        foreach ($cats as $cat) {
            $catMats = $materials->where('category', $cat);
            $items   = [];
            $lowCount = 0;

            foreach ($catMats as $m) {
                $stock = $m->currentStock();
                $isLow = $stock <= (float) $m->min_stock;
                if ($isLow) $lowCount++;

                $items[] = [
                    'name'     => $m->name,
                    'sub_type' => $m->sub_type,
                    'size'     => $m->size,
                    'unit'     => $m->unit,
                    'stock'    => $stock,
                    'min'      => (float) $m->min_stock,
                    'is_low'   => $isLow,
                ];
            }

            $categories[$cat] = [
                'label' => match($cat) { 'paper'=>'ក្រដាស (Paper)','film'=>'Film (ហ្វីល)','offset'=>'Offset','consumable'=>'Consumable (សម្ភារៈប្រើប្រាស់)',default=>$cat },
                'count'      => $catMats->count(),
                'low_count'  => $lowCount,
                'items'      => $items,
            ];
        }

        return [
            'date'       => now()->toDateString(),
            'total'      => $materials->count(),
            'categories' => $categories,
        ];
    }

    /**
     * Send a report to Telegram.
     */
    public function sendToTelegram(StockReport $report, string $chatId, ?int $threadId = null): bool
    {
        $caption = $this->buildCaption($report);

        $success = false;
        if ($report->image_path) {
            $success = $this->telegramService->sendPhoto($chatId, $report->image_path, $caption, $threadId);
        } else {
            $success = $this->telegramService->sendMessage($chatId, $caption, $threadId);
        }

        if ($success) {
            $report->update(['telegram_sent' => true, 'sent_at' => now()]);
        }

        return $success;
    }

    /**
     * Build formatted Khmer caption for Telegram.
     */
    public function buildCaption(StockReport $report): string
    {
        $summary = $report->summary_data;
        $date    = $report->report_date->format('d/m/Y');

        $catLabel = $report->categoryLabel();

        $lines = [
            "📄 សូមគោរពរាយការណ៍ Stock — {$catLabel}",
            "📅 {$date}",
            "",
            "📦 សង្ខេប:",
        ];

        if ($summary && isset($summary['categories'])) {
            foreach ($summary['categories'] as $cat => $data) {
                $emoji = match($cat) { 'paper'=>'📄','film'=>'🎞️','offset'=>'🖨️',default=>'📦' };
                $lines[] = "{$emoji} {$data['label']}: {$data['count']} items" .
                    ($data['low_count'] > 0 ? " ⚠️{$data['low_count']} low" : " ✅");
            }
        }

        // Low stock warnings
        $lowItems = [];
        if ($summary && isset($summary['categories'])) {
            foreach ($summary['categories'] as $data) {
                foreach ($data['items'] as $item) {
                    if ($item['is_low']) {
                        $lowItems[] = "🔴 {$item['name']}: {$item['stock']} {$item['unit']}";
                    }
                }
            }
        }

        if ($lowItems) {
            $lines[] = "";
            $lines[] = "⚠️ Stock ទាប:";
            foreach (array_slice($lowItems, 0, 5) as $l) { // max 5 in caption
                $lines[] = $l;
            }
            if (count($lowItems) > 5) {
                $lines[] = "... +" . (count($lowItems) - 5) . " more";
            }
        }

        if ($report->notes) {
            $lines[] = "";
            $lines[] = "📝 " . mb_substr($report->notes, 0, 200);
        }

        return implode("\n", $lines);
    }
}
