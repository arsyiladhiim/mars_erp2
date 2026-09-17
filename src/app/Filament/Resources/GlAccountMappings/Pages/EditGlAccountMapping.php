<?php

namespace App\Filament\Resources\GlAccountMappings\Pages;

use App\Filament\Resources\GlAccountMappings\GlAccountMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGlAccountMapping extends EditRecord
{
    protected static string $resource = GlAccountMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
