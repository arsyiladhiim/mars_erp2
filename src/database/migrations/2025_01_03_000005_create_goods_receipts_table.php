<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained('procurement_purchase_orders')->nullOnDelete();
            $table->foreignId('business_partner_id')->nullable()->constrained('master_business_partners')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('master_warehouses');
            $table->date('receipt_date');
            $table->string('supplier_reference')->nullable(); // DO/surat jalan number
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_goods_receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('inventory_goods_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_order_line_id')->nullable()->constrained('procurement_purchase_order_lines')->nullOnDelete();
            $table->foreignId('item_id')->constrained('master_items');
            $table->decimal('quantity', 18, 4);
            $table->foreignId('uom_id')->nullable()->constrained('master_uoms')->nullOnDelete();
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_goods_receipt_lines');
        Schema::dropIfExists('inventory_goods_receipts');
    }
};
