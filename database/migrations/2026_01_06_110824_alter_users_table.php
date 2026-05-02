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
        Schema::table('users', function ($table) {
            $table->string('google_id')->after('status')->nullable();
            $table->string('facebook_id')->after('google_id')->nullable();
            $table->string('github_id')->after('facebook_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('google_id');
            $table->dropColumn('facebook_id');
            $table->dropColumn('github_id');
        });
    }

    
};
