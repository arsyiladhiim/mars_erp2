<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('user.name')->label('Actor'),
            TextEntry::make('action')->badge(),
            TextEntry::make('entity_type')->label('Entity'),
            TextEntry::make('entity_id')->label('Record ID'),
            TextEntry::make('ip_address'),
            TextEntry::make('user_agent')->columnSpanFull(),
            CodeEntry::make('before')->columnSpanFull(),
            CodeEntry::make('after')->columnSpanFull(),
        ])->columns(2);
    }
}
