<?php

namespace App\Filament\Resources\AppUpdateResource\Pages;

use App\Filament\Resources\AppUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAppUpdates extends ListRecords
{
    protected static string $resource = AppUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
