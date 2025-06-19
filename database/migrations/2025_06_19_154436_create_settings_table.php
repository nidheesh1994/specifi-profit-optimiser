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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('labor_hours', 8, 2)->default(1.0);
            $table->decimal('labor_cost_per_hour', 8, 2)->default(10.0);
            $table->decimal('fixed_overheads', 8, 2)->default(0.0);
            $table->decimal('target_profit_margin', 5, 2)->default(20.0);
            $table->string('llm_provider')->default('openai');
            $table->string('api_key')->nullable();
            $table->string('model_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
