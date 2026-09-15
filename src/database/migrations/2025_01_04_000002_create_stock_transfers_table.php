<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('master_warehouses');
            $table->foreignId('to_warehouse_id')->constrained('master_warehouses');
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'in_transit', 'completed', 'cancelled'])
                ->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_stock_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('inventory_stock_transfers')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('master_items');
            $table->decimal('quantity', 18, 4);
            $table->string('batch_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_transfer_lines');
        Schema::dropIfExists('inventory_stock_transfers');
    }
};
