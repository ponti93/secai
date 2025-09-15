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
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('audio_file_name')->nullable();
            $table->bigInteger('audio_file_size')->nullable();
            $table->string('audio_mime_type')->nullable();
            $table->enum('transcription_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn([
                'audio_file_name', 
                'audio_file_size',
                'audio_mime_type',
                'transcription_status'
            ]);
        });
    }
};
