<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('core_branches')->nullOnDelete();
            $table->string('number')->unique();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained('core_cost_centers')->nullOnDelete();
            $table->foreignId('project_id')->nullable();
            $table->date('required_date')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', [
                'draft', 'submitted', 'pending_approval', 'approved', 'rejected', 'closed', 'cancelled',
            ])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procurement_purchase_request_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('procurement_purchase_requests')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('master_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4);
            $table->foreignId('uom_id')->nullable()->constrained('master_uoms')->nullOnDelete();
            $table->decimal('estimated_price', 18, 2)->default(0);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_purchase_request_lines');
        Schema::dropIfExists('procurement_purchase_requests');
    }
};
