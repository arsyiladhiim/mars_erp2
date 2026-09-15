<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_number_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('core_branches')->nullOnDelete();
            $table->string('document_type'); // PR, PO, GR, SO, INV, PAY, AST ...
            $table->string('prefix'); // PR
            $table->string('format')->default('{PREFIX}-{YEAR}-{NUMBER}');
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(6);
            $table->boolean('reset_yearly')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_number_series');
    }
};
