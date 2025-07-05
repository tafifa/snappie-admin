<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Review;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class UserReviews extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'User Reviews';

    public function getTableQuery(): Builder
    {
        $userId = $this->getRecord()->id;
        
        return Review::query()
            ->where('user_id', $userId)
            ->with(['place']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->route('filament.admin.resources.users.index') => 'Users',
            url()->route('filament.admin.resources.users.view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            'Reviews',
        ];
    }
}
