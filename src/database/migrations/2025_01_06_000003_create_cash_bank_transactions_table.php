<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_cash_bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('finance_bank_accounts');
            $table->string('number')->unique();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out'])->default('deposit');
            $table->date('transaction_date');
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_reconciled')->default(false);
            $table->date('reconciled_date')->nullable();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_cash_bank_transactions');
    }
};
