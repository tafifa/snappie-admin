<?php

namespace App\Filament\Resources\AppUpdateResource\Pages;

use App\Filament\Resources\AppUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppUpdate extends ViewRecord
{
    protected static string $resource = AppUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil')
                ->color('warning'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}
