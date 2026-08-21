<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all materials and send alerts for those below the minimum threshold';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\AlertService $alertService)
    {
        $this->info('Starting low stock check...');
        $materials = \App\Models\Material::with('movements')->get();
        
        $alertCount = 0;
        $lowStockMaterials = collect();

        foreach ($materials as $material) {
            // Check if it's low stock for logging purposes
            if ($material->isLowStock()) {
                $alertCount++;
                $lowStockMaterials->push($material);
                $this->info("Found low stock for: {$material->name} (Current: {$material->currentStock()} | Min: {$material->min_stock})");
            }
        }

        if ($lowStockMaterials->isNotEmpty()) {
            $alertService->sendGroupedLowStockAlert($lowStockMaterials, false);
        }

        $this->info("Low stock check completed. Triggered {$alertCount} items.");
    }
}
