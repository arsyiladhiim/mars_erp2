<?php

namespace App\Filament\Resources\WorkflowRules\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkflowRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rule')
                ->columns(2)
                ->components([
                    Select::make('company_id')->relationship('company', 'name')->searchable()->required(),
                    TextInput::make('name')->required()->maxLength(150),
                    Select::make('document_type')->options([
                        'purchase_request' => 'Purchase Request',
                        'purchase_order' => 'Purchase Order',
                        'sales_order' => 'Sales Order',
                        'stock_transfer' => 'Stock Transfer',
                        'stock_adjustment' => 'Stock Adjustment',
                        'outgoing_payment' => 'Outgoing Payment',
                    ])->required(),
                    TextInput::make('priority')->numeric()->default(0),
                    Toggle::make('is_active')->default(true),
                ]),
            Section::make('Approval Steps')
                ->description('Sequential or parallel approvers for this document type (PRD §23).')
                ->components([
                    Repeater::make('steps')
                        ->relationship()
                        ->columns(2)
                        ->schema([
                            TextInput::make('sequence')->numeric()->required()->default(1),
                            TextInput::make('name')->required()->maxLength(100),
                            Select::make('approver_type')->options([
                                'role' => 'Role', 'user' => 'Specific User', 'department_head' => 'Department Head',
                            ])->default('role')->required(),
                            TextInput::make('approver_role')->maxLength(100),
                            Select::make('approver_user_id')->relationship('approverUser', 'name')->searchable(),
                            Select::make('mode')->options([
                                'sequential' => 'Sequential', 'parallel' => 'Parallel',
                            ])->default('sequential')->required(),
                            TextInput::make('deadline_hours')->numeric(),
                        ])
                        ->reorderable('sequence')
                        ->collapsible(),
                ]),
        ]);
    }
}
