<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_supplier_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('rfq_id')->nullable()->constrained('procurement_rfqs')->nullOnDelete();
            $table->foreignId('business_partner_id')->constrained('master_business_partners');
            $table->date('validity_date')->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->unsignedInteger('payment_term_days')->nullable();
            $table->enum('status', ['draft', 'received', 'selected', 'rejected'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procurement_supplier_quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_quotation_id')->constrained('procurement_supplier_quotations')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('master_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4);
            $table->foreignId('uom_id')->nullable()->constrained('master_uoms')->nullOnDelete();
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('discount_percent', 6, 3)->default(0);
            $table->foreignId('tax_code_id')->nullable()->constrained('finance_tax_codes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_supplier_quotation_lines');
        Schema::dropIfExists('procurement_supplier_quotations');
    }
};
