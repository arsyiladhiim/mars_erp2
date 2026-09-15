<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('core_companies')->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->nullable()->constrained('core_accounting_periods')->nullOnDelete();
            $table->string('number')->unique();
            $table->date('entry_date');
            $table->enum('source_type', [
                'manual', 'goods_receipt', 'supplier_invoice', 'customer_invoice', 'delivery',
                'incoming_payment', 'outgoing_payment', 'stock_adjustment', 'depreciation', 'opening_balance',
            ])->default('manual');
            $table->nullableMorphs('reference'); // originating document, if system-generated
            $table->text('memo')->nullable();
            $table->decimal('total_debit', 18, 2)->default(0);
            $table->decimal('total_credit', 18, 2)->default(0);
            $table->enum('status', ['draft', 'posted', 'reversed'])->default('draft');
            $table->foreignId('reversed_journal_entry_id')->nullable()->constrained('finance_journal_entries')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('finance_journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('finance_journal_entries')->cascadeOnDelete();
            $table->foreignId('chart_of_account_id')->constrained('finance_chart_of_accounts');
            $table->foreignId('cost_center_id')->nullable()->constrained('core_cost_centers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_journal_entry_lines');
        Schema::dropIfExists('finance_journal_entries');
    }
};
