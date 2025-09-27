<?php

namespace App\Filament\Resources\UserChallengeResource\Pages;

use App\Filament\Resources\UserChallengeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserChallenges extends ListRecords
{
    protected static string $resource = UserChallengeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('success'),
        ];
    }
}