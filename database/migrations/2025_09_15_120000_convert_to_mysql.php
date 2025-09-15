<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert PostgreSQL-specific columns to MySQL-compatible ones
     */
    public function up()
    {
        // Convert JSON columns to TEXT for MySQL compatibility
        if (Schema::hasTable('emails')) {
            Schema::table('emails', function (Blueprint $table) {
                // Convert JSON columns to TEXT
                if (Schema::hasColumn('emails', 'cc_emails')) {
                    $table->text('cc_emails')->nullable()->change();
                }
                if (Schema::hasColumn('emails', 'bcc_emails')) {
                    $table->text('bcc_emails')->nullable()->change();
                }
                if (Schema::hasColumn('emails', 'attachments')) {
                    $table->text('attachments')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                if (Schema::hasColumn('documents', 'ai_metadata')) {
                    $table->text('ai_metadata')->nullable()->change();
                }
                if (Schema::hasColumn('documents', 'collaborators')) {
                    $table->text('collaborators')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('meetings')) {
            Schema::table('meetings', function (Blueprint $table) {
                if (Schema::hasColumn('meetings', 'participants')) {
                    $table->text('participants')->nullable()->change();
                }
            });
        }
    }

    public function down()
    {
        // Revert changes if needed
    }
};