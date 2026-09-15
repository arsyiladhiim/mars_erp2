<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Immutable movement ledger — the single source of truth for stock quantity (PRD Key Rule #10).
        Schema::create('inventory_stock_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('master_items');
            $table->foreignId('warehouse_id')->constrained('master_warehouses');
            $table->enum('movement_type', [
                'opening', 'purchase_receipt', 'sales_delivery', 'goods_issue', 'goods_receipt',
                'transfer_in', 'transfer_out', 'adjustment', 'return_in', 'return_out', 'stock_opname',
            ]);
            $table->morphs('source'); // source_type, source_id -> originating document line
            $table->decimal('quantity_in', 18, 4)->default(0);
            $table->decimal('quantity_out', 18, 4)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('balance_quantity', 18, 4)->default(0); // running balance snapshot
            $table->decimal('balance_value', 18, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->date('movement_date');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['item_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_ledger');
    }
};
