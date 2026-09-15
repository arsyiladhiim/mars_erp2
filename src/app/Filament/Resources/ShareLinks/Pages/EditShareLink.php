<?php

namespace App\Filament\Resources\ShareLinks\Pages;

use App\Filament\Resources\ShareLinks\ShareLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShareLink extends EditRecord
{
    protected static string $resource = ShareLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
