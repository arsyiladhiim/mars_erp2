<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberSeries extends Model
{
    protected $table = 'core_number_series';

    protected $fillable = [
        'company_id', 'branch_id', 'document_type', 'prefix', 'format',
        'next_number', 'padding', 'reset_yearly', 'last_reset_year', 'is_active',
    ];

    protected $casts = [
        'reset_yearly' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Find-or-create the active series for a company/document type and return
     * the next formatted document number. This is the entry point document
     * models should call (via App\Models\Concerns\GeneratesDocumentNumber)
     * rather than instantiating a series manually.
     */
    public static function next(int $companyId, string $documentType, ?int $branchId = null): string
    {
        $series = static::firstOrCreate(
            ['company_id' => $companyId, 'branch_id' => $branchId, 'document_type' => $documentType],
            [
                'prefix' => static::defaultPrefixFor($documentType),
                'format' => '{PREFIX}-{YEAR}-{NUMBER}',
                'next_number' => 1,
                'padding' => 6,
                'reset_yearly' => true,
                'is_active' => true,
            ]
        );

        return $series->generateNumber();
    }

    protected static function defaultPrefixFor(string $documentType): string
    {
        $map = [
            'purchase_request' => 'PR', 'rfq' => 'RFQ', 'supplier_quotation' => 'SQ',
            'purchase_order' => 'PO', 'goods_receipt' => 'GR', 'supplier_invoice' => 'SINV',
            'sales_quotation' => 'QUO', 'sales_order' => 'SO', 'delivery' => 'DO',
            'customer_invoice' => 'INV', 'incoming_payment' => 'RCP', 'outgoing_payment' => 'PAY',
            'stock_transfer' => 'STO', 'stock_adjustment' => 'ADJ', 'stock_opname' => 'OPN',
            'journal_entry' => 'JE', 'fixed_asset' => 'AST', 'ticket' => 'TKT',
        ];

        return $map[$documentType] ?? strtoupper(substr($documentType, 0, 4));
    }

    /**
     * Atomically reserve and format the next document number, resetting the
     * counter on a new fiscal/calendar year when reset_yearly is enabled.
     */
    public function generateNumber(): string
    {
        return DB::transaction(function () {
            $series = static::where('id', $this->id)->lockForUpdate()->first();

            $currentYear = (int) now()->year;

            if ($series->reset_yearly && $series->last_reset_year !== $currentYear) {
                $series->next_number = 1;
                $series->last_reset_year = $currentYear;
            }

            $number = str_pad((string) $series->next_number, $series->padding, '0', STR_PAD_LEFT);
            $series->next_number++;
            $series->save();

            return str_replace(
                ['{PREFIX}', '{YEAR}', '{NUMBER}'],
                [$series->prefix, $currentYear, $number],
                $series->format
            );
        });
    }
}
