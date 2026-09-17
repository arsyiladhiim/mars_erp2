<?php

namespace App\Filament\Resources\GlAccountMappings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GlAccountMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->disabled()->dehydrated(false),
            Select::make('chart_of_account_id')
                ->label('Mapped Account')
                ->relationship('chartOfAccount', 'name', fn ($query) => $query->where('is_active', true))
                ->searchable()
                ->preload()
                ->required()
                ->helperText('Pilih akun dari Chart of Accounts yang akan dipakai posting engine untuk transaksi jenis ini.'),
        ])->columns(1);
    }
}
