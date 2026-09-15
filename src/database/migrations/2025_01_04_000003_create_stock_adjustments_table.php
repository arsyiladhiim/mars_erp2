<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('warehouse_id')->constrained('master_warehouses');
            $table->date('adjustment_date');
            $table->enum('reason', ['damage', 'loss', 'found', 'correction', 'other'])->default('correction');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_stock_adjustment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('inventory_stock_adjustments')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('master_items');
            $table->decimal('quantity', 18, 4); // signed: +in / -out
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_adjustment_lines');
        Schema::dropIfExists('inventory_stock_adjustments');
    }
};
