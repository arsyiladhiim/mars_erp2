<?php

namespace App\Filament\Resources\MaintenanceRequests\Schemas;

use App\Models\Asset\FixedAsset;
use App\Models\Asset\ItAsset;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MaintenanceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('number')->required()->maxLength(50),
            TextInput::make('title')->required()->maxLength(150),
            MorphToSelect::make('maintainable')
                ->types([
                    MorphToSelect\Type::make(FixedAsset::class)->titleAttribute('name'),
                    MorphToSelect\Type::make(ItAsset::class)->titleAttribute('asset_tag'),
                ])
                ->searchable()
                ->columnSpanFull(),
            Select::make('type')->options([
                'preventive' => 'Preventive', 'corrective' => 'Corrective',
            ])->default('corrective')->required(),
            Select::make('technician_id')->relationship('technician', 'name')->searchable(),
            DatePicker::make('scheduled_date'),
            DatePicker::make('completed_date'),
            TextInput::make('cost')->numeric()->prefix('Rp'),
            Textarea::make('description')->rows(2)->columnSpanFull(),
            Textarea::make('parts_used')->rows(2)->columnSpanFull(),
            Select::make('status')->options([
                'requested' => 'Requested', 'scheduled' => 'Scheduled', 'in_progress' => 'In Progress',
                'completed' => 'Completed', 'cancelled' => 'Cancelled',
            ])->default('requested')->required(),
        ])->columns(2);
    }
}
