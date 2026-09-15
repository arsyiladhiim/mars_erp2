<?php

namespace App\Filament\Resources\PurchaseRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Request Header')
                ->columns(3)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    Select::make('branch_id')->relationship('branch', 'name')->searchable(),
                    TextInput::make('number')->required()->maxLength(50),
                    Select::make('requester_id')->relationship('requester', 'name')->searchable()->required(),
                    Select::make('department_id')->relationship('department', 'name')->searchable(),
                    Select::make('cost_center_id')->relationship('costCenter', 'name')->searchable(),
                    DatePicker::make('required_date'),
                    Select::make('status')->options([
                        'draft' => 'Draft', 'submitted' => 'Submitted', 'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved', 'rejected' => 'Rejected', 'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ])->default('draft')->required(),
                    Textarea::make('reason')->rows(2)->columnSpanFull(),
                ]),
            Section::make('Requested Items')
                ->components([
                    Repeater::make('lines')
                        ->relationship()
                        ->columns(4)
                        ->schema([
                            Select::make('item_id')->relationship('item', 'name')->searchable()->columnSpan(2),
                            TextInput::make('description'),
                            TextInput::make('quantity')->numeric()->required(),
                            Select::make('uom_id')->relationship('uom', 'name')->searchable(),
                            TextInput::make('estimated_price')->numeric()->prefix('Rp'),
                        ])
                        ->addActionLabel('Add Item'),
                ]),
        ]);
    }
}
