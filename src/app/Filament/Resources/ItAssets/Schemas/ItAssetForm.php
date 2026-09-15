<?php

namespace App\Filament\Resources\ItAssets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ItAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Device')
                ->columns(3)
                ->components([
                    TextInput::make('asset_tag')->required()->maxLength(50)->unique(ignoreRecord: true),
                    Select::make('device_type')->options([
                        'laptop' => 'Laptop', 'desktop' => 'Desktop', 'monitor' => 'Monitor',
                        'printer' => 'Printer', 'server' => 'Server', 'network_device' => 'Network Device',
                        'mobile_device' => 'Mobile Device', 'license' => 'License', 'sim' => 'SIM Card',
                        'peripheral' => 'Peripheral',
                    ])->required(),
                    Select::make('fixed_asset_id')->relationship('fixedAsset', 'name')->searchable()
                        ->label('Linked Fixed Asset'),
                    TextInput::make('brand')->maxLength(100),
                    TextInput::make('model')->maxLength(100),
                    TextInput::make('serial_number')->maxLength(100),
                ]),
            Section::make('Network & Software')
                ->columns(3)
                ->components([
                    TextInput::make('imei')->maxLength(30),
                    TextInput::make('ip_address')->maxLength(45),
                    TextInput::make('mac_address')->maxLength(30),
                    TextInput::make('os')->maxLength(100),
                    TagsInput::make('software')->columnSpan(2),
                ]),
            Section::make('Assignment')
                ->columns(3)
                ->components([
                    Select::make('assigned_user_id')->relationship('assignedUser', 'name')->searchable(),
                    Select::make('department_id')->relationship('department', 'name')->searchable(),
                    TextInput::make('location')->maxLength(150),
                    DatePicker::make('warranty_expiry'),
                    Select::make('status')->options([
                        'in_stock' => 'In Stock', 'assigned' => 'Assigned',
                        'under_repair' => 'Under Repair', 'retired' => 'Retired',
                    ])->default('in_stock')->required(),
                ]),
        ]);
    }
}
