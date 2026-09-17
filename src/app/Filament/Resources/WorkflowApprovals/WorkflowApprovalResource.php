<?php

namespace App\Filament\Resources\WorkflowApprovals;

use App\Filament\Resources\WorkflowApprovals\Pages\ListWorkflowApprovals;
use App\Filament\Resources\WorkflowApprovals\Tables\WorkflowApprovalsTable;
use App\Models\Workflow\WorkflowApproval;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * "My Approvals" inbox — pending WorkflowApproval rows actionable by the
 * signed-in user (assigned directly, or role-routed), across every
 * Approvable document type. Read/action only: no create/edit/delete, since
 * rows are materialized exclusively by WorkflowEngine::submit().
 */
class WorkflowApprovalResource extends Resource
{
    protected static ?string $model = WorkflowApproval::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Approvals';

    protected static ?string $navigationLabel = 'My Approvals';

    protected static ?string $modelLabel = 'Approval';

    public static function table(Table $table): Table
    {
        return WorkflowApprovalsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user())->with('approvable');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowApprovals::route('/'),
        ];
    }
}
