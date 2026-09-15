<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ticket')
                ->columns(2)
                ->components([
                    TextInput::make('number')->required()->maxLength(50),
                    TextInput::make('subject')->required()->maxLength(200),
                    Select::make('requester_id')->relationship('requester', 'name')->searchable()->required(),
                    Select::make('assignee_id')->relationship('assignee', 'name')->searchable(),
                    Select::make('department_id')->relationship('department', 'name')->searchable(),
                    TextInput::make('category')->maxLength(100),
                    Select::make('priority')->options([
                        'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
                    ])->default('medium')->required(),
                    TextInput::make('sla_hours')->numeric()->suffix('hours'),
                    Select::make('related_customer_id')->relationship('relatedCustomer', 'name')->searchable(),
                    Select::make('status')->options([
                        'open' => 'Open', 'assigned' => 'Assigned', 'in_progress' => 'In Progress',
                        'pending' => 'Pending', 'resolved' => 'Resolved', 'closed' => 'Closed',
                    ])->default('open')->required(),
                ]),
            Textarea::make('description')->rows(4)->columnSpanFull(),
        ]);
    }
}
