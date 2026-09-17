<?php

namespace App\Filament\Resources\WorkflowApprovals\Pages;

use App\Filament\Resources\WorkflowApprovals\WorkflowApprovalResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowApprovals extends ListRecords
{
    protected static string $resource = WorkflowApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
