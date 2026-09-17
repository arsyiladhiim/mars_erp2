<?php

namespace App\Models\Finance;

use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * G/L Account Determination (SAP B1-style): maps a fixed set of transaction
 * "keys" to the company's actual Chart of Accounts, so the posting engine
 * never hardcodes account codes (PRD §11.2 examples; architecture decision
 * to make this admin-configurable rather than hardcoded).
 *
 * Rows are seeded once per company (see ensureSeeded()) with a null account —
 * this is a fixed-row settings table, not a free-form resource: no create/
 * delete in the UI, only editing which account each key points to.
 */
class GlAccountMapping extends Model
{
    protected $table = 'finance_gl_account_mappings';

    protected $fillable = ['company_id', 'key', 'label', 'chart_of_account_id'];

    /**
     * The complete set of keys the posting engine understands. Keep this in
     * sync with every ->post...() method across the Finance/Inventory/Asset
     * services.
     *
     * @return array<string, string>
     */
    public static function keys(): array
    {
        return [
            'inventory' => 'Inventory (Asset)',
            'gr_ir_clearing' => 'GR/IR Clearing (Liability)',
            'accounts_payable' => 'Accounts Payable (Control)',
            'accounts_receivable' => 'Accounts Receivable (Control)',
            'sales_revenue' => 'Sales Revenue',
            'cogs' => 'Cost of Goods Sold',
            'tax_output' => 'VAT Payable (Output Tax)',
            'tax_input' => 'VAT Receivable (Input Tax)',
            'inventory_adjustment' => 'Inventory Adjustment Gain/Loss',
            'accumulated_depreciation' => 'Accumulated Depreciation (Contra-Asset)',
            'depreciation_expense' => 'Depreciation Expense',
            'fixed_assets' => 'Fixed Assets (at Cost)',
            'asset_disposal_gain_loss' => 'Gain/Loss on Asset Disposal',
        ];
    }

    /**
     * Idempotently create the fixed rows for a company (called from the
     * seeder and safe to re-run — e.g. after adding a new key to keys()).
     */
    public static function ensureSeeded(int $companyId): void
    {
        foreach (static::keys() as $key => $label) {
            static::firstOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                ['label' => $label],
            );
        }
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Resolve the mapped account for a posting key. Throws a clear,
     * actionable exception rather than silently posting to the wrong
     * account or a null account_id when an admin hasn't configured it yet.
     */
    public static function resolve(string $key, int $companyId): ChartOfAccount
    {
        $mapping = static::query()
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->first();

        if (! $mapping?->chart_of_account_id) {
            $label = static::keys()[$key] ?? $key;

            throw new RuntimeException(
                "G/L Account Determination for \"{$label}\" ({$key}) is not configured. ".
                'Set it in Administration → G/L Account Determination before posting this transaction.'
            );
        }

        return $mapping->chartOfAccount;
    }
}
