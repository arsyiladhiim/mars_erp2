<?php

namespace App\Filament\Resources\WorkflowApprovals\Tables;

use App\Models\Workflow\WorkflowApproval;
use App\Services\Workflow\WorkflowEngine;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use RuntimeException;

class WorkflowApprovalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('approvable_type')
                    ->label('Document Type')
                    ->formatStateUsing(fn (string $state) => Str::headline(class_basename($state))),
                TextColumn::make('approvable.number')
                    ->label('Document')
                    ->default(fn (WorkflowApproval $record) => '#'.$record->approvable_id),
                TextColumn::make('step_name')->label('Step'),
                TextColumn::make('due_at')
                    ->label('Due')
                    ->dateTime()
                    ->placeholder('—')
                    ->color(fn (?string $state) => $state && now()->greaterThan($state) ? 'danger' : null),
                TextColumn::make('created_at')->label('Submitted')->dateTime()->since(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->schema([Textarea::make('remarks')->label('Remarks')])
                    ->requiresConfirmation()
                    ->action(function (WorkflowApproval $record, array $data) {
                        try {
                            app(WorkflowEngine::class)->approve($record, auth()->user(), $data['remarks'] ?? null);
                            Notification::make()->title('Approved')->success()->send();
                        } catch (RuntimeException $e) {
                            Notification::make()->title('Could not approve')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->schema([Textarea::make('remarks')->label('Reason')->required()])
                    ->requiresConfirmation()
                    ->action(function (WorkflowApproval $record, array $data) {
                        try {
                            app(WorkflowEngine::class)->reject($record, auth()->user(), $data['remarks']);
                            Notification::make()->title('Rejected')->success()->send();
                        } catch (RuntimeException $e) {
                            Notification::make()->title('Could not reject')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->defaultSort('created_at');
    }
}
