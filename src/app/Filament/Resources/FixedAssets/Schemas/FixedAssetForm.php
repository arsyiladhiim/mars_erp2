<?php

namespace App\Filament\Resources\FixedAssets\Schemas;

use App\Models\Asset\FixedAsset;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FixedAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Asset Information')
                ->columns(2)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    TextInput::make('code')->required()->maxLength(30)->unique(ignoreRecord: true),
                    TextInput::make('name')->required()->maxLength(150),
                    Select::make('asset_category_id')->relationship('category', 'name')->searchable()->required(),
                    TextInput::make('serial_number')->maxLength(100),
                    DatePicker::make('warranty_expiry'),
                ]),
            Section::make('Acquisition & Depreciation')
                ->columns(3)
                ->components([
                    DatePicker::make('purchase_date')->required(),
                    TextInput::make('acquisition_cost')->numeric()->prefix('Rp')->required(),
                    TextInput::make('residual_value')->numeric()->prefix('Rp')->default(0),
                    Select::make('depreciation_method')->options([
                        'straight_line' => 'Straight Line', 'declining_balance' => 'Declining Balance',
                    ])->default('straight_line')->required(),
                    TextInput::make('useful_life_months')->numeric()->default(36)->required()->suffix('months'),
                    TextInput::make('accumulated_depreciation')->numeric()->prefix('Rp')->default(0)->disabled(),
                ]),
            Section::make('Assignment')
                ->columns(3)
                ->components([
                    Select::make('location_warehouse_id')->relationship('locationWarehouse', 'name')
                        ->searchable()->label('Location'),
                    Select::make('assigned_user_id')->relationship('assignedUser', 'name')->searchable(),
                    Select::make('department_id')->relationship('department', 'name')->searchable(),
                    Select::make('status')
                        ->options(function (?FixedAsset $record) {
                            $options = [
                                'draft' => 'Draft', 'capitalized' => 'Capitalized', 'in_use' => 'In Use',
                                'under_maintenance' => 'Under Maintenance', 'transferred' => 'Transferred',
                            ];

                            if ($record?->status === 'disposed') {
                                $options['disposed'] = 'Disposed';
                            }

                            return $options;
                        })
                        ->default('draft')->required()
                        ->disabled(fn (?FixedAsset $record) => $record?->status === 'disposed')
                        ->dehydrated()
                        ->helperText('Disposal is handled via the Dispose action, not this field.'),
                ]),
        ]);
    }
}
