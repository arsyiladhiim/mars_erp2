<?php

namespace App\Filament\Resources\ItAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ItAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_tag')->searchable()->sortable(),
                TextColumn::make('device_type')->badge(),
                TextColumn::make('brand'),
                TextColumn::make('model'),
                TextColumn::make('assignedUser.name')->label('Assigned To')->toggleable(),
                TextColumn::make('warranty_expiry')->date()->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('device_type')->options([
                    'laptop' => 'Laptop', 'desktop' => 'Desktop', 'monitor' => 'Monitor',
                    'printer' => 'Printer', 'server' => 'Server', 'network_device' => 'Network Device',
                    'mobile_device' => 'Mobile Device', 'license' => 'License', 'sim' => 'SIM Card',
                    'peripheral' => 'Peripheral',
                ]),
                SelectFilter::make('status')->options([
                    'in_stock' => 'In Stock', 'assigned' => 'Assigned',
                    'under_repair' => 'Under Repair', 'retired' => 'Retired',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
