<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil')
                ->color('warning'),
            Actions\Action::make('deleteTokens')
                ->label(fn () => 'Revoke All Tokens (' . $this->record->tokens()->count() . ')')
                ->icon('heroicon-o-key')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Revoke All Access Tokens')
                ->modalDescription('Are you sure you want to revoke all active tokens for this user? They will need to login again.')
                ->modalSubmitActionLabel('Yes, Revoke All')
                ->action(function () {
                    $user = $this->record;
                    $tokensCount = $user->tokens()->count();
                    
                    // Delete all tokens for this user
                    $user->tokens()->delete();
                    
                    // Show success notification
                    \Filament\Notifications\Notification::make()
                        ->title('Tokens Revoked')
                        ->body("Successfully revoked {$tokensCount} token(s) for {$user->name}")
                        ->success()
                        ->send();
                })
                ->disabled(fn () => $this->record->tokens()->count() === 0)
                ->tooltip(fn () => $this->record->tokens()->count() === 0 ? 'No active tokens to revoke' : 'Revoke all active tokens'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->color('danger'),
        ];
    }
}
