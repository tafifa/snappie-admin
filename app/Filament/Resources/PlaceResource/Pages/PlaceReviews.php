<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use App\Models\Review;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class PlaceReviews extends ListRecords
{
    protected static string $resource = PlaceResource::class;

    protected static ?string $title = 'Place Reviews';

    public function getTableQuery(): Builder
    {
        $placeId = $this->getRecord()->id;
        
        return Review::query()
            ->where('place_id', $placeId)
            ->with(['user']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Review Content')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('vote')
                    ->label('Rating')
                    ->formatStateUsing(fn (string $state): string => str_repeat('⭐', (int)$state))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('vote')
                    ->label('Rating')
                    ->options([
                        '1' => '⭐ (1 Star)',
                        '2' => '⭐⭐ (2 Stars)',
                        '3' => '⭐⭐⭐ (3 Stars)',
                        '4' => '⭐⭐⭐⭐ (4 Stars)',
                        '5' => '⭐⭐⭐⭐⭐ (5 Stars)',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Review $record): string => route('filament.admin.resources.reviews.view', $record)),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record): bool => $record->status !== 'approved')
                    ->action(fn (Review $record) => $record->update(['status' => 'approved']))
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Review $record): bool => $record->status !== 'rejected')
                    ->action(fn (Review $record) => $record->update(['status' => 'rejected']))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['status' => 'approved'])))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['status' => 'rejected'])))
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->route('filament.admin.resources.places.index') => 'Places',
            url()->route('filament.admin.resources.places.view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            'Reviews',
        ];
    }
}
