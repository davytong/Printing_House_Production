<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    /**
     * Test single stock in movement store.
     */
    public function test_can_record_stock_in_movement(): void
    {
        $material = Material::create([
            'code' => 'P-100',
            'name' => 'Paper A4 80g',
            'name_km' => 'ក្រដាស A4 80g',
            'category' => 'paper',
            'sub_type' => 'A4',
            'unit' => 'ream',
            'unit_cost' => 5.0,
            'minimum_stock' => 10,
            'status' => 'active',
        ]);

        $response = $this->withSession([
            'user_name' => 'Store Keeper',
            'user_position' => 'store',
            'user_role' => 'store',
        ])->post('/stock/movements', [
            'material_id' => $material->id,
            'type' => 'in',
            'quantity' => 100,
            'reference' => 'PO-123',
            'movement_date' => now()->toDateString(),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/stock/movements');

        // Assert movement is recorded
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $material->id,
            'type' => 'in',
            'quantity' => 100,
            'reference' => 'PO-123',
        ]);

        // Assert calculated stock is now 100
        $this->assertEquals(100, $material->currentStock());
    }

    /**
     * Test stock out cannot exceed current stock.
     */
    public function test_cannot_checkout_exceeding_current_stock(): void
    {
        $material = Material::create([
            'code' => 'P-200',
            'name' => 'Paper A3',
            'name_km' => 'ក្រដាស A3',
            'category' => 'paper',
            'sub_type' => 'A3',
            'unit' => 'ream',
            'unit_cost' => 8.0,
            'minimum_stock' => 5,
            'status' => 'active',
        ]);

        // Record stock in of 5 reams first
        StockMovement::create([
            'material_id' => $material->id,
            'type' => 'in',
            'quantity' => 5,
            'movement_date' => now()->toDateString(),
        ]);

        // Attempt checkout of 10 reams
        $response = $this->withSession([
            'user_name' => 'Reporters',
            'user_position' => 'press_report',
            'user_role' => 'reporter',
        ])->post('/stock/movements', [
            'material_id' => $material->id,
            'type' => 'out',
            'quantity' => 10,
            'movement_date' => now()->toDateString(),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Assert stock remains 5
        $this->assertEquals(5, $material->currentStock());
    }

    /**
     * Test daily bulk update stock adjustments.
     */
    public function test_can_process_daily_bulk_stock_adjustments(): void
    {
        $material1 = Material::create([
            'code' => 'F-10',
            'name' => 'Glossy Film',
            'name_km' => 'ស្គុតរអិល',
            'category' => 'film',
            'sub_type' => 'glossy',
            'unit' => 'roll',
            'unit_cost' => 12.0,
            'minimum_stock' => 2,
            'status' => 'active',
        ]);

        // Initialize stock with 10 rolls
        StockMovement::create([
            'material_id' => $material1->id,
            'type' => 'in',
            'quantity' => 10,
            'movement_date' => now()->toDateString(),
        ]);

        $response = $this->withSession([
            'user_name' => 'Store Keeper',
            'user_position' => 'store',
            'user_role' => 'store',
        ])->post('/stock/movements/daily', [
            'category' => 'film',
            'update_date' => now()->toDateString(),
            'send_telegram' => 0,
            'items' => [
                [
                    'material_id' => $material1->id,
                    'current_stock' => 7, // Adjust stock from 10 to 7
                ]
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Assert adjustment movement is recorded
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $material1->id,
            'type' => 'adjust',
            'quantity' => 7,
        ]);

        // Assert new stock is 7
        $this->assertEquals(7, $material1->currentStock());
    }
}
