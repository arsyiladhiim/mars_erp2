<?php

namespace App\Filament\Resources\SystemPreferences\Pages;

use App\Filament\Resources\SystemPreferences\SystemPreferenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSystemPreferences extends ListRecords
{
    protected static string $resource = SystemPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
