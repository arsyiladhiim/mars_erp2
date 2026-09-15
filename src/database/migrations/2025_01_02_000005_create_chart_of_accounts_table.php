<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('account_type', [
                'asset', 'liability', 'equity', 'revenue', 'expense', 'cogs',
            ]);
            $table->foreignId('parent_id')->nullable()->constrained('finance_chart_of_accounts')->nullOnDelete();
            $table->boolean('is_control_account')->default(false);
            $table->enum('control_type', ['ar', 'ap', 'inventory', 'bank', 'cash'])->nullable();
            $table->boolean('is_tax_account')->default(false);
            $table->boolean('requires_cost_center')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_chart_of_accounts');
    }
};
