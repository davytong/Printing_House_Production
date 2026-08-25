<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ProductionBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintingBatchTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /**
     * Test starting a new batch with cloned targets.
     */
    public function test_can_start_new_batch_cloning_targets(): void
    {
        ProductionBatch::clearCache();
        ProductionBatch::where('status', 'active')->update(['status' => 'completed']);
        $oldBatch = ProductionBatch::create([
            'name' => 'Old Active Batch',
            'status' => 'active',
            'started_at' => now()->subDays(5),
        ]);

        // 2. Create a book inside old batch
        $book = Book::create([
            'batch_id' => $oldBatch->id,
            'title' => 'Test Book Grade 1',
            'category' => 'perfect_binding',
            'printing_method' => 'offset',
            'grade' => 'Grade 1',
            'target_qty' => 5000,
            'total_printed' => 2000,
        ]);

        // 3. Trigger starting new batch
        $response = $this->withSession([
            'user_name' => 'Admin User',
            'user_position' => 'admin',
            'user_role' => 'admin',
        ])->post('/printing/new-batch', [
            'name' => 'New Batch 2026',
            'reset_mode' => 'keep_targets',
        ]);

        $response->assertStatus(302);

        // Assert old batch is completed
        $oldBatch->refresh();
        $this->assertEquals('completed', $oldBatch->status);
        $this->assertNotNull($oldBatch->completed_at);

        // Assert new active batch is created
        $newBatch = ProductionBatch::where('status', 'active')->latest('id')->first();
        $this->assertNotNull($newBatch);
        $this->assertEquals('New Batch 2026', $newBatch->name);

        // Assert book is cloned with target intact but printed quantity reset to 0
        $clonedBook = Book::where('batch_id', $newBatch->id)->first();
        $this->assertNotNull($clonedBook);
        $this->assertEquals('Test Book Grade 1', $clonedBook->title);
        $this->assertEquals(5000, $clonedBook->target_qty);
        $this->assertEquals(0, $clonedBook->total_printed);
    }

    /**
     * Test starting a fresh empty batch.
     */
    public function test_can_start_fresh_empty_batch(): void
    {
        $oldBatch = ProductionBatch::create([
            'name' => 'Old Batch',
            'status' => 'active',
            'started_at' => now()->subDays(5),
        ]);

        Book::create([
            'batch_id' => $oldBatch->id,
            'title' => 'Old Book',
            'category' => 'staple',
            'printing_method' => 'digital',
            'target_qty' => 1000,
            'total_printed' => 1000,
        ]);

        $response = $this->withSession([
            'user_name' => 'Admin User',
            'user_position' => 'admin',
            'user_role' => 'admin',
        ])->post('/printing/new-batch', [
            'name' => 'Fresh Batch',
            'reset_mode' => 'fresh',
        ]);

        $response->assertStatus(302);

        $newBatch = ProductionBatch::where('status', 'active')->latest('id')->first();
        $this->assertNotNull($newBatch);
        $this->assertEquals('Fresh Batch', $newBatch->name);

        // Assert no books exist in the new fresh batch
        $booksCount = Book::where('batch_id', $newBatch->id)->count();
        $this->assertEquals(0, $booksCount);
    }
}
