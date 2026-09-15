<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('core_fiscal_years')->cascadeOnDelete();
            $table->string('name'); // "January 2026"
            $table->unsignedTinyInteger('period_number'); // 1-12
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['open', 'processing', 'review', 'closed', 'locked'])->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_accounting_periods');
    }
};
