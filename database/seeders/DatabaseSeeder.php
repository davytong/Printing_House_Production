<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Material;
use App\Models\PrintRequest;
use App\Models\PrintRequestItem;
use App\Models\ProductionBatch;
use App\Models\ProductionTask;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Suppliers
        $sup1 = Supplier::create(['name' => 'ABC Paper Co.', 'contact_person' => 'Mr. Sok', 'email' => 'contact@abcpaper.com', 'phone' => '012345678', 'status' => 'active']);
        $sup2 = Supplier::create(['name' => 'Khmer Ink Supplies', 'contact_person' => 'Ms. Roth', 'email' => 'sales@khmerink.com', 'phone' => '098765432', 'status' => 'active']);

        // 2. Create Materials (Inventory)
        $paper1 = Material::create([
            'code' => 'MAT-P-001', 'name' => 'A4 80gsm Paper Roll', 'category' => 'paper', 'sub_type' => 'Roll',
            'unit' => 'kg', 'min_stock' => 500, 'unit_cost' => 1.20
        ]);
        $paper2 = Material::create([
            'code' => 'MAT-P-002', 'name' => 'A5 Cover Paper 250gsm', 'category' => 'paper', 'sub_type' => 'Sheet',
            'unit' => 'packs', 'min_stock' => 100, 'unit_cost' => 5.50
        ]);
        $inkCyan = Material::create([
            'code' => 'MAT-I-001', 'name' => 'Cyan Ink Drum', 'category' => 'ink', 'sub_type' => 'Liquid',
            'unit' => 'L', 'min_stock' => 20, 'unit_cost' => 45.00
        ]);
        $inkMag = Material::create([
            'code' => 'MAT-I-002', 'name' => 'Magenta Ink Drum', 'category' => 'ink', 'sub_type' => 'Liquid',
            'unit' => 'L', 'min_stock' => 20, 'unit_cost' => 45.00
        ]);
        $plate = Material::create([
            'code' => 'MAT-PL-001', 'name' => 'Thermal CTP Plate', 'category' => 'plate', 'sub_type' => 'Plate',
            'unit' => 'pcs', 'min_stock' => 100, 'unit_cost' => 3.20
        ]);

        // 3. Create Machines
        $mac1 = Machine::create(['name' => 'Speedmaster XL 106', 'model' => 'XL 106-8-P', 'type' => 'offset', 'status' => 'operational', 'maintenance_interval_days' => 30]);
        $mac2 = Machine::create(['name' => 'Webpress Sunday 2000', 'model' => 'S2000', 'type' => 'offset', 'status' => 'operational', 'maintenance_interval_days' => 45]);
        $mac3 = Machine::create(['name' => 'Polar Cutter 137', 'model' => '137 ED', 'type' => 'cutting', 'status' => 'maintenance', 'maintenance_interval_days' => 60]);

        // 4. Create Print Requests
        $req1 = PrintRequest::create([
            'title' => 'Khmer Language Textbooks 2026',
            'requester_name' => 'Ministry of Education',
            'priority' => 'high',
            'status' => 'approved',
            'approved_by' => 'Admin Manager',
            'approved_at' => now()->subDays(2),
            'required_by' => now()->addDays(14),
            'quantity_requested' => 50000,
            'notes' => 'Urgent for new school year.'
        ]);
        
        PrintRequestItem::create(['print_request_id' => $req1->id, 'book_title' => 'Khmer Grade 1', 'quantity_requested' => 25000, 'notes' => 'Perfect binding']);
        PrintRequestItem::create(['print_request_id' => $req1->id, 'book_title' => 'Khmer Grade 2', 'quantity_requested' => 25000]);

        $req2 = PrintRequest::create([
            'title' => 'Mathematics Textbooks',
            'requester_name' => 'BELTEI International School',
            'priority' => 'normal',
            'status' => 'pending',
            'required_by' => now()->addDays(30),
            'quantity_requested' => 10000,
        ]);
        PrintRequestItem::create(['print_request_id' => $req2->id, 'book_title' => 'Math Grade 4', 'quantity_requested' => 10000]);

        // 5. Create Production Schedules (Grid)
        \App\Models\ProductionSchedule::create([
            'year' => date('Y'),
            'month' => date('n'),
            'process' => 'Design',
            'day' => date('j'),
            'task' => 'Math Grade 4 Cover',
            'color' => '#3b82f6', // blue
            'status' => 'in_progress'
        ]);

        \App\Models\ProductionSchedule::create([
            'year' => date('Y'),
            'month' => date('n'),
            'process' => 'Press',
            'day' => date('j'),
            'task' => 'Khmer Grade 1 Text',
            'color' => '#ef4444', // red
            'status' => 'done'
        ]);
        
        \App\Models\ProductionSchedule::create([
            'year' => date('Y'),
            'month' => date('n'),
            'process' => 'Binding',
            'day' => date('j', strtotime('+2 days')),
            'task' => 'Khmer Grade 1 Binding',
            'color' => '#8b5cf6', // purple
            'status' => 'planned'
        ]);

        $batch2 = ProductionBatch::create([
            'name' => 'PB-' . date('Ymd') . '-02',
            'status' => 'active',
            'started_at' => now(),
        ]);
        // (Purchase Orders skipped to avoid legacy schema conflicts)

        // 7. Stock Movements (Logs)
        StockMovement::create(['material_id' => $paper1->id, 'type' => 'adjust', 'quantity' => 1500, 'reference' => 'Opening Stock', 'movement_date' => now()->subDays(10)]);
        StockMovement::create(['material_id' => $paper1->id, 'type' => 'out', 'quantity' => 50, 'reference' => 'Usage PB-01', 'movement_date' => now()->subDay()]);
        StockMovement::create(['material_id' => $paper2->id, 'type' => 'adjust', 'quantity' => 200, 'reference' => 'Opening Stock', 'movement_date' => now()->subDays(10)]);
        StockMovement::create(['material_id' => $inkCyan->id, 'type' => 'adjust', 'quantity' => 50, 'reference' => 'Opening Stock', 'movement_date' => now()->subDays(10)]);
        StockMovement::create(['material_id' => $inkMag->id, 'type' => 'adjust', 'quantity' => 15, 'reference' => 'Opening Stock', 'movement_date' => now()->subDays(10)]);
        StockMovement::create(['material_id' => $plate->id, 'type' => 'adjust', 'quantity' => 350, 'reference' => 'Opening Stock', 'movement_date' => now()->subDays(10)]);
    }
}

