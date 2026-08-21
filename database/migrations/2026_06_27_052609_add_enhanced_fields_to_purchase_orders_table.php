<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add new enhanced fields
            $table->string('priority')->default('medium')->after('status'); // low, medium, high, urgent
            $table->text('reason')->nullable()->after('priority'); // Why this PO is needed
            $table->string('requested_by')->nullable()->after('reason'); // Who created this PO
            $table->integer('approved_by')->nullable()->after('requested_by'); // Manager who approved
            $table->timestamp('approved_at')->nullable()->after('approved_by'); // When approved
            $table->string('payment_method')->default('cash')->after('currency'); // cash, bank, credit
            $table->string('payment_status')->default('pending')->after('payment_method'); // pending, partial, paid
            $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_status'); // Amount paid so far
            $table->timestamp('paid_at')->nullable()->after('paid_amount'); // When payment completed
            $table->text('invoice_path')->nullable()->after('paid_at'); // Path to invoice file
            
            // Update status column to support new statuses
            // Current: draft, sent, partial, received, cancelled
            // Add: pending_approval, approved, in_transit, completed
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'priority',
                'reason',
                'requested_by',
                'approved_by',
                'approved_at',
                'payment_method',
                'payment_status',
                'paid_amount',
                'paid_at',
                'invoice_path',
            ]);
        });
    }
};
