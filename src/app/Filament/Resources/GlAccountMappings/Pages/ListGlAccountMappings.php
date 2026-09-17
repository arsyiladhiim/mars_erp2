<?php

namespace App\Filament\Resources\GlAccountMappings\Pages;

use App\Filament\Resources\GlAccountMappings\GlAccountMappingResource;
use Filament\Resources\Pages\ListRecords;

class ListGlAccountMappings extends ListRecords
{
    protected static string $resource = GlAccountMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
