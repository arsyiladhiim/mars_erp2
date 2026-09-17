<?php

namespace App\Filament\Resources\JournalEntries\Pages;

use App\Filament\Resources\JournalEntries\JournalEntryResource;
use App\Services\Finance\JournalEntryPostingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Post')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalDescription('Posting checks that total debit equals total credit, then locks this entry. This cannot be undone.')
                ->action(function () {
                    try {
                        app(JournalEntryPostingService::class)->post($this->record);
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Journal Entry posted')->success()->send();
                    } catch (RuntimeException $e) {
                        Notification::make()->title('Could not post')->body($e->getMessage())->danger()->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
