<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\AchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

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