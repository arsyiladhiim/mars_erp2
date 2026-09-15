<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('item_category_id')->nullable()->constrained('master_item_categories')->nullOnDelete();
            $table->string('brand')->nullable();
            $table->foreignId('uom_id')->constrained('master_uoms');
            $table->string('barcode')->nullable();
            $table->enum('type', ['inventory', 'service', 'non_inventory'])->default('inventory');
            $table->enum('costing_method', ['moving_average', 'standard'])->default('moving_average');
            $table->decimal('standard_cost', 18, 2)->default(0);
            $table->decimal('average_cost', 18, 2)->default(0);
            $table->decimal('selling_price', 18, 2)->default(0);
            $table->decimal('minimum_stock', 18, 4)->default(0);
            $table->decimal('maximum_stock', 18, 4)->nullable();
            $table->decimal('reorder_point', 18, 4)->default(0);
            $table->boolean('is_batch_tracked')->default(false);
            $table->boolean('is_serial_tracked')->default(false);
            $table->boolean('has_expiry')->default(false);
            $table->foreignId('tax_code_id')->nullable()->constrained('finance_tax_codes')->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_items');
    }
};
