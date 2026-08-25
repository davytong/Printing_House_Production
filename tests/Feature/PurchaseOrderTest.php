<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     * Test user can create a purchase order.
     */
    public function test_can_create_purchase_order(): void
    {
        $supplier = Supplier::create([
            'name' => 'Beltei Supplier Ltd',
            'contact_name' => 'Supplier Agent',
            'phone' => '012345678',
            'email' => 'supplier@beltei.edu.kh',
            'address' => 'Phnom Penh',
            'status' => 'active',
        ]);

        $inventoryItem = InventoryItem::create([
            'code' => 'INV-PAPER',
            'name' => 'Coated Paper A4',
            'category' => 'paper',
            'unit' => 'ream',
            'quantity_in_stock' => 10,
            'minimum_stock' => 5,
            'status' => 'active',
        ]);

        $response = $this->withSession([
            'user_name' => 'Manager User',
            'user_position' => 'manager',
            'user_role' => 'manager',
        ])->post('/purchase-orders', [
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(5)->toDateString(),
            'currency' => 'USD',
            'notes' => 'PO Test notes',
            'priority' => 'high',
            'reason' => 'Need paper supplies',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'items' => [
                [
                    'item_name' => 'Coated Paper A4',
                    'unit' => 'ream',
                    'quantity_ordered' => 50,
                    'unit_price' => 4.5,
                    'inventory_item_id' => $inventoryItem->id,
                    'notes' => 'Item 1 notes',
                ]
            ]
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'priority' => 'high',
        ]);

        $po = PurchaseOrder::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($po);
        $this->assertEquals(1, $po->items()->count());
    }

    /**
     * Test receiving items on a PO updates inventory stock.
     */
    public function test_receiving_po_items_updates_inventory_stock(): void
    {
        $supplier = Supplier::create([
            'name' => 'Beltei Supplier Ltd',
            'contact_name' => 'Supplier Agent',
            'phone' => '012345678',
            'status' => 'active',
        ]);

        $inventoryItem = InventoryItem::create([
            'code' => 'INV-PAPER',
            'name' => 'Coated Paper A4',
            'category' => 'paper',
            'unit' => 'ream',
            'quantity_in_stock' => 10,
            'minimum_stock' => 5,
            'status' => 'active',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'status' => 'sent',
            'order_date' => now()->toDateString(),
            'currency' => 'USD',
            'total_amount' => 225.0,
        ]);

        $poItem = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'inventory_item_id' => $inventoryItem->id,
            'item_name' => 'Coated Paper A4',
            'unit' => 'ream',
            'quantity_ordered' => 50,
            'quantity_received' => 0,
            'unit_price' => 4.5,
            'total_price' => 225.0,
        ]);

        // Receive 50 reams
        $response = $this->withSession([
            'user_name' => 'Admin User',
            'user_position' => 'admin',
            'user_role' => 'admin',
        ])->post("/purchase-orders/{$po->id}/receive", [
            'items' => [
                [
                    'id' => $poItem->id,
                    'quantity_received' => 50,
                ]
            ]
        ]);

        $response->assertStatus(302);

        $po->refresh();
        $this->assertEquals('received', $po->status);

        $poItem->refresh();
        $this->assertEquals(50, $poItem->quantity_received);

        // Inventory must increment from 10 to 60
        $inventoryItem->refresh();
        $this->assertEquals(60, $inventoryItem->quantity_in_stock);
    }
}
