<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Header')
                ->columns(3)
                ->components([
                    TextEntry::make('number'),
                    TextEntry::make('businessPartner.name')->label('Supplier'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('order_date')->date(),
                    TextEntry::make('delivery_date')->date(),
                    TextEntry::make('warehouse.name'),
                ]),
            Section::make('Lines')
                ->components([
                    RepeatableEntry::make('lines')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('item.name')->label('Item'),
                            TextEntry::make('quantity')->numeric(),
                            TextEntry::make('uom.code')->label('UOM'),
                            TextEntry::make('unit_price')->money('IDR'),
                            TextEntry::make('line_total')->money('IDR'),
                        ]),
                ]),
            Section::make('Totals')
                ->columns(3)
                ->components([
                    TextEntry::make('subtotal')->money('IDR'),
                    TextEntry::make('tax_total')->money('IDR'),
                    TextEntry::make('grand_total')->money('IDR')->weight(FontWeight::Bold),
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
