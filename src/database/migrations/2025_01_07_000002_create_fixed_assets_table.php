<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('asset_category_id')->constrained('asset_categories');
            $table->date('purchase_date');
            $table->decimal('acquisition_cost', 18, 2)->default(0);
            $table->unsignedInteger('useful_life_months')->default(36);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance'])->default('straight_line');
            $table->decimal('residual_value', 18, 2)->default(0);
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->foreignId('location_warehouse_id')->nullable()->constrained('master_warehouses')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('status', [
                'draft', 'capitalized', 'in_use', 'under_maintenance', 'transferred', 'disposed',
            ])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_fixed_assets');
    }
};
