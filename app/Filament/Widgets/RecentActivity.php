<?php

namespace App\Filament\Widgets;

use App\Models\Checkin;
use App\Models\Review;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;

class RecentActivity extends BaseWidget
{
    protected static ?string $heading = '📍 Recent Check-ins';
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Checkin::query()->with(['user', 'place']))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-user')
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-map-pin')
                    ->limit(25),
                    
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->formatStateUsing(function ($record) {
                        if ($record->latitude && $record->longitude) {
                            return number_format($record->latitude, 4) . ', ' . number_format($record->longitude, 4);
                        }
                        return 'No GPS';
                    })
                    ->tooltip(function ($record) {
                        if ($record->latitude && $record->longitude) {
                            return "Lat: {$record->latitude}, Lng: {$record->longitude}";
                        }
                        return 'No GPS coordinates available';
                    })
                    ->badge()
                    ->color(fn ($record) => ($record->latitude && $record->longitude) ? 'success' : 'gray')
                    ->icon('heroicon-m-map'),
                    
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Check-in Time')
                    ->dateTime('M j, g:i A')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y g:i:s A')),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Checkin $record): string => route('filament.admin.resources.checkins.view', $record)),
            ])
            ->emptyStateHeading('No recent check-ins')
            ->emptyStateDescription('Check-ins will appear here once users start checking in.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }
}
