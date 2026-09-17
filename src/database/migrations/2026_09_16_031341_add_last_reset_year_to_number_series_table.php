<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_number_series', function (Blueprint $table) {
            $table->unsignedSmallInteger('last_reset_year')->nullable()->after('reset_yearly');
        });
    }

    public function down(): void
    {
        Schema::table('core_number_series', function (Blueprint $table) {
            $table->dropColumn('last_reset_year');
        });
    }
};
