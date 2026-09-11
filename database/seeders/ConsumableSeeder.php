<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class ConsumableSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name'      => 'Cyan Ink',
                'name_km'   => 'ទឹកថ្នាំ Cyan (ខៀវ)',
                'category'  => 'consumable',
                'sub_type'  => 'Ink',
                'unit'      => 'Bottle',
                'min_stock' => 5,
                'unit_cost' => 25.00,
                'icon'      => '💧',
                'opening'   => 12,
            ],
            [
                'name'      => 'Magenta Ink',
                'name_km'   => 'ទឹកថ្នាំ Magenta (ក្រហម)',
                'category'  => 'consumable',
                'sub_type'  => 'Ink',
                'unit'      => 'Bottle',
                'min_stock' => 5,
                'unit_cost' => 25.00,
                'icon'      => '🔴',
                'opening'   => 10,
            ],
            [
                'name'      => 'Yellow Ink',
                'name_km'   => 'ទឹកថ្នាំ Yellow (លឿង)',
                'category'  => 'consumable',
                'sub_type'  => 'Ink',
                'unit'      => 'Bottle',
                'min_stock' => 5,
                'unit_cost' => 25.00,
                'icon'      => '🟡',
                'opening'   => 8,
            ],
            [
                'name'      => 'Black Ink',
                'name_km'   => 'ទឹកថ្នាំ Black (ខ្មៅ)',
                'category'  => 'consumable',
                'sub_type'  => 'Ink',
                'unit'      => 'Bottle',
                'min_stock' => 5,
                'unit_cost' => 25.00,
                'icon'      => '⚫',
                'opening'   => 15,
            ],
            [
                'name'      => 'Cleaning Sponge',
                'name_km'   => 'ប្រឡះសម្អាត (Cleaning Sponge)',
                'category'  => 'consumable',
                'sub_type'  => 'Cleaning',
                'unit'      => 'Pcs',
                'min_stock' => 10,
                'unit_cost' => 3.50,
                'icon'      => '🧽',
                'opening'   => 20,
            ],
            [
                'name'      => 'Cleaning Liquid',
                'name_km'   => 'ទឹកសម្អាត (Cleaning Liquid)',
                'category'  => 'consumable',
                'sub_type'  => 'Cleaning',
                'unit'      => 'Bottle',
                'min_stock' => 3,
                'unit_cost' => 15.00,
                'icon'      => '🪠',
                'opening'   => 7,
            ],
            [
                'name'      => 'A4 Paper Pack (80gsm)',
                'name_km'   => 'ក្រដាស A4 80gsm',
                'category'  => 'paper',
                'sub_type'  => 'Pack',
                'unit'      => 'Pack',
                'min_stock' => 10,
                'unit_cost' => 4.50,
                'icon'      => '📄',
                'opening'   => 50,
            ],
        ];

        foreach ($items as $data) {
            $opening = $data['opening'];
            unset($data['opening']);

            $material = Material::firstOrCreate(
                ['name' => $data['name']],
                $data
            );

            if ($material->movements()->count() === 0) {
                StockMovement::create([
                    'material_id'   => $material->id,
                    'type'          => 'adjust',
                    'quantity'      => $opening,
                    'reference'     => 'Opening Stock',
                    'performed_by'  => 'System',
                    'notes'         => 'Initial consumable stock setup',
                    'movement_date' => now()->toDateString(),
                ]);
            }
        }
    }
}
