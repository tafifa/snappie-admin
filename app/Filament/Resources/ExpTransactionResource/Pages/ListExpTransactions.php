<?php

namespace App\Filament\Resources\ExpTransactionResource\Pages;

use App\Filament\Resources\ExpTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpTransactions extends ListRecords
{
    protected static string $resource = ExpTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}