<?php

namespace App\Filament\Resources\Rfqs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RfqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('RFQ Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    TextInput::make('number')->required()->maxLength(50),
                    Select::make('purchase_request_id')->relationship('purchaseRequest', 'number')->searchable(),
                    DatePicker::make('required_date'),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'sent' => 'Sent', 'responded' => 'Responded',
                        'closed' => 'Closed', 'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
                    Textarea::make('terms')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Invited Suppliers')
                ->components([
                    Repeater::make('suppliers')
                        ->relationship()
                        ->columns(2)
                        ->schema([
                            Select::make('business_partner_id')->relationship('businessPartner', 'name')
                                ->searchable()->required(),
                            Select::make('response_status')->options([
                                'pending' => 'Pending', 'responded' => 'Responded', 'declined' => 'Declined',
                            ])->default('pending'),
                        ])
                        ->addActionLabel('Invite Supplier'),
                ]),
            Section::make('Items')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(4)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('description'),
                            TextInput::make('quantity')->numeric()->required(),
                            Select::make('uom_id')->relationship('uom', 'name')->searchable(),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
