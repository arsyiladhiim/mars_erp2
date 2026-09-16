<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewGoodsReceipt extends ViewRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Header')
                ->columns(3)
                ->components([
                    TextEntry::make('number'),
                    TextEntry::make('businessPartner.name')->label('Supplier'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('purchaseOrder.number')->label('PO #'),
                    TextEntry::make('warehouse.name'),
                    TextEntry::make('receipt_date')->date(),
                    TextEntry::make('supplier_reference')->label('DO / Surat Jalan'),
                ]),
            Section::make('Lines')
                ->components([
                    RepeatableEntry::make('lines')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('item.name')->label('Item'),
                            TextEntry::make('quantity')->numeric(),
                            TextEntry::make('uom.code')->label('UOM'),
                            TextEntry::make('unit_cost')->money('IDR'),
                            TextEntry::make('batch_number'),
                        ]),
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
