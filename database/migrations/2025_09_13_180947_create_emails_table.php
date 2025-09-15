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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('content');
            $table->string('from_email');
            $table->string('to_email');
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->enum('status', ['draft', 'sent', 'received', 'archived'])->default('draft');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_important')->default(false);
            $table->json('attachments')->nullable();
            $table->text('ai_summary')->nullable();
            $table->text('ai_reply')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_read']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
