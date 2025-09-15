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
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('tax_amount', 10, 2)->default(0)->after('amount');
            $table->string('merchant')->nullable()->after('vendor');
            $table->string('payment_method')->nullable()->after('merchant');
            $table->string('receipt_number')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'merchant', 'payment_method', 'receipt_number']);
        });
    }
};