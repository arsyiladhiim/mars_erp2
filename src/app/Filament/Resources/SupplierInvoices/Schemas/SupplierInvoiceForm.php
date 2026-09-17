<?php

namespace App\Filament\Resources\SupplierInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()
                        ->required()->label('Supplier'),
                    TextInput::make('number')->maxLength(50)
                        ->disabled(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (string $operation) => $operation !== 'create')
                        ->required(fn (string $operation) => $operation === 'edit')
                        ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
                    TextInput::make('supplier_invoice_number')->label('Supplier\'s Invoice No.')->maxLength(100),
                    Select::make('purchase_order_id')->relationship('purchaseOrder', 'number')->searchable(),
                    Select::make('goods_receipt_id')->relationship('goodsReceipt', 'number')->searchable(),
                    DatePicker::make('invoice_date')->required(),
                    DatePicker::make('due_date'),
                    TextInput::make('currency')->default('IDR')->maxLength(3),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'pending_approval' => 'Pending Approval', 'approved' => 'Approved',
                        'posted' => 'Posted', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])->default('draft')->required()->disabled()->dehydrated()
                        ->helperText('Managed via the Post action.'),
                ]),
            Section::make('Lines')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(5)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('description'),
                            TextInput::make('quantity')->numeric()->default(1)->required(),
                            TextInput::make('unit_price')->numeric()->prefix('Rp')->required(),
                            Select::make('tax_code_id')->relationship('taxCode', 'name')->searchable(),
                            TextInput::make('line_total')->numeric()->prefix('Rp'),
                        ])
                        ->addActionLabel('Add Line'),
                ]),
            Section::make('Totals')
                ->columns(4)
                ->components([
                    TextInput::make('subtotal')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('tax_total')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('grand_total')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('paid_amount')->numeric()->prefix('Rp')->default(0)->disabled(),
                ]),
        ]);
    }
}
