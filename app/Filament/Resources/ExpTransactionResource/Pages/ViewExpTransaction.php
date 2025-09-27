<?php

namespace App\Filament\Resources\ExpTransactionResource\Pages;

use App\Filament\Resources\ExpTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExpTransaction extends ViewRecord
{
    protected static string $resource = ExpTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}