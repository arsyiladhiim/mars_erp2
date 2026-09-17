<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Assigns Shield-generated permissions ("{Action}:{Model}") to the
 * non-super-admin roles created in DatabaseSeeder, grouped by the ERP
 * navigation domain each role owns (PRD §5 Target Users). `super_admin`
 * bypasses all checks via config/filament-shield.php and needs nothing here.
 *
 * Safe to re-run: uses syncPermissions per role, so it can be replayed after
 * adding new resources/permissions without duplicating anything.
 */
class RolePermissionSeeder extends Seeder
{
    protected const FULL = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'];

    protected const READ_UPDATE = ['ViewAny', 'View', 'Update'];

    protected const READ = ['ViewAny', 'View'];

    public function run(): void
    {
        $this->assign('Finance', [
            [self::FULL, [
                'TaxCode', 'ChartOfAccount', 'SupplierInvoice', 'BankAccount', 'JournalEntry',
                'CashBankTransaction', 'OutgoingPayment', 'IncomingPayment', 'FiscalYear',
            ]],
            [self::READ_UPDATE, ['GlAccountMapping', 'AccountingPeriod']],
            [self::READ, ['CustomerInvoice', 'PurchaseOrder', 'SalesOrder', 'BusinessPartner', 'Item', 'Warehouse']],
        ]);

        $this->assign('Purchasing', [
            [self::FULL, ['PurchaseRequest', 'Rfq', 'SupplierQuotation', 'PurchaseOrder']],
            [self::READ, [
                'BusinessPartner', 'Item', 'ItemCategory', 'Uom', 'Warehouse', 'GoodsReceipt', 'SupplierInvoice',
            ]],
        ]);

        $this->assign('Warehouse', [
            [self::FULL, ['GoodsReceipt', 'StockTransfer', 'StockAdjustment', 'StockOpname', 'BatchSerial']],
            [self::READ, [
                'StockLedgerEntry', 'Item', 'ItemCategory', 'Uom', 'Warehouse', 'PurchaseOrder', 'Delivery',
            ]],
        ]);

        $this->assign('Sales', [
            [self::FULL, ['SalesQuotation', 'SalesOrder', 'Delivery', 'CustomerInvoice', 'IncomingPayment']],
            [self::READ, ['BusinessPartner', 'Item', 'Warehouse', 'StockLedgerEntry']],
        ]);

        // Director: executive oversight — read-only, everywhere (PRD §5.1).
        $this->assign('Director', [
            [self::READ, [
                'Company', 'Branch', 'Department', 'CostCenter', 'ProfitCenter', 'Currency', 'FiscalYear',
                'AccountingPeriod', 'NumberSeries', 'SystemPreference', 'WorkflowRule', 'AuditLog', 'GlAccountMapping',
                'BusinessPartner', 'Item', 'ItemCategory', 'Uom', 'Warehouse',
                'PurchaseRequest', 'Rfq', 'SupplierQuotation', 'PurchaseOrder',
                'GoodsReceipt', 'StockLedgerEntry', 'StockTransfer', 'StockAdjustment', 'StockOpname', 'BatchSerial',
                'SalesQuotation', 'SalesOrder', 'Delivery', 'CustomerInvoice',
                'TaxCode', 'ChartOfAccount', 'SupplierInvoice', 'BankAccount', 'JournalEntry',
                'CashBankTransaction', 'OutgoingPayment', 'IncomingPayment',
                'AssetCategory', 'FixedAsset', 'ItAsset', 'MaintenanceRequest',
                'Todo', 'Memo', 'Document', 'ShareLink', 'Ticket', 'KbArticle',
            ]],
        ]);
    }

    /**
     * @param  array<int, array{0: array<int, string>, 1: array<int, string>}>  $groups
     *                                                                          List of [actions, modelNames] pairs.
     */
    protected function assign(string $roleName, array $groups): void
    {
        $role = Role::findOrCreate($roleName, 'web');

        $permissionNames = [];

        foreach ($groups as [$actions, $models]) {
            foreach ($models as $model) {
                foreach ($actions as $action) {
                    $permissionNames[] = "{$action}:{$model}";
                }
            }
        }

        // Only assign permissions that actually exist (keeps this seeder safe
        // to run even before shield:generate has produced every entry).
        $existing = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        $role->syncPermissions($existing);

        $missing = array_diff($permissionNames, $existing);

        if ($missing) {
            $this->command?->warn(
                "[{$roleName}] skipped ".count($missing)." permission(s) not found (run shield:generate?): "
                .implode(', ', array_slice($missing, 0, 5)).(count($missing) > 5 ? '…' : '')
            );
        }
    }
}
