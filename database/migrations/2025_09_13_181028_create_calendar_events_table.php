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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->string('location')->nullable();
            $table->string('google_event_id')->nullable();
            $table->json('attendees')->nullable();
            $table->enum('status', ['confirmed', 'tentative', 'cancelled'])->default('confirmed');
            $table->boolean('all_day')->default(false);
            $table->string('recurrence_rule')->nullable();
            $table->json('reminders')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'start_time']);
            $table->index(['user_id', 'status']);
            $table->index('google_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
