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
        Schema::table('emails', function (Blueprint $table) {
            $table->string('ai_category')->nullable();
            $table->string('ai_priority')->nullable();
            $table->json('ai_sentiment')->nullable();
            $table->json('ai_key_info')->nullable();
            $table->string('ai_suggested_action')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn([
                'ai_category',
                'ai_priority',
                'ai_sentiment',
                'ai_key_info',
                'ai_suggested_action'
            ]);
        });
    }
};
