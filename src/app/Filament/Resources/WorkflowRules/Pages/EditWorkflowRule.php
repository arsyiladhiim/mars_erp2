<?php

namespace App\Filament\Resources\WorkflowRules\Pages;

use App\Filament\Resources\WorkflowRules\WorkflowRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowRule extends EditRecord
{
    protected static string $resource = WorkflowRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
