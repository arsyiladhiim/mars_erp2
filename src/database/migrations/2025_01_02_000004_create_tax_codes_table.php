<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_tax_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PPN11, PPN0
            $table->string('name');
            $table->decimal('rate', 6, 3)->default(0); // percentage
            $table->enum('type', ['output', 'input', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_tax_codes');
    }
};
