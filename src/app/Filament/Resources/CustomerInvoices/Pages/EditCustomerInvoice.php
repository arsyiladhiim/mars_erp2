<?php

namespace App\Filament\Resources\CustomerInvoices\Pages;

use App\Filament\Resources\CustomerInvoices\CustomerInvoiceResource;
use App\Services\Sales\CustomerInvoicePostingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use RuntimeException;

class EditCustomerInvoice extends EditRecord
{
    protected static string $resource = CustomerInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('post')
                ->label('Post')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalDescription('Posting will book the Accounts Receivable journal entry for this invoice. This cannot be undone.')
                ->action(function () {
                    try {
                        app(CustomerInvoicePostingService::class)->post($this->record);
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Customer Invoice posted')->success()->send();
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
