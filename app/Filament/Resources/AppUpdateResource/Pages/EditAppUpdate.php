<?php

namespace App\Filament\Resources\AppUpdateResource\Pages;

use App\Filament\Resources\AppUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppUpdate extends EditRecord
{
    protected static string $resource = AppUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                ->color('info'),
            Actions\DeleteAction::make(),
        ];
    }
}
