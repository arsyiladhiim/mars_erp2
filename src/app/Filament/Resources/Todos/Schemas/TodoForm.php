<?php

namespace App\Filament\Resources\Todos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TodoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
            Textarea::make('description')->rows(3)->columnSpanFull(),
            Select::make('assignee_id')->relationship('assignee', 'name')->searchable(),
            Select::make('department_id')->relationship('department', 'name')->searchable(),
            DatePicker::make('due_date'),
            Select::make('priority')->options([
                'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
            ])->default('medium')->required(),
            Select::make('status')->options([
                'open' => 'Open', 'in_progress' => 'In Progress', 'done' => 'Done', 'cancelled' => 'Cancelled',
            ])->default('open')->required(),
            Repeater::make('checklist')
                ->simple(TextInput::make('item')->required())
                ->columnSpanFull()
                ->addActionLabel('Add checklist item'),
        ])->columns(2);
    }
}
