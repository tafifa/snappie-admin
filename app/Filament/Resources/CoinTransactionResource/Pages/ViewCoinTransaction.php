<?php

namespace App\Filament\Resources\CoinTransactionResource\Pages;

use App\Filament\Resources\CoinTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCoinTransaction extends ViewRecord
{
    protected static string $resource = CoinTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}