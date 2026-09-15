<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('core_branches')->nullOnDelete();
            $table->string('number')->unique();
            $table->foreignId('business_partner_id')->constrained('master_business_partners');
            $table->foreignId('sales_quotation_id')->nullable()->constrained('sales_quotations')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('master_warehouses')->nullOnDelete();
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->unsignedInteger('payment_term_days')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_total', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->enum('status', [
                'draft', 'submitted', 'pending_approval', 'approved', 'partially_delivered',
                'delivered', 'closed', 'cancelled', 'rejected',
            ])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('master_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4);
            $table->foreignId('uom_id')->nullable()->constrained('master_uoms')->nullOnDelete();
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount_percent', 6, 3)->default(0);
            $table->foreignId('tax_code_id')->nullable()->constrained('finance_tax_codes')->nullOnDelete();
            $table->decimal('line_total', 18, 2)->default(0);
            $table->decimal('delivered_quantity', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
        Schema::dropIfExists('sales_orders');
    }
};
