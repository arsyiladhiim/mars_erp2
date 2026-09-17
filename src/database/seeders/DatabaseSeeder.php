<?php

namespace Database\Seeders;

use App\Models\Asset\AssetCategory;
use App\Models\Asset\FixedAsset;
use App\Models\Asset\ItAsset;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\Branch;
use App\Models\Core\Company;
use App\Models\Core\CostCenter;
use App\Models\Core\Currency;
use App\Models\Core\Department;
use App\Models\Core\FiscalYear;
use App\Models\Core\NumberSeries;
use App\Models\Finance\BankAccount;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\SupplierInvoice;
use App\Models\Finance\TaxCode;
use App\Models\Helpdesk\Ticket;
use App\Models\Inventory\GoodsReceipt;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Item;
use App\Models\Master\ItemCategory;
use App\Models\Master\Uom;
use App\Models\Master\Warehouse;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Productivity\Memo;
use App\Models\Productivity\Todo;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\Delivery;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesQuotation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Foundation ────────────────────────────────────────────────
        $company = Company::create([
            'code' => 'MARS', 'name' => 'PT Mars Teknologi Indonesia',
            'legal_name' => 'PT Mars Teknologi Indonesia', 'tax_id' => '01.234.567.8-901.000',
            'email' => 'info@marserp.test', 'phone' => '+62 21 5550 1234',
            'address' => 'Jl. Jendral Sudirman Kav. 1, Jakarta Selatan', 'base_currency' => 'IDR',
        ]);

        $branch = Branch::create([
            'company_id' => $company->id, 'code' => 'HO', 'name' => 'Head Office',
            'address' => 'Jakarta Selatan', 'phone' => '+62 21 5550 1234',
        ]);

        $deptNames = ['Finance' => 'FIN', 'Purchasing' => 'PUR', 'Warehouse' => 'WH', 'Sales' => 'SLS', 'IT' => 'IT', 'HR' => 'HR'];
        $departments = [];
        foreach ($deptNames as $name => $code) {
            $departments[$code] = Department::create([
                'company_id' => $company->id, 'branch_id' => $branch->id, 'code' => $code, 'name' => $name,
            ]);
        }

        foreach ($departments as $code => $dept) {
            CostCenter::create([
                'company_id' => $company->id, 'department_id' => $dept->id,
                'code' => "CC-$code", 'name' => "{$dept->name} Cost Center",
            ]);
        }

        Currency::create(['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'exchange_rate_to_base' => 1, 'is_base' => true]);
        Currency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_base' => 15800]);

        $fy = FiscalYear::create([
            'company_id' => $company->id, 'code' => 'FY2026',
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open',
        ]);

        foreach (range(1, 12) as $m) {
            AccountingPeriod::create([
                'fiscal_year_id' => $fy->id,
                'name' => \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F Y'),
                'period_number' => $m,
                'start_date' => \Carbon\Carbon::create(2026, $m, 1)->startOfMonth(),
                'end_date' => \Carbon\Carbon::create(2026, $m, 1)->endOfMonth(),
                'status' => $m === 1 ? 'open' : 'open',
            ]);
        }

        foreach ([
            'purchase_request' => 'PR', 'rfq' => 'RFQ', 'purchase_order' => 'PO', 'goods_receipt' => 'GR',
            'supplier_invoice' => 'SINV', 'sales_quotation' => 'QUO', 'sales_order' => 'SO', 'delivery' => 'DO',
            'customer_invoice' => 'INV', 'incoming_payment' => 'RCP', 'outgoing_payment' => 'PAY',
            'stock_transfer' => 'STO', 'stock_adjustment' => 'ADJ', 'stock_opname' => 'OPN',
            'journal_entry' => 'JE', 'fixed_asset' => 'AST', 'ticket' => 'TKT',
        ] as $type => $prefix) {
            NumberSeries::create([
                'company_id' => $company->id, 'document_type' => $type, 'prefix' => $prefix,
                'format' => '{PREFIX}-{YEAR}-{NUMBER}', 'next_number' => 2, 'padding' => 6,
            ]);
        }

        // ── Security: roles & admin user ─────────────────────────────
        // 'super_admin' is Filament Shield's built-in bypass-all role (config/filament-shield.php).
        // The other roles are placeholders ready for per-resource permission assignment via the
        // Shield "Roles" admin page in a later (backend-hardening) pass.
        foreach (['super_admin', 'Finance', 'Purchasing', 'Warehouse', 'Sales', 'Director'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $admin = User::create([
            'employee_code' => 'EMP-0001', 'company_id' => $company->id, 'branch_id' => $branch->id,
            'department_id' => $departments['IT']->id, 'name' => 'System Administrator',
            'email' => 'admin@marserp.test', 'password' => bcrypt('password'), 'job_title' => 'System Administrator',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        $admin->assignRole('super_admin');

        // ── Master Data ───────────────────────────────────────────────
        $uoms = [];
        foreach (['PCS' => 'Pieces', 'BOX' => 'Box', 'KG' => 'Kilogram', 'LTR' => 'Liter', 'UNIT' => 'Unit'] as $code => $name) {
            $uoms[$code] = Uom::create(['code' => $code, 'name' => $name]);
        }

        $categories = [];
        foreach (['Electronics', 'Office Supplies', 'Raw Material', 'Services'] as $i => $name) {
            $categories[$name] = ItemCategory::create(['code' => 'CAT-'.($i + 1), 'name' => $name]);
        }

        $mainWh = Warehouse::create(['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => 'WH-MAIN', 'name' => 'Main Warehouse', 'address' => 'Jakarta Selatan']);
        Warehouse::create(['company_id' => $company->id, 'branch_id' => $branch->id, 'code' => 'WH-BR1', 'name' => 'Branch Warehouse']);

        $ppn11 = TaxCode::create(['code' => 'PPN11', 'name' => 'PPN 11%', 'rate' => 11, 'type' => 'both']);
        TaxCode::create(['code' => 'PPN0', 'name' => 'PPN 0% (Non-taxable)', 'rate' => 0, 'type' => 'both']);

        $coa = [
            ['1101', 'Cash', 'asset', false, null],
            ['1102', 'Bank - BCA', 'asset', true, 'bank'],
            ['1201', 'Accounts Receivable', 'asset', true, 'ar'],
            ['1301', 'Inventory', 'asset', true, 'inventory'],
            ['1401', 'GR/IR Clearing', 'asset', false, null],
            ['2101', 'Accounts Payable', 'liability', true, 'ap'],
            ['2201', 'VAT Payable (Output)', 'liability', false, null],
            ['2202', 'VAT Receivable (Input)', 'asset', false, null],
            ['3101', 'Common Stock', 'equity', false, null],
            ['3201', 'Retained Earnings', 'equity', false, null],
            ['4101', 'Sales Revenue', 'revenue', false, null],
            ['5101', 'Cost of Goods Sold', 'cogs', false, null],
            ['6101', 'Operating Expense', 'expense', false, null],
            ['6102', 'Depreciation Expense', 'expense', false, null],
            ['6103', 'Inventory Adjustment Gain/Loss', 'expense', false, null],
            ['1501', 'Accumulated Depreciation', 'asset', false, null],
            ['1502', 'Fixed Assets at Cost', 'asset', false, null],
            ['6104', 'Gain/Loss on Asset Disposal', 'expense', false, null],
        ];
        $accounts = [];
        foreach ($coa as [$code, $name, $type, $isControl, $controlType]) {
            $accounts[$code] = ChartOfAccount::create([
                'company_id' => $company->id, 'code' => $code, 'name' => $name, 'account_type' => $type,
                'is_control_account' => $isControl, 'control_type' => $controlType,
                'is_tax_account' => str_starts_with($code, '22'),
            ]);
        }

        GlAccountMapping::ensureSeeded($company->id);
        GlAccountMapping::where('company_id', $company->id)->update(['chart_of_account_id' => null]);
        $glMap = [
            'inventory' => '1301', 'gr_ir_clearing' => '1401', 'accounts_payable' => '2101',
            'accounts_receivable' => '1201', 'sales_revenue' => '4101', 'cogs' => '5101',
            'tax_output' => '2201', 'tax_input' => '2202', 'inventory_adjustment' => '6103',
            'accumulated_depreciation' => '1501', 'depreciation_expense' => '6102',
            'fixed_assets' => '1502', 'asset_disposal_gain_loss' => '6104',
        ];
        foreach ($glMap as $key => $code) {
            GlAccountMapping::where('company_id', $company->id)->where('key', $key)
                ->update(['chart_of_account_id' => $accounts[$code]->id]);
        }

        BankAccount::create([
            'company_id' => $company->id, 'chart_of_account_id' => $accounts['1102']->id,
            'account_name' => 'PT Mars Teknologi Indonesia', 'account_number' => '1234567890',
            'bank_name' => 'Bank Central Asia', 'branch' => 'Sudirman', 'opening_balance' => 500000000,
        ]);

        $items = [];
        $itemDefs = [
            ['ITM-0001', 'Laptop Business 14"', 'Electronics', 'PCS', 8500000, 12500000],
            ['ITM-0002', 'Monitor LED 24"', 'Electronics', 'PCS', 1500000, 2200000],
            ['ITM-0003', 'Office Chair Ergonomic', 'Office Supplies', 'PCS', 750000, 1100000],
            ['ITM-0004', 'A4 Paper 80gsm', 'Office Supplies', 'BOX', 45000, 65000],
            ['ITM-0005', 'Steel Sheet 2mm', 'Raw Material', 'KG', 18000, 0],
            ['ITM-0006', 'IT Support Service (Monthly)', 'Services', 'UNIT', 0, 3500000],
        ];
        foreach ($itemDefs as [$sku, $name, $cat, $uom, $cost, $price]) {
            $items[$sku] = Item::create([
                'sku' => $sku, 'name' => $name, 'item_category_id' => $categories[$cat]->id,
                'uom_id' => $uoms[$uom]->id, 'type' => $cat === 'Services' ? 'service' : 'inventory',
                'average_cost' => $cost, 'standard_cost' => $cost, 'selling_price' => $price,
                'minimum_stock' => 5, 'reorder_point' => 10, 'tax_code_id' => $ppn11->id,
            ]);
        }

        $customers = [];
        foreach ([
            ['CUST-001', 'PT Nusantara Digital', 'both'],
            ['CUST-002', 'CV Sinar Abadi', 'customer'],
            ['CUST-003', 'PT Cahaya Prima', 'customer'],
        ] as [$code, $name, $type]) {
            $customers[$code] = BusinessPartner::create([
                'code' => $code, 'name' => $name, 'type' => $type, 'currency' => 'IDR',
                'payment_term_days' => 30, 'credit_limit' => 200000000, 'default_tax_code_id' => $ppn11->id,
            ]);
        }

        $suppliers = [];
        foreach ([
            ['SUPP-001', 'PT Sumber Elektronik', 'supplier'],
            ['SUPP-002', 'CV Mitra Perkakas', 'supplier'],
            ['SUPP-003', 'PT Baja Sejahtera', 'supplier'],
        ] as [$code, $name, $type]) {
            $suppliers[$code] = BusinessPartner::create([
                'code' => $code, 'name' => $name, 'type' => $type, 'currency' => 'IDR',
                'payment_term_days' => 30, 'default_tax_code_id' => $ppn11->id,
            ]);
        }

        // ── Sample transactions (Procurement) ────────────────────────
        $pr = PurchaseRequest::create([
            'company_id' => $company->id, 'branch_id' => $branch->id, 'number' => 'PR-2026-000001',
            'requester_id' => $admin->id, 'department_id' => $departments['IT']->id,
            'required_date' => now()->addDays(14), 'reason' => 'Replenish laptop stock for new hires',
            'status' => 'approved',
        ]);
        $pr->lines()->create(['item_id' => $items['ITM-0001']->id, 'quantity' => 5, 'uom_id' => $uoms['PCS']->id, 'estimated_price' => 8500000]);

        $po = PurchaseOrder::create([
            'company_id' => $company->id, 'branch_id' => $branch->id, 'number' => 'PO-2026-000001',
            'business_partner_id' => $suppliers['SUPP-001']->id, 'warehouse_id' => $mainWh->id,
            'order_date' => now()->subDays(5), 'delivery_date' => now()->addDays(7), 'payment_term_days' => 30,
            'subtotal' => 42500000, 'tax_total' => 4675000, 'grand_total' => 47175000, 'status' => 'approved',
        ]);
        $po->lines()->create([
            'item_id' => $items['ITM-0001']->id, 'quantity' => 5, 'uom_id' => $uoms['PCS']->id,
            'unit_price' => 8500000, 'tax_code_id' => $ppn11->id, 'line_total' => 42500000,
        ]);

        $gr = GoodsReceipt::create([
            'company_id' => $company->id, 'purchase_order_id' => $po->id,
            'business_partner_id' => $suppliers['SUPP-001']->id, 'warehouse_id' => $mainWh->id,
            'number' => 'GR-2026-000001', 'receipt_date' => now()->subDays(2),
            'supplier_reference' => 'SJ-99881', 'status' => 'posted',
        ]);
        $gr->lines()->create([
            'item_id' => $items['ITM-0001']->id, 'quantity' => 5, 'uom_id' => $uoms['PCS']->id, 'unit_cost' => 8500000,
        ]);

        SupplierInvoice::create([
            'company_id' => $company->id, 'number' => 'SINV-2026-000001', 'supplier_invoice_number' => 'INV/SE/0456',
            'business_partner_id' => $suppliers['SUPP-001']->id, 'purchase_order_id' => $po->id, 'goods_receipt_id' => $gr->id,
            'invoice_date' => now()->subDays(2), 'due_date' => now()->addDays(28),
            'subtotal' => 42500000, 'tax_total' => 4675000, 'grand_total' => 47175000, 'status' => 'posted',
        ]);

        // ── Sample transactions (Sales) ───────────────────────────────
        $quo = SalesQuotation::create([
            'company_id' => $company->id, 'branch_id' => $branch->id, 'number' => 'QUO-2026-000001',
            'business_partner_id' => $customers['CUST-001']->id, 'quotation_date' => now()->subDays(10),
            'validity_date' => now()->addDays(20), 'subtotal' => 24400000, 'tax_total' => 2684000,
            'grand_total' => 27084000, 'status' => 'accepted',
        ]);
        $quo->lines()->create([
            'item_id' => $items['ITM-0002']->id, 'quantity' => 10, 'uom_id' => $uoms['PCS']->id,
            'unit_price' => 2200000, 'tax_code_id' => $ppn11->id, 'line_total' => 22000000,
        ]);

        $so = SalesOrder::create([
            'company_id' => $company->id, 'branch_id' => $branch->id, 'number' => 'SO-2026-000001',
            'business_partner_id' => $customers['CUST-001']->id, 'sales_quotation_id' => $quo->id,
            'warehouse_id' => $mainWh->id, 'order_date' => now()->subDays(8), 'delivery_date' => now()->addDays(3),
            'payment_term_days' => 30, 'subtotal' => 22000000, 'tax_total' => 2420000,
            'grand_total' => 24420000, 'status' => 'approved',
        ]);
        $so->lines()->create([
            'item_id' => $items['ITM-0002']->id, 'quantity' => 10, 'uom_id' => $uoms['PCS']->id,
            'unit_price' => 2200000, 'tax_code_id' => $ppn11->id, 'line_total' => 22000000,
        ]);

        $delivery = Delivery::create([
            'company_id' => $company->id, 'number' => 'DO-2026-000001', 'sales_order_id' => $so->id,
            'business_partner_id' => $customers['CUST-001']->id, 'warehouse_id' => $mainWh->id,
            'delivery_date' => now()->subDays(1), 'status' => 'posted',
        ]);
        $delivery->lines()->create(['item_id' => $items['ITM-0002']->id, 'quantity' => 10]);

        CustomerInvoice::create([
            'company_id' => $company->id, 'number' => 'INV-2026-000001', 'business_partner_id' => $customers['CUST-001']->id,
            'sales_order_id' => $so->id, 'delivery_id' => $delivery->id, 'invoice_date' => now()->subDays(1),
            'due_date' => now()->addDays(29), 'subtotal' => 22000000, 'tax_total' => 2420000,
            'grand_total' => 24420000, 'status' => 'posted',
        ]);

        // ── Asset ───────────────────────────────────────────────────
        $laptopCategory = AssetCategory::create(['code' => 'AC-LAPTOP', 'name' => 'Laptop & Computer', 'useful_life_months' => 36]);
        $asset = FixedAsset::create([
            'company_id' => $company->id, 'code' => 'AST-2026-000001', 'name' => 'Laptop Business 14" - EMP0001',
            'asset_category_id' => $laptopCategory->id, 'purchase_date' => now()->subMonths(2),
            'acquisition_cost' => 12500000, 'useful_life_months' => 36, 'accumulated_depreciation' => 694444,
            'location_warehouse_id' => $mainWh->id, 'assigned_user_id' => $admin->id,
            'department_id' => $departments['IT']->id, 'status' => 'in_use',
        ]);
        ItAsset::create([
            'fixed_asset_id' => $asset->id, 'asset_tag' => 'IT-0001', 'device_type' => 'laptop',
            'brand' => 'Dell', 'model' => 'Latitude 5440', 'serial_number' => 'SN-DL5440-001',
            'os' => 'Windows 11 Pro', 'assigned_user_id' => $admin->id, 'department_id' => $departments['IT']->id,
            'status' => 'assigned',
        ]);

        // ── Productivity / Helpdesk ────────────────────────────────────
        Todo::create([
            'title' => 'Review Q1 purchase requests', 'assignee_id' => $admin->id, 'created_by' => $admin->id,
            'department_id' => $departments['PUR']->id, 'due_date' => now()->addDays(3), 'priority' => 'high',
            'status' => 'open',
        ]);
        Memo::create([
            'title' => 'Company Holiday Notice', 'content' => 'Office will be closed on national holidays per government calendar.',
            'visibility' => 'shared', 'author_id' => $admin->id,
        ]);
        Ticket::create([
            'number' => 'TKT-2026-000001', 'subject' => 'Laptop not booting', 'description' => 'Employee laptop fails to boot after Windows update.',
            'requester_id' => $admin->id, 'department_id' => $departments['IT']->id, 'category' => 'Hardware',
            'priority' => 'high', 'sla_hours' => 8, 'status' => 'open',
        ]);

        // ── Number Series: the sample documents above were seeded with
        // hand-picked "-000001" numbers rather than via NumberSeries::next(),
        // so pre-register each series past that used number — otherwise the
        // first real auto-generated document collides with the seed data.
        // Always branch_id=null: GeneratesDocumentNumber uses one company-wide
        // counter per document type (see its docblock for why per-branch
        // counters are unsafe against a globally-unique `number` column).
        foreach ([
            ['purchase_request', 'PR'],
            ['purchase_order', 'PO'],
            ['goods_receipt', 'GR'],
            ['supplier_invoice', 'SINV'],
            ['sales_quotation', 'QUO'],
            ['sales_order', 'SO'],
            ['delivery', 'DO'],
            ['customer_invoice', 'INV'],
        ] as [$documentType, $prefix]) {
            NumberSeries::updateOrCreate(
                ['company_id' => $company->id, 'branch_id' => null, 'document_type' => $documentType],
                [
                    'prefix' => $prefix, 'format' => '{PREFIX}-{YEAR}-{NUMBER}', 'next_number' => 2,
                    'padding' => 6, 'reset_yearly' => true, 'last_reset_year' => now()->year, 'is_active' => true,
                ]
            );
        }

        // ── RBAC: assign domain permissions to the non-super-admin roles ──
        $this->call(RolePermissionSeeder::class);
    }
}
