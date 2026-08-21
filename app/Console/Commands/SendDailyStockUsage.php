<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\TelegramGroup;
use App\Services\TelegramService;
use Carbon\Carbon;

class SendDailyStockUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-stock-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a report of yesterday\'s stock usage to the Telegram group';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        // Fetch destination from settings
        $chatId = \App\Models\Setting::get('daily_usage_chat_id');
        $threadId = \App\Models\Setting::get('daily_usage_thread_id');
        
        if (!$chatId) {
            $this->warn('No Telegram target configured for Daily Stock Usage report.');
            return;
        }

        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        // Get all materials that had ANY movement yesterday
        $materials = Material::whereHas('movements', function($q) use ($yesterday) {
            $q->whereDate('movement_date', $yesterday);
        })->get();

        $message = "📉 *Daily Stock Usage Report*\n";
        $message .= "Date: " . $yesterday->format('d/m/Y') . "\n\n";

        if ($materials->isEmpty()) {
            $message .= "No stock was used or added yesterday. 💤\n";
        } else {
            $categories = $materials->groupBy('category');

            foreach ($categories as $category => $items) {
                // Get the emoji for the category from the first item
                $emoji = $items->first()->categoryEmoji();
                $catName = ucfirst($category);
                $message .= "{$emoji} *{$catName}*\n";

                foreach ($items as $material) {
                    // Get all movements up to yesterday (to calculate starting stock of yesterday)
                    $movementsBeforeYesterday = StockMovement::where('material_id', $material->id)
                        ->where('movement_date', '<', $yesterday)
                        ->get();
                    $startStock = Material::currentStockFromMovements($movementsBeforeYesterday);

                    // Get yesterday's movements
                    $yesterdayMovements = StockMovement::where('material_id', $material->id)
                        ->whereDate('movement_date', $yesterday)
                        ->get();
                    
                    $used = $yesterdayMovements->where('type', 'out')->sum('quantity');
                    $added = $yesterdayMovements->where('type', 'in')->sum('quantity');

                    // Current stock (as of today)
                    $currentStock = $material->currentStock();
                    
                    $unit = $material->unit;
                    $itemName = $material->name_km ? $material->name_km : $material->name;

                    // Format: "Item Name: 12 -> 10 (Used: 2)"
                    $message .= "• {$itemName}: ";
                    if ($startStock != $currentStock) {
                        $message .= "{$startStock} ➔ *{$currentStock}* {$unit}";
                    } else {
                        $message .= "*{$currentStock}* {$unit}";
                    }

                    if ($used > 0) {
                        $message .= " (Used: -{$used})";
                    }
                    if ($added > 0) {
                        $message .= " (Added: +{$added})";
                    }
                    $message .= "\n";
                }
                $message .= "\n";
            }
        }

        // Send message
        $telegram->sendMessage($chatId, $message, $threadId ? (int)$threadId : null, 'Markdown');

        $this->info('Daily Stock Usage report sent to Telegram!');
    }
}
