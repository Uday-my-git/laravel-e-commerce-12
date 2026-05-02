<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Gateway
            $table->string('payment_gateway');

            // IDs
            $table->string('refund_transaction_id')->nullable(); // refund_id
            $table->string('payment_reference')->nullable();     // payment_intent / transId

            // Money
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'succeeded',
                'failed'
            ])->default('pending');

            // Reason
            $table->text('reason')->nullable();

            // Raw response
            $table->longText('payload')->nullable();

            // Indexes
            $table->index('payment_id');
            $table->index('refund_transaction_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
