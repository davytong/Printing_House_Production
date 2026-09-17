<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TelegramMiniAppStockOutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_multi_item_stock_out_succeeds_and_deducts_all_items(): void
    {
        $mat1 = Material::create([
            'code' => 'TEST-M1',
            'name' => 'Cleaning Sponge Test',
            'category' => 'consumable',
            'unit' => 'pcs',
            'min_stock' => 2,
            'status' => 'active',
        ]);
        StockMovement::create([
            'material_id' => $mat1->id,
            'type' => 'in',
            'quantity' => 10,
            'movement_date' => now()->toDateString(),
        ]);

        $mat2 = Material::create([
            'code' => 'TEST-M2',
            'name' => 'Cyan Ink Test',
            'category' => 'consumable',
            'unit' => 'can',
            'min_stock' => 2,
            'status' => 'active',
        ]);
        StockMovement::create([
            'material_id' => $mat2->id,
            'type' => 'in',
            'quantity' => 8,
            'movement_date' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/telegram/app/stock-out', [
            'items' => [
                ['material_id' => $mat1->id, 'quantity' => 3],
                ['material_id' => $mat2->id, 'quantity' => 2],
            ],
            'reason' => 'Production',
            'notes' => 'Batch printing run',
            'performed_by' => 'Davy',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
        ]);

        $json = $response->json();
        $this->assertCount(2, $json['items']);
        $this->assertEquals(7, $mat1->currentStock());
        $this->assertEquals(6, $mat2->currentStock());

        // Verify ActivityLog was created
        $this->assertTrue(ActivityLog::where('module', 'inventory')->where('action', 'Stock Out')->exists());
    }

    public function test_multi_item_stock_out_fails_atomically_if_one_item_exceeds_stock(): void
    {
        $mat1 = Material::create([
            'code' => 'TEST-M3',
            'name' => 'Rubber Blanket Test',
            'category' => 'consumable',
            'unit' => 'sheet',
            'min_stock' => 1,
            'status' => 'active',
        ]);
        StockMovement::create([
            'material_id' => $mat1->id,
            'type' => 'in',
            'quantity' => 2,
            'movement_date' => now()->toDateString(),
        ]);

        $mat2 = Material::create([
            'code' => 'TEST-M4',
            'name' => 'Plate Cleaner Test',
            'category' => 'consumable',
            'unit' => 'bottle',
            'min_stock' => 1,
            'status' => 'active',
        ]);
        StockMovement::create([
            'material_id' => $mat2->id,
            'type' => 'in',
            'quantity' => 1,
            'movement_date' => now()->toDateString(),
        ]);

        // Requesting 1 from mat1 (valid) but 5 from mat2 (invalid, stock is only 1)
        $response = $this->postJson('/api/telegram/app/stock-out', [
            'items' => [
                ['material_id' => $mat1->id, 'quantity' => 1],
                ['material_id' => $mat2->id, 'quantity' => 5],
            ],
            'reason' => 'Cleaning',
            'performed_by' => 'Operator',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['ok' => false]);

        // Neither item should be deducted (transaction rolled back)
        $this->assertEquals(2, $mat1->currentStock());
        $this->assertEquals(1, $mat2->currentStock());
    }

    public function test_single_item_backward_compatibility(): void
    {
        $mat = Material::create([
            'code' => 'TEST-M5',
            'name' => 'Single Ink Test',
            'category' => 'consumable',
            'unit' => 'can',
            'min_stock' => 1,
            'status' => 'active',
        ]);
        StockMovement::create([
            'material_id' => $mat->id,
            'type' => 'in',
            'quantity' => 5,
            'movement_date' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/telegram/app/stock-out', [
            'material_id' => $mat->id,
            'quantity' => 2,
            'reason' => 'Production',
            'performed_by' => 'Davy',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'remaining' => 3,
        ]);
        $this->assertEquals(3, $mat->currentStock());
    }

    public function test_get_data_returns_rich_movement_metadata(): void
    {
        $mat = Material::create([
            'code'      => 'TEST-RICH-1',
            'name'      => 'Rich Meta Test',
            'category'  => 'consumable',
            'unit'      => 'pcs',
            'min_stock' => 1,
            'status'    => 'active',
        ]);
        StockMovement::create([
            'material_id'   => $mat->id,
            'type'          => 'out',
            'quantity'      => 3,
            'reason'        => 'Production',
            'performed_by'  => 'Davy',
            'movement_date' => now()->toDateString(),
        ]);
        StockMovement::create([
            'material_id'   => $mat->id,
            'type'          => 'adjust',
            'quantity'      => 10,
            'movement_date' => now()->toDateString(),
        ]);

        // Default 'out' filter
        $response = $this->getJson('/api/telegram/app/data?type=out');
        $response->assertStatus(200)->assertJson(['ok' => true]);
        $json = $response->json();
        $outRecords = collect($json['recent'])->where('material', 'Rich Meta Test');
        $this->assertNotEmpty($outRecords->values());
        foreach ($outRecords as $r) {
            $this->assertEquals('out', $r['type']);
            $this->assertTrue($r['can_resend']);
            $this->assertStringContainsString('-', $r['qty_display']);
            $this->assertEquals('text-danger', $r['qty_class']);
        }

        // 'adjust' filter
        $resp2 = $this->getJson('/api/telegram/app/data?type=adjust');
        $resp2->assertStatus(200);
        $json2 = $resp2->json();
        $adjRecords = collect($json2['recent'])->where('material', 'Rich Meta Test');
        foreach ($adjRecords as $r) {
            $this->assertEquals('adjust', $r['type']);
            $this->assertFalse($r['can_resend']);
            $this->assertStringContainsString('ស្តុក:', $r['qty_display']);
        }
    }

    public function test_resend_returns_error_for_non_out_movement(): void
    {
        $mat = Material::create([
            'code'      => 'TEST-RESEND-1',
            'name'      => 'Resend Test Material',
            'category'  => 'consumable',
            'unit'      => 'pcs',
            'min_stock' => 1,
            'status'    => 'active',
        ]);
        $movement = StockMovement::create([
            'material_id'   => $mat->id,
            'type'          => 'adjust',
            'quantity'      => 5,
            'movement_date' => now()->toDateString(),
        ]);

        $response = $this->postJson("/api/telegram/app/resend/{$movement->id}");
        $response->assertStatus(422)->assertJson(['ok' => false]);
    }

    public function test_resend_returns_404_for_missing_movement(): void
    {
        $response = $this->postJson('/api/telegram/app/resend/999999');
        $response->assertStatus(404)->assertJson(['ok' => false]);
    }

    public function test_resend_succeeds_for_stock_out_movement(): void
    {
        $mat = Material::create([
            'code'      => 'TEST-RESEND-2',
            'name'      => 'Resend Out Material',
            'category'  => 'consumable',
            'unit'      => 'bottle',
            'min_stock' => 1,
            'status'    => 'active',
        ]);
        StockMovement::create([
            'material_id'   => $mat->id,
            'type'          => 'in',
            'quantity'      => 10,
            'movement_date' => now()->toDateString(),
        ]);
        $outMovement = StockMovement::create([
            'material_id'   => $mat->id,
            'type'          => 'out',
            'quantity'      => 2,
            'reason'        => 'Production',
            'performed_by'  => 'Davy',
            'movement_date' => now()->toDateString(),
        ]);

        // No Telegram chat configured -> ok:true but sent:false
        \App\Models\Setting::where('key', 'stock_out_chat_id')->delete();
        \App\Models\Setting::where('key', 'daily_usage_chat_id')->delete();

        $response = $this->postJson("/api/telegram/app/resend/{$outMovement->id}");
        $response->assertStatus(200)->assertJson(['ok' => true]);
        $json = $response->json();
        $this->assertEquals(1, $json['items']);
        $this->assertStringContainsString('SO-', $json['ref']);
    }
}
