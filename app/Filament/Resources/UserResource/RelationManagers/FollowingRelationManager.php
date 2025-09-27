<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\UserResource;

class FollowingRelationManager extends RelationManager
{
    protected static string $relationship = 'following';

    protected static ?string $title = 'Following';

    protected static ?string $modelLabel = 'Following';

    protected static ?string $pluralModelLabel = 'Following';

    protected static ?string $icon = 'heroicon-o-user-plus';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->prefix('@')
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Following Since')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('recent_following')
                    ->label('Recently Followed (Last 30 days)')
                    ->query(fn (Builder $query): Builder => $query->wherePivot('created_at', '>=', now()->subDays(30))),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record])),
                
                Tables\Actions\DetachAction::make()
                    ->label('Unfollow')
                    ->icon('heroicon-o-user-minus')
                    ->requiresConfirmation()
                    ->modalHeading('Unfollow User')
                    ->modalDescription('Are you sure you want to unfollow this user? This action cannot be undone.')
                    ->modalSubmitActionLabel('Unfollow'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Unfollow Selected')
                        ->icon('heroicon-o-user-minus')
                        ->requiresConfirmation()
                        ->modalHeading('Unfollow Selected Users')
                        ->modalDescription('Are you sure you want to unfollow the selected users? This action cannot be undone.')
                        ->modalSubmitActionLabel('Unfollow'),
                ]),
            ])
            ->poll('30s')
            ->emptyStateHeading('Not Following Anyone')
            ->emptyStateDescription('This user is not following anyone yet.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}