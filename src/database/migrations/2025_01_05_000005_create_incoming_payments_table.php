<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_incoming_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('business_partner_id')->constrained('master_business_partners');
            $table->foreignId('customer_invoice_id')->nullable()->constrained('sales_customer_invoices')->nullOnDelete();
            $table->date('payment_date');
            $table->enum('method', ['cash', 'bank_transfer', 'giro', 'credit_card', 'other'])->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_incoming_payments');
    }
};
