<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('warehouse_id')->constrained('master_warehouses');
            $table->date('count_date');
            $table->enum('status', ['draft', 'counting', 'pending_approval', 'approved', 'posted', 'cancelled'])
                ->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_stock_opname_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('inventory_stock_opnames')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('master_items');
            $table->decimal('system_quantity', 18, 4)->default(0);
            $table->decimal('physical_quantity', 18, 4)->default(0);
            $table->decimal('variance_quantity', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_opname_lines');
        Schema::dropIfExists('inventory_stock_opnames');
    }
};
