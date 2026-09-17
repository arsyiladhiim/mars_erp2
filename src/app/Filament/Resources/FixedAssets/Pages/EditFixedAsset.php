<?php

namespace App\Filament\Resources\FixedAssets\Pages;

use App\Filament\Resources\FixedAssets\FixedAssetResource;
use App\Models\Finance\BankAccount;
use App\Services\Asset\AssetDisposalService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use RuntimeException;

class EditFixedAsset extends EditRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dispose')
                ->label('Dispose')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible(fn () => $this->record->status !== 'disposed')
                ->schema([
                    DatePicker::make('disposal_date')->required()->default(now()),
                    TextInput::make('proceeds')->numeric()->prefix('Rp')->default(0)
                        ->helperText('Amount received for the asset, if any (0 for a scrap/write-off).'),
                    Select::make('bank_account_id')
                        ->options(fn () => BankAccount::query()->pluck('account_name', 'id'))
                        ->searchable()->label('Deposit To')
                        ->helperText('Required only if proceeds are greater than zero.'),
                ])
                ->requiresConfirmation()
                ->modalDescription('Posts the disposal journal entry and marks this asset as disposed. This cannot be undone.')
                ->action(function (array $data) {
                    try {
                        app(AssetDisposalService::class)->dispose(
                            $this->record,
                            Carbon::parse($data['disposal_date']),
                            (float) ($data['proceeds'] ?? 0),
                            $data['bank_account_id'] ?? null,
                        );
                        $this->record->refresh();
                        $this->fillForm();

                        Notification::make()->title('Asset disposed')->success()->send();
                    } catch (RuntimeException $e) {
                        Notification::make()->title('Could not dispose asset')->body($e->getMessage())->danger()->send();
                    }
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
