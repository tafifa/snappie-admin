<?php

namespace App\Filament\Resources\RewardResource\Pages;

use App\Filament\Resources\RewardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReward extends EditRecord
{
    protected static string $resource = RewardResource::class;

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