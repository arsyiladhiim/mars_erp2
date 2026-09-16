<?php

namespace App\Filament\Resources\SupplierInvoices\Pages;

use App\Filament\Resources\SupplierInvoices\SupplierInvoiceResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewSupplierInvoice extends ViewRecord
{
    protected static string $resource = SupplierInvoiceResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Header')
                ->columns(3)
                ->components([
                    TextEntry::make('number'),
                    TextEntry::make('supplier_invoice_number')->label('Supplier Invoice No.'),
                    TextEntry::make('businessPartner.name')->label('Supplier'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('purchaseOrder.number')->label('PO #'),
                    TextEntry::make('goodsReceipt.number')->label('GR #'),
                    TextEntry::make('invoice_date')->date(),
                    TextEntry::make('due_date')->date(),
                ]),
            Section::make('Lines')
                ->components([
                    RepeatableEntry::make('lines')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('item.name')->label('Item'),
                            TextEntry::make('description'),
                            TextEntry::make('quantity')->numeric(),
                            TextEntry::make('line_total')->money('IDR'),
                        ]),
                ]),
            Section::make('Totals')
                ->columns(4)
                ->components([
                    TextEntry::make('subtotal')->money('IDR'),
                    TextEntry::make('tax_total')->money('IDR'),
                    TextEntry::make('grand_total')->money('IDR')->weight(FontWeight::Bold),
                    TextEntry::make('paid_amount')->money('IDR'),
                ]),
            Section::make('Attachments')
                ->components([
                    RepeatableEntry::make('documents')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('title'),
                            TextEntry::make('uploadedBy.name')->label('Uploaded By'),
                        ])
                        ->placeholder('Belum ada dokumen terlampir.'),
                ])
                ->collapsed(),
            Section::make('Activity')
                ->components([
                    RepeatableEntry::make('auditLogs')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('action')->badge(),
                            TextEntry::make('user.name')->label('By'),
                            TextEntry::make('created_at')->dateTime(),
                        ])
                        ->placeholder('Belum ada aktivitas tercatat.'),
                ])
                ->collapsed(),
        ]);
    }
}
