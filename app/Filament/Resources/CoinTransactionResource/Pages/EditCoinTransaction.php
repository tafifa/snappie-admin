<?php

namespace App\Filament\Resources\CoinTransactionResource\Pages;

use App\Filament\Resources\CoinTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoinTransaction extends EditRecord
{
    protected static string $resource = CoinTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}