<?php

namespace App\Models\Finance;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Sales\CustomerInvoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomingPayment extends Model
{
    use SoftDeletes;

    protected $table = 'finance_incoming_payments';

    protected $fillable = [
        'company_id', 'number', 'business_partner_id', 'customer_invoice_id',
        'payment_date', 'method', 'reference_number', 'amount', 'status',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class);
    }

    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class);
    }
}
