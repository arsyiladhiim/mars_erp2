<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->foreignId('purchase_request_id')->nullable()->constrained('procurement_purchase_requests')->nullOnDelete();
            $table->date('required_date')->nullable();
            $table->text('terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'responded', 'closed', 'cancelled'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procurement_rfq_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('procurement_rfqs')->cascadeOnDelete();
            $table->foreignId('business_partner_id')->constrained('master_business_partners')->cascadeOnDelete();
            $table->enum('response_status', ['pending', 'responded', 'declined'])->default('pending');
            $table->timestamps();
        });

        Schema::create('procurement_rfq_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('procurement_rfqs')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('master_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4);
            $table->foreignId('uom_id')->nullable()->constrained('master_uoms')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_rfq_lines');
        Schema::dropIfExists('procurement_rfq_suppliers');
        Schema::dropIfExists('procurement_rfqs');
    }
};
