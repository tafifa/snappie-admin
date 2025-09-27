<?php

namespace App\Filament\Resources\UserRewardResource\Pages;

use App\Filament\Resources\UserRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserReward extends ViewRecord
{
    protected static string $resource = UserRewardResource::class;

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