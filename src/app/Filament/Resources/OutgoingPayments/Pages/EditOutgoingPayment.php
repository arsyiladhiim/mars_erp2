<?php

namespace App\Filament\Resources\OutgoingPayments\Pages;

use App\Filament\Resources\OutgoingPayments\OutgoingPaymentResource;
use App\Services\Finance\OutgoingPaymentPostingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

class EditOutgoingPayment extends EditRecord
{
    protected static string $resource = OutgoingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Post')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalDescription('Posting will book the cash disbursement and update the applied invoice\'s paid amount. This cannot be undone.')
                ->action(function () {
                    try {
                        app(OutgoingPaymentPostingService::class)->post($this->record);
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Outgoing Payment posted')->success()->send();
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
