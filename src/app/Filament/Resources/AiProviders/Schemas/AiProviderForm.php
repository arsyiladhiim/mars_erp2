<?php

namespace App\Filament\Resources\AiProviders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Provider Configuration')
                ->columns(2)
                ->components([
                    TextInput::make('name')->required()->maxLength(100)
                        ->helperText('e.g. "OpenAI", "Ollama (local)", "Self-hosted vLLM".'),
                    TextInput::make('default_model')->required()->maxLength(100)
                        ->helperText('e.g. gpt-4o-mini, llama3.1, mistral-nemo.'),
                    TextInput::make('base_url')->required()->url()->maxLength(255)->columnSpanFull()
                        ->helperText('OpenAI-compatible endpoint, e.g. https://api.openai.com/v1'),
                    TextInput::make('api_key')
                        ->password()
                        ->revealable()
                        ->maxLength(500)
                        ->columnSpanFull()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->helperText('Leave blank when editing to keep the currently stored key. Stored encrypted at rest.'),
                    Toggle::make('is_active')
                        ->columnSpanFull()
                        ->helperText('Activating this provider will automatically deactivate all others.'),
                ]),
        ]);
    }
}
