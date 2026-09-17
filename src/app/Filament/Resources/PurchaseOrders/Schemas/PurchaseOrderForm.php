<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Order Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('branch_id')->relationship('branch', 'name')->searchable(),
                    TextInput::make('number')->maxLength(50)
                        ->disabled(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (string $operation) => $operation !== 'create')
                        ->required(fn (string $operation) => $operation === 'edit')
                        ->helperText(fn (string $operation) => $operation === 'create' ? 'Auto-generated on save.' : null),
                    Select::make('business_partner_id')->relationship('businessPartner', 'name')->searchable()
                        ->required()->label('Supplier'),
                    Select::make('supplier_quotation_id')->relationship('supplierQuotation', 'number')
                        ->searchable(),
                    Select::make('warehouse_id')->relationship('warehouse', 'name')->searchable(),
                    DatePicker::make('order_date')->required(),
                    DatePicker::make('delivery_date'),
                    TextInput::make('payment_term_days')->numeric()->suffix('days'),
                    TextInput::make('currency')->default('IDR')->maxLength(3),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'submitted' => 'Submitted', 'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved', 'sent' => 'Sent', 'partially_received' => 'Partially Received',
                        'received' => 'Received', 'closed' => 'Closed', 'cancelled' => 'Cancelled',
                        'rejected' => 'Rejected',
                    ])->default('draft')->required()->disabled()->dehydrated()
                        ->helperText('Managed via Submit for Approval / the approvals inbox / Goods Receipt posting.'),
                ]),
            Section::make('Lines')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(6)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('quantity')->numeric()->required(),
                            Select::make('uom_id')->relationship('uom', 'name')->searchable(),
                            TextInput::make('unit_price')->numeric()->prefix('Rp')->required(),
                            TextInput::make('discount_percent')->numeric()->suffix('%'),
                            Select::make('tax_code_id')->relationship('taxCode', 'name')->searchable(),
                            TextInput::make('line_total')->numeric()->prefix('Rp'),
                        ])
                        ->addActionLabel('Add Line'),
                ]),
            Section::make('Totals')
                ->columns(3)
                ->components([
                    TextInput::make('subtotal')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('tax_total')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('grand_total')->numeric()->prefix('Rp')->default(0),
                ]),
        ]);
    }
}
