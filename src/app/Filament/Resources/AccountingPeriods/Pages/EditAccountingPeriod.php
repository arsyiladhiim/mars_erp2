<?php

namespace App\Filament\Resources\AccountingPeriods\Pages;

use App\Filament\Resources\AccountingPeriods\AccountingPeriodResource;
use App\Services\Finance\PeriodClosingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

class EditAccountingPeriod extends EditRecord
{
    protected static string $resource = AccountingPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close')
                ->label('Close Period')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('danger')
                ->visible(fn () => ! $this->record->isLocked())
                ->requiresConfirmation()
                ->modalDescription('Closing blocks any further postings into this period. Draft journal entries dated within it must be posted or removed first.')
                ->action(function () {
                    try {
                        app(PeriodClosingService::class)->close($this->record, auth()->user());
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Period closed')->success()->send();
                    } catch (RuntimeException $e) {
                        Notification::make()->title('Could not close period')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('reopen')
                ->label('Reopen Period')
                ->icon(Heroicon::OutlinedLockOpen)
                ->color('warning')
                ->visible(fn () => $this->record->status === 'closed')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(PeriodClosingService::class)->reopen($this->record, auth()->user());
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Period reopened')->success()->send();
                    } catch (RuntimeException $e) {
                        Notification::make()->title('Could not reopen period')->body($e->getMessage())->danger()->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
