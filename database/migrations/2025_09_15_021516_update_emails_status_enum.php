<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include 'sending' and 'failed'
        DB::statement("ALTER TABLE emails DROP CONSTRAINT IF EXISTS emails_status_check");
        DB::statement("ALTER TABLE emails ALTER COLUMN status TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE emails ADD CONSTRAINT emails_status_check CHECK (status IN ('draft', 'sent', 'received', 'archived', 'sending', 'failed'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE emails DROP CONSTRAINT IF EXISTS emails_status_check");
        DB::statement("ALTER TABLE emails ALTER COLUMN status TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE emails ADD CONSTRAINT emails_status_check CHECK (status IN ('draft', 'sent', 'received', 'archived'))");
    }
};
