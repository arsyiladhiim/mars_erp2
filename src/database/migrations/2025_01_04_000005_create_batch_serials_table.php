<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_batch_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('master_items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('master_warehouses');
            $table->enum('type', ['batch', 'serial']);
            $table->string('batch_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['available', 'reserved', 'sold', 'expired', 'quarantine'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_batch_serials');
    }
};
