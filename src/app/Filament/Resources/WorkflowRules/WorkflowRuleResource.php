<?php

namespace App\Filament\Resources\WorkflowRules;

use App\Filament\Resources\WorkflowRules\Pages\CreateWorkflowRule;
use App\Filament\Resources\WorkflowRules\Pages\EditWorkflowRule;
use App\Filament\Resources\WorkflowRules\Pages\ListWorkflowRules;
use App\Filament\Resources\WorkflowRules\Schemas\WorkflowRuleForm;
use App\Filament\Resources\WorkflowRules\Tables\WorkflowRulesTable;
use App\Models\Workflow\WorkflowRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowRuleResource extends Resource
{
    protected static ?string $model = WorkflowRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return WorkflowRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkflowRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkflowRules::route('/'),
            'create' => CreateWorkflowRule::route('/create'),
            'edit' => EditWorkflowRule::route('/{record}/edit'),
        ];
    }
}
