<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use App\Services\Inventory\StockOpnamePostingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

class EditStockOpname extends EditRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Post')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => ! in_array($this->record->status, ['posted', 'cancelled'], true))
                ->requiresConfirmation()
                ->modalDescription('Posting reconciles each line against the live stock balance and books any variance. This cannot be undone.')
                ->action(function () {
                    try {
                        app(StockOpnamePostingService::class)->post($this->record);
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Stock Opname posted')->success()->send();
                    } catch (RuntimeException $e) {
                        Notification::make()->title('Could not post')->body($e->getMessage())->danger()->send();
                    }
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
