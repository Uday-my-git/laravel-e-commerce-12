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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();

            // Relations (optional)
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Gateway
            $table->string('payment_gateway'); // stripe / authorize_net / etc.

            // Event info
            $table->string('event_id')->nullable(); // evt_xxx / webhook id
            $table->string('event_type');

            // Resource
            $table->string('resource_id')->nullable(); // payment_intent / transId

            // Processing
            $table->boolean('processed')->default(false);

            // Full payload
            $table->longText('payload');

            // Indexes
            $table->index('event_id');
            $table->index('event_type');
            $table->index('payment_gateway');

            // Prevent duplicate webhook
            $table->unique(['event_id', 'payment_gateway']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
