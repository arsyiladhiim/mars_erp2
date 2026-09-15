<?php

namespace App\Filament\Resources\Memos\Pages;

use App\Filament\Resources\Memos\MemoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemos extends ListRecords
{
    protected static string $resource = MemoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
