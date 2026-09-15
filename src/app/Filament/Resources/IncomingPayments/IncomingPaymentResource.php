<?php

namespace App\Filament\Resources\IncomingPayments;

use App\Filament\Resources\IncomingPayments\Pages\CreateIncomingPayment;
use App\Filament\Resources\IncomingPayments\Pages\EditIncomingPayment;
use App\Filament\Resources\IncomingPayments\Pages\ListIncomingPayments;
use App\Filament\Resources\IncomingPayments\Schemas\IncomingPaymentForm;
use App\Filament\Resources\IncomingPayments\Tables\IncomingPaymentsTable;
use App\Models\Finance\IncomingPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncomingPaymentResource extends Resource
{
    protected static ?string $model = IncomingPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return IncomingPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncomingPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncomingPayments::route('/'),
            'create' => CreateIncomingPayment::route('/create'),
            'edit' => EditIncomingPayment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
