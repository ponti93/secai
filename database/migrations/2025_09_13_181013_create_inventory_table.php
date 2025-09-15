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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('sku')->unique();
            $table->integer('quantity');
            $table->integer('min_quantity')->default(10);
            $table->decimal('unit_price', 10, 2);
            $table->string('supplier')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->boolean('needs_reorder')->default(false);
            $table->timestamp('last_restocked')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'needs_reorder']);
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
