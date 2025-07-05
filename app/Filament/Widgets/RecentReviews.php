<?php

namespace App\Filament\Widgets;

use App\Models\Review;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentReviews extends BaseWidget
{
    protected static ?string $heading = 'Recent Reviews';
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Review::query()->with(['user', 'place']))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('vote')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => '👍 Positive',
                        0 => '👎 Negative',
                        default => 'Unknown',
                    })
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'primary' => 'flagged',
                    ]),
                Tables\Columns\TextColumn::make('content')
                    ->label('Review')
                    ->limit(50)
                    ->placeholder('No content'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted')
                    ->since()
                    ->sortable(),
            ])            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Review $record): string => route('filament.admin.resources.reviews.edit', $record)),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Review $record) => $record->update(['status' => 'approved']))
                    ->requiresConfirmation()
                    ->visible(fn (Review $record) => $record->status === 'pending'),
            ]);
    }
}
