<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Checkin;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;

class UserCheckins extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'User Check-ins';

    public function getTableQuery(): Builder
    {
        $userId = $this->getRecord()->id;
        
        return Checkin::query()
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('time', 'desc');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->route('filament.admin.resources.users.index') => 'Users',
            url()->route('filament.admin.resources.users.view', ['record' => $this->getRecord()]) => $this->getRecord()->name,
            'Check-ins',
        ];
    }
}
