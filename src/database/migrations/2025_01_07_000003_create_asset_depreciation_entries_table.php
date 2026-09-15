<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained('asset_fixed_assets')->cascadeOnDelete();
            $table->date('period_date');
            $table->decimal('depreciation_amount', 18, 2)->default(0);
            $table->decimal('accumulated_after', 18, 2)->default(0);
            $table->decimal('book_value_after', 18, 2)->default(0);
            $table->enum('status', ['draft', 'posted'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciation_entries');
    }
};
