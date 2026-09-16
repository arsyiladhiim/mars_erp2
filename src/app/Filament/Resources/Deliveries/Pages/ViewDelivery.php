<?php

namespace App\Filament\Resources\Deliveries\Pages;

use App\Filament\Resources\Deliveries\DeliveryResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewDelivery extends ViewRecord
{
    protected static string $resource = DeliveryResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Header')
                ->columns(3)
                ->components([
                    TextEntry::make('number'),
                    TextEntry::make('salesOrder.number')->label('SO #'),
                    TextEntry::make('businessPartner.name')->label('Customer'),
                    TextEntry::make('status')->badge(),
                    TextEntry::make('warehouse.name'),
                    TextEntry::make('delivery_date')->date(),
                    TextEntry::make('tracking_number'),
                ]),
            Section::make('Lines')
                ->components([
                    RepeatableEntry::make('lines')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('item.name')->label('Item'),
                            TextEntry::make('quantity')->numeric(),
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
