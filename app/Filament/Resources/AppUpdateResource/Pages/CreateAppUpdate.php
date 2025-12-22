<?php

namespace App\Filament\Resources\AppUpdateResource\Pages;

use App\Filament\Resources\AppUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppUpdate extends CreateRecord
{
    protected static string $resource = AppUpdateResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
