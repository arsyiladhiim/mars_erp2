use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Procurement\PurchaseOrder;

$company = Company::first();
$supplier = BusinessPartner::suppliers()->first() ?? BusinessPartner::first();

$po = PurchaseOrder::create([
    'company_id' => $company->id,
    'business_partner_id' => $supplier->id,
    'order_date' => now(),
    'grand_total' => 1000000,
]);

echo "Created PO number: {$po->number}\n";
echo "Status before submit: {$po->status}\n";

$po->submitForApproval();
$po->refresh();

echo "Status after submit: {$po->status}\n";
echo "Approvals count: " . $po->approvals()->count() . "\n";

$po->delete();
echo "Cleaned up test PO.\n";
