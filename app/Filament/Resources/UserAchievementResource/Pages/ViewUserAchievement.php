<?php

namespace App\Filament\Resources\UserAchievementResource\Pages;

use App\Filament\Resources\UserAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserAchievement extends ViewRecord
{
    protected static string $resource = UserAchievementResource::class;

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