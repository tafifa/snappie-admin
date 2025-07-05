<?php

namespace App\Filament\Resources\PlaceResource\Pages;

use App\Filament\Resources\PlaceResource;
use App\Models\Checkin;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class PlaceCheckins extends ListRecords
{
    protected static string $resource = PlaceResource::class;

    protected static ?string $title = 'Place Check-ins';

    public function getTableQuery(): Builder
    {
        $placeId = $this->getRecord()->id;
        
        return Checkin::query()
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

                Tables\Columns\TextColumn::make('time')
                    ->label('Check-in Time')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('check_in_status')
                    ->label('Check-in Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'done',
                        'danger' => 'notdone',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('mission_status')
                    ->label('Mission Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('mission_completed_at')
                    ->label('Mission Completed')
                    ->dateTime()
                    ->placeholder('Not completed')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('check_in_status')
                    ->label('Check-in Status')
                    ->options([
                        'pending' => 'Pending',
                        'done' => 'Done',
                        'notdone' => 'Not Done',
                    ]),

                SelectFilter::make('mission_status')
                    ->label('Mission Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Checkin $record): string => route('filament.admin.resources.checkins.view', $record)),

                Tables\Actions\Action::make('complete_checkin')
                    ->label('Mark as Done')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Checkin $record): bool => $record->check_in_status !== 'done')
                    ->action(fn (Checkin $record) => $record->update(['check_in_status' => 'done']))
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('complete_mission')
                    ->label('Complete Mission')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Checkin $record): bool => $record->mission_status !== 'completed')
                    ->action(fn (Checkin $record) => $record->update([
                        'mission_status' => 'completed',
                        'mission_completed_at' => now()
                    ]))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('complete_checkins')
                        ->label('Mark as Done')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['check_in_status' => 'done'])))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('complete_missions')
                        ->label('Complete Missions')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update([
                            'mission_status' => 'completed',
                            'mission_completed_at' => now()
                        ])))
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('time', 'desc');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->route('filament.admin.resources.places.index') => 'Places',
            url()->route('filament.admin.resources.places.view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            'Check-ins',
        ];
    }
}
