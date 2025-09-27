<?php

namespace App\Filament\Resources\UserRewardResource\Pages;

use App\Filament\Resources\UserRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserRewards extends ListRecords
{
    protected static string $resource = UserRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('success'),
        ];
    }
}