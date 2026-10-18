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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('category')->default('Produk Fizikal');
            $table->decimal('price', 14, 2)->default(0.00);
            $table->decimal('cost_price', 14, 2)->default(0.00);
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->text('usp')->nullable(); // Unique Selling Proposition for AI Ad copy
            $table->string('target_audience')->nullable(); // Target demographic
            $table->string('status')->default('active'); // active, draft, archived
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
