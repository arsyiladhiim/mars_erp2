<?php

namespace App\Filament\Resources\OutgoingPayments;

use App\Filament\Resources\OutgoingPayments\Pages\CreateOutgoingPayment;
use App\Filament\Resources\OutgoingPayments\Pages\EditOutgoingPayment;
use App\Filament\Resources\OutgoingPayments\Pages\ListOutgoingPayments;
use App\Filament\Resources\OutgoingPayments\Schemas\OutgoingPaymentForm;
use App\Filament\Resources\OutgoingPayments\Tables\OutgoingPaymentsTable;
use App\Models\Finance\OutgoingPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OutgoingPaymentResource extends Resource
{
    protected static ?string $model = OutgoingPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    public static function form(Schema $schema): Schema
    {
        return OutgoingPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OutgoingPaymentsTable::configure($table);
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
            'index' => ListOutgoingPayments::route('/'),
            'create' => CreateOutgoingPayment::route('/create'),
            'edit' => EditOutgoingPayment::route('/{record}/edit'),
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
