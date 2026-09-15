<?php

namespace App\Filament\Resources\Todos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TodosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('assignee.name')->searchable(),
                TextColumn::make('due_date')->date()->sortable(),
                TextColumn::make('priority')->badge()->color(fn (string $state) => match ($state) {
                    'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'gray',
                }),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Open', 'in_progress' => 'In Progress', 'done' => 'Done', 'cancelled' => 'Cancelled',
                ]),
                SelectFilter::make('priority')->options([
                    'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
                ]),
            ])
            ->defaultSort('due_date')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
