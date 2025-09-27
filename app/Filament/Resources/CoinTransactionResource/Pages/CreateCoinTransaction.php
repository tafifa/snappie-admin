<?php

namespace App\Filament\Resources\CoinTransactionResource\Pages;

use App\Filament\Resources\CoinTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCoinTransaction extends CreateRecord
{
    protected static string $resource = CoinTransactionResource::class;
}