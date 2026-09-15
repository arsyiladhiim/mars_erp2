<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('business_partner_id')->constrained('master_business_partners');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained('sales_deliveries')->nullOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_total', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->decimal('paid_amount', 18, 2)->default(0);
            $table->enum('status', [
                'draft', 'posted', 'partially_paid', 'paid', 'overdue', 'cancelled',
            ])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_customer_invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->constrained('sales_customer_invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('master_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->foreignId('tax_code_id')->nullable()->constrained('finance_tax_codes')->nullOnDelete();
            $table->decimal('line_total', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_customer_invoice_lines');
        Schema::dropIfExists('sales_customer_invoices');
    }
};
