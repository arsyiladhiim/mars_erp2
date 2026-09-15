<?php

namespace App\Filament\Resources\WorkflowRules\Pages;

use App\Filament\Resources\WorkflowRules\WorkflowRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowRules extends ListRecords
{
    protected static string $resource = WorkflowRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
