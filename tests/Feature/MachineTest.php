<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    }

    /**
     * Test user can create a machine.
     */
    public function test_can_create_machine(): void
    {
        $response = $this->withSession([
            'user_name' => 'Technician User',
            'user_position' => 'press_operator',
            'user_role' => 'operator',
        ])->post('/machines', [
            'name' => 'Speedmaster XL 106',
            'model' => 'XL 106',
            'manufacturer' => 'Heidelberg',
            'serial_number' => 'HD123456',
            'type' => 'offset',
            'status' => 'operational',
            'purchased_date' => now()->subYear()->toDateString(),
            'maintenance_interval_days' => 30,
            'notes' => 'Primary offset printing press',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('machines', [
            'name' => 'Speedmaster XL 106',
            'serial_number' => 'HD123456',
            'status' => 'operational',
        ]);

        $machine = Machine::where('serial_number', 'HD123456')->first();
        $this->assertNotNull($machine);
        $this->assertStringStartsWith('MCH-', $machine->code);
    }

    /**
     * Test scheduling and completing maintenance updates machine status.
     */
    public function test_completing_maintenance_updates_machine_status(): void
    {
        $machine = Machine::create([
            'name' => 'Speedmaster XL 106',
            'model' => 'XL 106',
            'type' => 'offset',
            'status' => 'breakdown', // currently breakdown
            'maintenance_interval_days' => 30,
        ]);

        $schedule = MaintenanceSchedule::create([
            'machine_id' => $machine->id,
            'type' => 'breakdown',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
        ]);

        $response = $this->withSession([
            'user_name' => 'Technician User',
            'user_position' => 'press_operator',
            'user_role' => 'operator',
        ])->post("/machines/maintenance/{$schedule->id}/complete", [
            'completed_date' => now()->toDateString(),
            'downtime_hours' => 4,
            'findings' => 'Replaced main roller belt.',
            'parts_used' => 'Roller belt model B2',
            'cost' => 150.0,
        ]);

        $response->assertStatus(302);

        $schedule->refresh();
        $this->assertEquals('completed', $schedule->status);
        $this->assertEquals(4, $schedule->downtime_hours);
        $this->assertEquals(150.0, $schedule->cost);

        // Machine status should update to operational
        $machine->refresh();
        $this->assertEquals('operational', $machine->status);
        $this->assertEquals(now()->toDateString(), $machine->last_maintenance->toDateString());
        $this->assertEquals(now()->addDays(30)->toDateString(), $machine->next_maintenance->toDateString());
    }
}
