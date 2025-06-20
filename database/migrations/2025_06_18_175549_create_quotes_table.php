<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('products');           // List of product line items

            // Quote-level cost parameters
            $table->decimal('labor_hours', 8, 2);
            $table->decimal('labor_cost_per_hour', 10, 2);
            $table->decimal('fixed_overheads', 10, 2);
            $table->decimal('target_profit_margin', 5, 2);
            $table->decimal('calculated_margin', 5, 2)->nullable();
            $table->decimal('total_profit', 10, 2)->nullable();
            $table->string('health_status')->nullable(); // green, amber, red

            // AI Suggestions
            $table->text('ai_suggestions')->nullable();

            // AI Chat Support
            $table->longText('chat_log')->nullable(); // Chat history in JSON
            $table->string('ai_model_used')->nullable(); // e.g., "gpt-4"
            $table->text('last_ai_feedback')->nullable(); // Last summary from AI
            $table->timestamp('chat_started_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
