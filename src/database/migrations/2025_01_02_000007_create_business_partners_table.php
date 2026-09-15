<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_business_partners', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['customer', 'supplier', 'both'])->default('customer');
            $table->string('tax_id')->nullable(); // NPWP
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->unsignedInteger('payment_term_days')->nullable();
            $table->decimal('credit_limit', 18, 2)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->foreignId('default_tax_code_id')->nullable()->constrained('finance_tax_codes')->nullOnDelete();
            $table->string('contact_person')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_business_partners');
    }
};
