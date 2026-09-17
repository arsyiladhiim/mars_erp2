<?php

namespace App\Filament\Resources\Tickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable(),
                TextColumn::make('subject')->searchable(),
                TextColumn::make('requester.name')->searchable(),
                TextColumn::make('assignee.name')->toggleable(),
                TextColumn::make('priority')->badge()->color(fn (string $state) => match ($state) {
                    'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'gray',
                }),
                TextColumn::make('due_at')
                    ->label('SLA Due')
                    ->dateTime()
                    ->placeholder('—')
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'resolved', 'closed' => 'success',
                    default => 'gray',
                }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Open', 'assigned' => 'Assigned', 'in_progress' => 'In Progress',
                    'pending' => 'Pending', 'resolved' => 'Resolved', 'closed' => 'Closed',
                ]),
                SelectFilter::make('priority')->options([
                    'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
