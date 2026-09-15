<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_it_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->nullable()->constrained('asset_fixed_assets')->nullOnDelete();
            $table->string('asset_tag')->unique();
            $table->enum('device_type', [
                'laptop', 'desktop', 'monitor', 'printer', 'server', 'network_device',
                'mobile_device', 'license', 'sim', 'peripheral',
            ]);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('imei')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('os')->nullable();
            $table->json('software')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->nullOnDelete();
            $table->string('location')->nullable();
            $table->enum('status', ['in_stock', 'assigned', 'under_repair', 'retired'])->default('in_stock');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_it_assets');
    }
};
