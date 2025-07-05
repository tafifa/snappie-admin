<?php

namespace App\Filament\Resources\CheckinResource\Pages;

use App\Filament\Resources\CheckinResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckin extends CreateRecord
{
    protected static string $resource = CheckinResource::class;
}
