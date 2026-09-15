<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('user.name')->label('Actor')->searchable(),
                TextColumn::make('action')->badge(),
                TextColumn::make('entity_type')->label('Entity'),
                TextColumn::make('entity_id')->label('Record ID'),
                TextColumn::make('ip_address'),
            ])
            ->filters([
                SelectFilter::make('action')->options([
                    'created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted',
                    'approved' => 'Approved', 'rejected' => 'Rejected', 'posted' => 'Posted',
                    'login' => 'Login', 'logout' => 'Logout',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([ViewAction::make()]);
    }
}
