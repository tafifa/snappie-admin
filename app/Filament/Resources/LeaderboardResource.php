<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaderboardResource\Pages;
use App\Models\Leaderboard;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeaderboardResource extends Resource
{
    protected static ?string $model = Leaderboard::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Leaderboards';

    protected static ?string $modelLabel = 'Leaderboard';

    protected static ?string $pluralModelLabel = 'Leaderboards';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 9;

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\Section::make('📊 Leaderboard Data')
    //                 ->description('Data ranking pengguna (JSON format)')
    //                 ->schema([
    //                     Forms\Components\Actions::make([
    //                         Forms\Components\Actions\Action::make('generate_leaderboard')
    //                             ->label('Generate Leaderboard')
    //                             ->icon('heroicon-o-sparkles')
    //                             ->color('success')
    //                             ->requiresConfirmation()
    //                             ->modalHeading('Generate Leaderboard')
    //                             ->modalDescription('This will generate a leaderboard based on the top 10 users with the highest total EXP. This action will overwrite any existing leaderboard data.')
    //                             ->modalSubmitActionLabel('Generate')
    //                             ->action(function ($livewire, $set) {
    //                                 $leaderboardData = app(\App\Services\LeaderboardService::class)->getTopUsersThisMonth(7);

    //                                 // Set all form fields
    //                                 $set('leaderboard', json_encode($leaderboardData, true));
    //                                 // dd(json_decode($leaderboardData));
    //                                 $set('started_at', now()->startOfMonth()->format('Y-m-d H:i:s'));
    //                                 $set('ended_at', now()->endOfMonth()->format('Y-m-d H:i:s'));

    //                                 \Filament\Notifications\Notification::make()
    //                                     ->title('Leaderboard Generated Successfully')
    //                                     ->body('Top 10 users by EXP have been loaded into the leaderboard.')
    //                                     ->success()
    //                                     ->send();
    //                             }),
    //                     ]),

    //                     Forms\Components\DateTimePicker::make('started_at')
    //                         ->label('Start Date')
    //                         ->helperText('Tanggal mulai periode leaderboard'),

    //                     Forms\Components\DateTimePicker::make('ended_at')
    //                         ->label('End Date')
    //                         ->helperText('Tanggal berakhir periode leaderboard'),

    //                     Forms\Components\Select::make('status')
    //                         ->label('Status')
    //                         ->options([
    //                             true => 'Active',
    //                             false => 'Inactive',
    //                         ])
    //                         ->helperText('Status leaderboard')
    //                         ->default(true),

    //                     // Forms\Components\Repeater::make('leaderboard')
    //                     //     ->label('Leaderboard')
    //                     //     ->schema([
    //                     //         Forms\Components\TextInput::make('name')
    //                     //             ->label('Name')
    //                     //             ->required(),
    //                     //         Forms\Components\TextInput::make('exp')
    //                     //             ->label('EXP')
    //                     //             ->numeric()
    //                     //             ->required(),
    //                     //     ])
    //                     //     ->required(),

    //                     Forms\Components\Textarea::make('leaderboard')
    //                         ->label('Leaderboard JSON')
    //                         ->rows(10)
    //                         ->helperText('Data leaderboard dalam format JSON. Akan diupdate otomatis oleh sistem.')
    //                         ->columnSpanFull(),
    //                 ])
    //                 ->collapsible(),
    //         ]);
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📊 Leaderboard Data')
                    ->description('Data ranking pengguna (JSON format)')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_leaderboard')
                                ->label('Generate Leaderboard')
                                ->icon('heroicon-o-sparkles')
                                ->color('success')
                                ->requiresConfirmation()
                                ->modalHeading('Generate Leaderboard')
                                ->modalDescription('This will generate a leaderboard based on the top 10 users with the highest total EXP. This action will overwrite any existing leaderboard data.')
                                ->modalSubmitActionLabel('Generate')
                                ->action(function ($set) {
                                    $leaderboardData = app(\App\Services\LeaderboardService::class)->getTopUsersThisMonth(7);
                                    $set('leaderboard', $leaderboardData);  // Directly store the array

                                    // Set start and end dates for leaderboard
                                    // dd($leaderboardData);
                                    $set('started_at', now()->startOfMonth()->format('Y-m-d H:i:s'));
                                    $set('ended_at', now()->endOfMonth()->format('Y-m-d H:i:s'));

                                    // Notification after generating leaderboard
                                    \Filament\Notifications\Notification::make()
                                        ->title('Leaderboard Generated Successfully')
                                        ->body('Top 10 users by EXP have been loaded into the leaderboard.')
                                        ->success()
                                        ->send();
                                }),
                        ]),

                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Start Date')
                            ->helperText('Tanggal mulai periode leaderboard'),

                        Forms\Components\DateTimePicker::make('ended_at')
                            ->label('End Date')
                            ->helperText('Tanggal berakhir periode leaderboard'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                true => 'Active',
                                false => 'Inactive',
                            ])
                            ->helperText('Status leaderboard')
                            ->default(true),

                        Forms\Components\Repeater::make('leaderboard')
                            ->label('Leaderboard')
                            ->reorderable('false')
                            ->schema([
                                Forms\Components\TextInput::make('rank')
                                    ->label('Rank')
                                    ->required(),
                                Forms\Components\TextInput::make('image_url')
                                    ->label('Profile Image URL')
                                    ->required(),
                                Forms\Components\TextInput::make('name')       // 'name' field for the leaderboard
                                    ->label('User')
                                    ->required(),
                                Forms\Components\TextInput::make('total_exp')  // 'total_exp' field for the leaderboard
                                    ->label('EXP')
                                    ->numeric()
                                    ->required(),
                            ]),


                    ])
                    ->collapsible(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status')
                    ->placeholder('All leaderboards')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_from'),
                        Forms\Components\DatePicker::make('start_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('started_at', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('started_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('refresh')
                    ->label('Refresh Data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (Leaderboard $record) {
                        // Call leaderboard service to refresh data
                        app(\App\Services\LeaderboardService::class)->refreshLeaderboard();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Refresh Leaderboard Data')
                    ->modalDescription('This will update the leaderboard with current user rankings.')
                    ->visible(fn(Leaderboard $record): bool => $record->status),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('🏆 Leaderboard Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('started_at')
                                    ->label('Start Date')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('ended_at')
                                    ->label('End Date')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn(string $state): string => $state ? 'Active' : 'Inactive'),
                            ])->columns(3),

                        Infolists\Components\Section::make('🥇 Top 10 Rankings')
                            ->schema([
                                RepeatableEntry::make('leaderboard')
                                    ->label('Leaderboard')
                                    ->schema([
                                        TextEntry::make('rank')->label('Rank'),
                                        TextEntry::make('image_url')->label(''),
                                        TextEntry::make('name')->label(''),
                                        TextEntry::make('total_exp')->label('')->badge()->color('warning')->icon('heroicon-o-star'),
                                    ])
                                    ->default('Tidak ada data')
                                    ->columns(4),
                            ]),

                        Infolists\Components\Section::make('📅 Timeline')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime()
                                    ->since(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime()
                                    ->since(),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),
            ])
            ->columns(2);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaderboards::route('/'),
            'create' => Pages\CreateLeaderboard::route('/create'),
            'view' => Pages\ViewLeaderboard::route('/{record}'),
            'edit' => Pages\EditLeaderboard::route('/{record}/edit'),
        ];
    }
}
