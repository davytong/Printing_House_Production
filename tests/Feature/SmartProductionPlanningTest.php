<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Machine;
use App\Models\PrintRequest;
use App\Models\ProductionJob;
use App\Models\ProductionProcess;
use App\Models\ProductionSchedule;
use App\Models\ProductionTemplate;
use App\Models\ProductionTemplateProcess;
use App\Services\ProductionPlanningService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SmartProductionPlanningTest extends TestCase
{
    use DatabaseTransactions;

    protected ProductionPlanningService $planningService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planningService = app(ProductionPlanningService::class);

        // Seed basic processes
        $press = ProductionProcess::create([
            'name' => 'Press', 'code' => 'PRS', 'sequence' => 1, 'default_capacity' => 8000, 'is_active' => true
        ]);
        $folding = ProductionProcess::create([
            'name' => 'Folding', 'code' => 'FLD', 'sequence' => 2, 'default_capacity' => 10000, 'is_active' => true
        ]);
        $binding = ProductionProcess::create([
            'name' => 'Binding', 'code' => 'BND', 'sequence' => 3, 'default_capacity' => 6000, 'is_active' => true
        ]);

        // Seed basic template
        $template = ProductionTemplate::create([
            'name' => 'Textbook Template', 'is_active' => true
        ]);
        ProductionTemplateProcess::create([
            'template_id' => $template->id, 'process_name' => 'Press', 'sequence' => 1, 'capacity' => 8000
        ]);
        ProductionTemplateProcess::create([
            'template_id' => $template->id, 'process_name' => 'Folding', 'sequence' => 2, 'capacity' => 10000
        ]);
        ProductionTemplateProcess::create([
            'template_id' => $template->id, 'process_name' => 'Binding', 'sequence' => 3, 'capacity' => 6000
        ]);

        // Seed Machine
        Machine::create([
            'code' => 'MCH-001', 'name' => 'Offset Press A', 'type' => 'offset',
            'process_name' => 'Press', 'daily_capacity' => 8000, 'status' => 'operational'
        ]);
    }

    /**
     * Test 1: 5,000 copies with 8,000/day capacity produces 1 day.
     */
    public function test_single_day_capacity_calculation()
    {
        $split = $this->planningService->calculateDailySplit(5000, 8000);
        $this->assertCount(1, $split);
        $this->assertEquals([5000], $split);
    }

    /**
     * Test 2: 20,000 copies with 8,000/day capacity splits into 8000, 8000, 4000.
     */
    public function test_multi_day_capacity_splitting()
    {
        $split = $this->planningService->calculateDailySplit(20000, 8000);
        $this->assertCount(3, $split);
        $this->assertEquals([8000, 8000, 4000], $split);
        $this->assertEquals(20000, array_sum($split));
    }

    /**
     * Test 3: Sunday is non-working and skipped automatically.
     */
    public function test_sunday_is_skipped_automatically()
    {
        // Pick a Saturday: 2026-09-05 (Sat) -> next day should be 2026-09-07 (Mon)
        $saturday = Carbon::create(2026, 9, 5);
        $this->assertEquals(6, $saturday->dayOfWeek);

        $nextWorking = $this->planningService->addWorkingDays($saturday, 1, false);
        $this->assertEquals(1, $nextWorking->dayOfWeek); // Monday
        $this->assertEquals(7, $nextWorking->day);
    }

    /**
     * Test 4: Machine capacity overload is detected during conflict check.
     */
    public function test_machine_conflict_detection()
    {
        $machine = Machine::first();
        $date = Carbon::create(2026, 9, 2);

        // Schedule an existing 7,000 units on Press A
        ProductionSchedule::create([
            'year' => 2026, 'month' => 9, 'day' => 2,
            'process' => 'Press', 'machine_id' => $machine->id,
            'task' => 'Existing Book', 'planned_qty' => 7000, 'status' => 'planned'
        ]);

        // Attempting to add 3,000 more when capacity is 8,000 -> 7,000 + 3,000 = 10,000 > 8,000
        $conflict = $this->planningService->checkDateConflict($date, 'Press', $machine->id, 3000, 8000);
        $this->assertTrue($conflict['has_conflict']);
        $this->assertEquals('danger', $conflict['severity']);
    }

    /**
     * Test 5: Confirming a plan creates ProductionJob, ProductionSchedules, and ProductionTasks.
     */
    public function test_plan_confirmation_populates_database()
    {
        $template = ProductionTemplate::first();

        $planPreview = $this->planningService->generatePlan([
            'name' => 'Math Grade 11',
            'quantity' => 16000,
            'start_date' => '2026-09-02',
            'due_date' => '2026-09-15',
            'priority' => 'high',
            'template_id' => $template->id,
        ]);

        $this->assertNotEmpty($planPreview['stages']);

        $job = $this->planningService->confirmPlan($planPreview, 'Admin Tester');

        $this->assertDatabaseHas('production_jobs', [
            'id' => $job->id,
            'name' => 'Math Grade 11',
            'quantity' => 16000,
            'status' => 'confirmed'
        ]);

        $this->assertTrue(ProductionSchedule::where('production_job_id', $job->id)->exists());
    }

    /**
     * Test 6: Recording daily actual output updates variance and remaining quantity.
     */
    public function test_recording_actual_output_calculates_variance()
    {
        $schedule = ProductionSchedule::create([
            'year' => 2026, 'month' => 9, 'day' => 2,
            'schedule_date' => '2026-09-02',
            'process' => 'Press',
            'task' => 'Level 11',
            'planned_qty' => 8000,
            'actual_qty' => 0,
            'status' => 'planned'
        ]);

        $actualLog = $this->planningService->recordActual($schedule->id, 7200, 'Slight paper feed issue', 'Davy');

        $this->assertEquals(7200, $actualLog->actual_qty);
        $this->assertEquals(-800, $actualLog->variance);

        $schedule->refresh();
        $this->assertEquals(7200, $schedule->actual_qty);
        $this->assertEquals(800, $schedule->remainingQty());
        $this->assertEquals('in_progress', $schedule->status);
    }

    /**
     * Test 7: Locked schedule is protected from auto-rescheduling.
     */
    public function test_locked_schedule_is_preserved_during_rescheduling()
    {
        $job = ProductionJob::create([
            'name' => 'Fixed Textbook', 'quantity' => 5000, 'start_date' => '2026-09-02', 'status' => 'confirmed'
        ]);

        $lockedSchedule = ProductionSchedule::create([
            'production_job_id' => $job->id,
            'year' => 2026, 'month' => 9, 'day' => 5,
            'schedule_date' => '2026-09-05',
            'process' => 'Binding',
            'task' => 'Fixed Binding Stage',
            'planned_qty' => 5000,
            'is_locked' => true,
            'status' => 'locked'
        ]);

        $suggestion = $this->planningService->generateRescheduleSuggestion($lockedSchedule->id, 2);

        $lockedItem = collect($suggestion['shifts'])->firstWhere('id', $lockedSchedule->id);
        $this->assertTrue($lockedItem['is_locked']);
        $this->assertEquals($lockedSchedule->day, $lockedItem['new_day']);
    }

    /**
     * Test 8: Due date feasibility warning when production duration exceeds deadline.
     */
    public function test_due_date_feasibility_detection()
    {
        $template = ProductionTemplate::first();

        $plan = $this->planningService->generatePlan([
            'name' => 'Late Job',
            'quantity' => 50000,
            'start_date' => '2026-09-02',
            'due_date' => '2026-09-04',
            'template_id' => $template->id,
        ]);

        $this->assertFalse($plan['summary']['is_on_time']);
        $this->assertGreaterThan(0, $plan['summary']['overdue_days']);
        $this->assertNotEmpty($plan['summary']['warnings']);
    }

    /**
     * Test 9: Approved print request pre-populates wizard data.
     */
    public function test_approved_print_request_integration()
    {
        $req = PrintRequest::create([
            'title' => 'Grade 10 English',
            'requester_name' => 'Academic Dept',
            'priority' => 'high',
            'status' => 'approved',
            'quantity_requested' => 4000,
            'total_books_requested' => 1,
            'required_by' => '2026-09-12'
        ]);

        $response = $this->withSession(['user_name' => 'Admin'])->postJson(route('schedule.plan.preview'), [
            'name' => $req->title,
            'quantity' => $req->quantity_requested,
            'start_date' => '2026-09-02',
            'due_date' => $req->required_by->format('Y-m-d'),
            'priority' => $req->priority,
            'print_request_id' => $req->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('plan.job.print_request_id', $req->id);
    }

    /**
     * Test 10: Bulk text / Excel importer.
     */
    public function test_bulk_import_parses_entries()
    {
        $rawText = "Math Level 1, 5000, Press, 3\nMath Level 1, 5000, Folding, 4";

        $response = $this->post(route('schedule.bulk-import'), [
            'year' => 2026,
            'month' => 9,
            'import_data' => $rawText,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('production_schedules', [
            'year' => 2026,
            'month' => 9,
            'day' => 3,
            'process' => 'Press',
            'task' => 'Math Level 1',
            'planned_qty' => 5000
        ]);
        $this->assertDatabaseHas('production_schedules', [
            'year' => 2026,
            'month' => 9,
            'day' => 4,
            'process' => 'Folding',
            'task' => 'Math Level 1',
            'planned_qty' => 5000
        ]);
    }

    /**
     * Test 11: Machine maintenance status flags a conflict warning.
     */
    public function test_machine_maintenance_flags_conflict()
    {
        $machine = Machine::first();
        $machine->update(['status' => 'maintenance']);

        $conflict = $this->planningService->checkDateConflict(Carbon::create(2026, 9, 2), 'Press', $machine->id, 1000, 8000);
        $this->assertTrue($conflict['has_conflict']);
        $this->assertEquals('warning', $conflict['severity']);
    }

    /**
     * Test 12: Process sequence ordering follows template dependencies.
     */
    public function test_process_dependency_sequence()
    {
        $template = ProductionTemplate::first();

        $plan = $this->planningService->generatePlan([
            'name' => 'Sequence Test',
            'quantity' => 8000,
            'start_date' => '2026-09-02',
            'template_id' => $template->id,
        ]);

        $this->assertCount(3, $plan['stages']);
        $this->assertEquals('Press', $plan['stages'][0]['process_name']);
        $this->assertEquals('Folding', $plan['stages'][1]['process_name']);
        $this->assertEquals('Binding', $plan['stages'][2]['process_name']);

        // Folding starts after Press finishes
        $this->assertTrue(Carbon::parse($plan['stages'][1]['start_date'])->gte(Carbon::parse($plan['stages'][0]['end_date'])));
    }

    /**
     * Test 13: Backward compatibility of legacy schedules with null new columns.
     */
    public function test_legacy_schedule_backward_compatibility()
    {
        $legacy = ProductionSchedule::create([
            'year' => 2026,
            'month' => 9,
            'day' => 10,
            'process' => 'Press',
            'task' => 'Old Book Style',
            'note' => 'Legacy Note',
            'color' => '#3b82f6',
            'status' => 'planned'
        ]);

        $this->assertNull($legacy->production_job_id);
        $this->assertNull($legacy->machine_id);
        $this->assertNull($legacy->planned_qty);
        $this->assertEquals(0, $legacy->remainingQty());
        $this->assertFalse($legacy->isLocked());
    }
}
