<?php

namespace App\Filament\Resources\UserRewardResource\Pages;

use App\Filament\Resources\UserRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserReward extends EditRecord
{
    protected static string $resource = UserRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('heroicon-o-eye')
                ->color('info'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}