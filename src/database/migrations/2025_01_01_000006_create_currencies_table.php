<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // IDR, USD
            $table->string('name');
            $table->string('symbol', 10)->nullable();
            $table->decimal('exchange_rate_to_base', 18, 6)->default(1);
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_currencies');
    }
};
