<?php

namespace App\Filament\Resources\SystemPreferences\Pages;

use App\Filament\Resources\SystemPreferences\SystemPreferenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSystemPreference extends EditRecord
{
    protected static string $resource = SystemPreferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
