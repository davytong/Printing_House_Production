<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\ProductionProcess;
use App\Models\ProductionTemplate;
use App\Models\ProductionTemplateProcess;
use Illuminate\Database\Seeder;

class ProductionPlanningSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Default Processes
        $processes = [
            ['name' => 'Design',    'code' => 'DSN', 'sequence' => 1,  'default_capacity' => 10000, 'color' => '#3b82f6'],
            ['name' => 'Prepress',  'code' => 'PRE', 'sequence' => 2,  'default_capacity' => 10000, 'color' => '#6366f1'],
            ['name' => 'Press',     'code' => 'PRS', 'sequence' => 3,  'default_capacity' => 8000,  'color' => '#ef4444'],
            ['name' => 'Digital',   'code' => 'DIG', 'sequence' => 4,  'default_capacity' => 12000, 'color' => '#8b5cf6'],
            ['name' => 'Folding',   'code' => 'FLD', 'sequence' => 5,  'default_capacity' => 10000, 'color' => '#d946ef'],
            ['name' => 'Gathering', 'code' => 'GAT', 'sequence' => 6,  'default_capacity' => 8000,  'color' => '#f59e0b'],
            ['name' => 'Staple',    'code' => 'STP', 'sequence' => 7,  'default_capacity' => 6000,  'color' => '#06b6d4'],
            ['name' => 'Binding',   'code' => 'BND', 'sequence' => 8,  'default_capacity' => 6000,  'color' => '#ec4899'],
            ['name' => 'Cutting',   'code' => 'CUT', 'sequence' => 9,  'default_capacity' => 15000, 'color' => '#14b8a6'],
            ['name' => 'Lamination','code' => 'LAM','sequence' => 10, 'default_capacity' => 8000,  'color' => '#e11d48'],
            ['name' => 'Packaging', 'code' => 'PKG', 'sequence' => 11, 'default_capacity' => 10000, 'color' => '#10b981'],
            ['name' => 'Delivery',  'code' => 'DLV', 'sequence' => 12, 'default_capacity' => 20000, 'color' => '#f97316'],
            ['name' => 'Other',     'code' => 'OTH', 'sequence' => 13, 'default_capacity' => 10000, 'color' => '#64748b'],
        ];

        foreach ($processes as $p) {
            ProductionProcess::updateOrCreate(
                ['name' => $p['name']],
                [
                    'code'             => $p['code'],
                    'sequence'         => $p['sequence'],
                    'default_capacity' => $p['default_capacity'],
                    'color'            => $p['color'],
                    'is_active'        => true,
                ]
            );
        }

        // 2. Default Templates (Shop Floor Production Pipeline without Delivery)
        $templates = [
            [
                'name' => 'Textbook (Perfect Binding)',
                'description' => 'Standard textbook pipeline: Press -> Folding -> Gathering -> Binding -> Cutting -> Packaging',
                'stages' => [
                    ['process_name' => 'Press',     'sequence' => 1, 'capacity' => 8000],
                    ['process_name' => 'Folding',   'sequence' => 2, 'capacity' => 10000],
                    ['process_name' => 'Gathering', 'sequence' => 3, 'capacity' => 8000],
                    ['process_name' => 'Binding',   'sequence' => 4, 'capacity' => 6000],
                    ['process_name' => 'Cutting',   'sequence' => 5, 'capacity' => 15000],
                    ['process_name' => 'Packaging', 'sequence' => 6, 'capacity' => 10000],
                ],
            ],
            [
                'name' => 'Workbook (Saddle Stitch / Staple)',
                'description' => 'Staple workbook pipeline: Press -> Folding -> Gathering -> Staple -> Cutting -> Packaging',
                'stages' => [
                    ['process_name' => 'Press',     'sequence' => 1, 'capacity' => 8000],
                    ['process_name' => 'Folding',   'sequence' => 2, 'capacity' => 10000],
                    ['process_name' => 'Gathering', 'sequence' => 3, 'capacity' => 8000],
                    ['process_name' => 'Staple',    'sequence' => 4, 'capacity' => 6000],
                    ['process_name' => 'Cutting',   'sequence' => 5, 'capacity' => 15000],
                    ['process_name' => 'Packaging', 'sequence' => 6, 'capacity' => 10000],
                ],
            ],
            [
                'name' => 'Cover / Laminated Sheet',
                'description' => 'Cover production: Design -> Press -> Lamination -> Cutting',
                'stages' => [
                    ['process_name' => 'Design',    'sequence' => 1, 'capacity' => 10000],
                    ['process_name' => 'Press',     'sequence' => 2, 'capacity' => 8000],
                    ['process_name' => 'Lamination','sequence' => 3, 'capacity' => 8000],
                    ['process_name' => 'Cutting',   'sequence' => 4, 'capacity' => 15000],
                ],
            ],
            [
                'name' => 'Digital Fast Track (Rush)',
                'description' => 'Quick digital print & staple: Digital -> Folding -> Staple -> Packaging',
                'stages' => [
                    ['process_name' => 'Digital',   'sequence' => 1, 'capacity' => 12000],
                    ['process_name' => 'Folding',   'sequence' => 2, 'capacity' => 10000],
                    ['process_name' => 'Staple',    'sequence' => 3, 'capacity' => 6000],
                    ['process_name' => 'Packaging', 'sequence' => 4, 'capacity' => 10000],
                ],
            ],
        ];

        foreach ($templates as $t) {
            $tpl = ProductionTemplate::updateOrCreate(
                ['name' => $t['name']],
                ['description' => $t['description'], 'is_active' => true]
            );

            ProductionTemplateProcess::where('template_id', $tpl->id)->delete();
            foreach ($t['stages'] as $s) {
                ProductionTemplateProcess::create([
                    'template_id'  => $tpl->id,
                    'process_name' => $s['process_name'],
                    'sequence'     => $s['sequence'],
                    'capacity'     => $s['capacity'],
                ]);
            }
        }

        // 3. Ensure existing machines have default capacities and process mappings
        $machines = Machine::all();
        foreach ($machines as $m) {
            $processName = match($m->type) {
                'offset'  => 'Press',
                'digital' => 'Digital',
                'folding' => 'Folding',
                'binding' => 'Binding',
                'cutting' => 'Cutting',
                default   => 'Other',
            };

            $dailyCap = match($m->type) {
                'offset'  => 8000,
                'digital' => 12000,
                'folding' => 10000,
                'binding' => 6000,
                'cutting' => 15000,
                default   => 8000,
            };

            if (!$m->process_name) {
                $m->process_name = $processName;
            }
            if (!$m->daily_capacity) {
                $m->daily_capacity = $dailyCap;
            }
            if (!$m->working_hours) {
                $m->working_hours = 8;
            }
            if (!$m->working_days) {
                $m->working_days = [1, 2, 3, 4, 5, 6]; // Mon - Sat
            }
            $m->saveQuietly();
        }
    }
}
