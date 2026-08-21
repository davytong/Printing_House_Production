<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\AlertService;

class SendBatchAlertJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 60;
    public $tries   = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public array $materialIds
    ) {}

    public function handle(AlertService $alertService): void
    {
        $materials = \App\Models\Material::whereIn('id', $this->materialIds)->with('movements')->get();
        if ($materials->isNotEmpty()) {
            $alertService->sendGroupedLowStockAlert($materials, false);
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SendBatchAlertJob failed permanently', [
            'material_ids' => $this->materialIds,
            'error'        => $exception->getMessage(),
        ]);
    }
}
