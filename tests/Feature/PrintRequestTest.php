<?php

namespace Tests\Feature;

use App\Models\PrintRequest;
use App\Models\PrintRequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintRequestTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     * Test user can create a print request.
     */
    public function test_can_create_print_request(): void
    {
        $response = $this->withSession([
            'user_name' => 'Department Head',
            'user_position' => 'academic_dept',
            'user_role' => 'requester',
        ])->post('/requests', [
            'title' => 'English Book Grade 1 Print',
            'requester_name' => 'John Smith',
            'department' => 'Academic Dept',
            'priority' => 'high',
            'required_by' => now()->addDays(10)->toDateString(),
            'notes' => 'Needs to be printed for next term.',
            'books' => [
                [
                    'book_title' => 'English Grade 1',
                    'grade' => 'Grade 1',
                    'category' => 'perfect_binding',
                    'quantity_requested' => 1200,
                    'notes' => 'Urgent prints',
                ]
            ]
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('print_requests', [
            'title' => 'English Book Grade 1 Print',
            'requester_name' => 'John Smith',
            'status' => 'pending',
        ]);

        $request = PrintRequest::where('title', 'English Book Grade 1 Print')->first();
        $this->assertNotNull($request);
        $this->assertEquals(1, $request->items()->count());
        $this->assertEquals(1200, $request->totalQty());
    }

    /**
     * Test admin can approve print request.
     */
    public function test_admin_can_approve_print_request(): void
    {
        $request = PrintRequest::create([
            'title' => 'Grade 2 Math',
            'requester_name' => 'Teacher',
            'priority' => 'normal',
            'status' => 'pending',
            'quantity_requested' => 500,
        ]);

        $response = $this->withSession([
            'user_name' => 'Admin User',
            'user_position' => 'admin',
            'user_role' => 'admin',
        ])->post("/requests/{$request->id}/approve", [
            'approved_by' => 'Admin User',
        ]);

        $response->assertStatus(302);

        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertEquals('Admin User', $request->approved_by);
        $this->assertNotNull($request->approved_at);
    }
}
