<?php

namespace App\Filament\Resources\ExpTransactionResource\Pages;

use App\Filament\Resources\ExpTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpTransaction extends EditRecord
{
    protected static string $resource = ExpTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}