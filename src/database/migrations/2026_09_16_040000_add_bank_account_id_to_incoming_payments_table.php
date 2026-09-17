<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_incoming_payments', function (Blueprint $table) {
            $table->foreignId('bank_account_id')->nullable()->after('customer_invoice_id')
                ->constrained('finance_bank_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_incoming_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bank_account_id');
        });
    }
};
