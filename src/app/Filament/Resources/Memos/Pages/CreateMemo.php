<?php

namespace App\Filament\Resources\Memos\Pages;

use App\Filament\Resources\Memos\MemoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMemo extends CreateRecord
{
    protected static string $resource = MemoResource::class;
}
