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

    /**
     * Test daily update attaches recorded today_out and today_in movements.
     */
    public function test_daily_update_attaches_today_movements(): void
    {
        $material = Material::create([
            'code' => 'C-01',
            'name' => 'Cleaning Soap',
            'name_km' => 'សាប៊ូជូតស្អាត',
            'category' => 'consumable',
            'unit' => 'bottle',
            'unit_cost' => 2.0,
            'min_stock' => 5,
            'status' => 'active',
        ]);

        // Record initial stock IN 10
        StockMovement::create([
            'material_id' => $material->id,
            'type' => 'in',
            'quantity' => 10,
            'movement_date' => now()->toDateString(),
        ]);

        // Record Stock OUT 1 bottle today
        StockMovement::create([
            'material_id' => $material->id,
            'type' => 'out',
            'quantity' => 1,
            'movement_date' => now()->toDateString(),
        ]);

        // GET daily update page
        $response = $this->get('/stock/movements/daily?category=consumable');
        $response->assertStatus(200);
        $response->assertSee('data-today-out="1"', false);

        // GET daily stats endpoint
        $statsResponse = $this->get('/stock/movements/daily-stats?category=consumable&date=' . now()->toDateString());
        $statsResponse->assertStatus(200);
        $statsResponse->assertJsonFragment([
            $material->id => [
                'today_in' => 10,
                'today_out' => 1,
            ]
        ]);
    }

    /**
     * Test saving daily update resets today_out so (បានប្រើ) does not persist after save.
     */
    public function test_saving_daily_update_resets_today_movements(): void
    {
        $material = Material::create([
            'code'      => 'C-RESET-1',
            'name'      => 'Plate Cleaner Reset Test',
            'name_km'   => 'សាប៊ូជូតស្អាត តេស្ត',
            'category'  => 'consumable',
            'unit'      => 'bottle',
            'unit_cost' => 2.0,
            'min_stock' => 5,
            'status'    => 'active',
        ]);

        // Record initial stock IN 10
        StockMovement::create([
            'material_id'   => $material->id,
            'type'          => 'in',
            'quantity'      => 10,
            'movement_date' => now()->toDateString(),
        ]);

        // Record Stock OUT 2 bottles today
        StockMovement::create([
            'material_id'   => $material->id,
            'type'          => 'out',
            'quantity'      => 2,
            'movement_date' => now()->toDateString(),
        ]);

        $this->assertEquals(8, $material->currentStock());

        // Prior to saving, daily update shows today_out = 2
        $response = $this->get('/stock/movements/daily?category=consumable');
        $response->assertStatus(200);
        $response->assertSee('data-today-out="2"', false);

        // User saves daily update confirming current stock of 8
        $postResponse = $this->post('/stock/movements/daily', [
            'category'      => 'consumable',
            'update_date'   => now()->toDateString(),
            'performed_by'  => 'Tester',
            'send_telegram' => 0,
            'items'         => [
                [
                    'material_id'   => $material->id,
                    'current_stock' => 8,
                ]
            ]
        ]);
        $postResponse->assertRedirect();

        // After saving, reload daily update: today_out MUST be 0 (reset)
        $reloaded = $this->get('/stock/movements/daily?category=consumable');
        $reloaded->assertStatus(200);
        $reloaded->assertSee('data-today-out="0"', false);
        $reloaded->assertDontSee('data-today-out="2"', false);

        // Daily stats endpoint also returns 0 for today_out
        $stats = $this->get('/stock/movements/daily-stats?category=consumable&date=' . now()->toDateString());
        $stats->assertStatus(200);
        $stats->assertJsonFragment([
            $material->id => [
                'today_in'  => 0,
                'today_out' => 0,
            ]
        ]);

        // If a new stock out occurs AFTER the save, it captures only the new movement (1)
        StockMovement::create([
            'material_id'   => $material->id,
            'type'          => 'out',
            'quantity'      => 1,
            'movement_date' => now()->toDateString(),
        ]);

        $this->assertEquals(7, $material->currentStock());

        $afterNewOut = $this->get('/stock/movements/daily?category=consumable');
        $afterNewOut->assertStatus(200);
        $afterNewOut->assertSee('data-today-out="1"', false);
    }
}

