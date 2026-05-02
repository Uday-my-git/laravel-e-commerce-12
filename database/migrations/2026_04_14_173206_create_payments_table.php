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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // ✅ MUST exist BEFORE index
            $table->string('payment_gateway');

            $table->string('transaction_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->string('reference_id')->nullable();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');

            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'refunded',
                'partial_refund'
            ])->default('pending');

            $table->longText('payload')->nullable();

            // ✅ indexes AFTER column
            $table->index('order_id');
            $table->index('payment_gateway');
            $table->index('transaction_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
