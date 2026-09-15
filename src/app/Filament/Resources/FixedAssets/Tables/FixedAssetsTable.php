<?php

namespace App\Filament\Resources\FixedAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FixedAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('acquisition_cost')->money('IDR'),
                TextColumn::make('accumulated_depreciation')->money('IDR')->toggleable(),
                TextColumn::make('assignedUser.name')->label('Assigned To')->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'capitalized' => 'Capitalized', 'in_use' => 'In Use',
                    'under_maintenance' => 'Under Maintenance', 'transferred' => 'Transferred',
                    'disposed' => 'Disposed',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
