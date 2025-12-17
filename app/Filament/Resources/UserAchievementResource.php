<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAchievementResource\Pages;
use App\Models\UserAchievement;
use App\Models\User;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class UserAchievementResource extends Resource
{
    protected static ?string $model = UserAchievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'User Achievements';

    protected static ?string $modelLabel = 'User Achievement';

    protected static ?string $pluralModelLabel = 'User Achievements';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Relasi User & Achievement
                Forms\Components\Section::make('👤 User & Achievement')
                    ->description('Hubungan antara pengguna dan achievement/challenge')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pengguna')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih pengguna')
                                    ->suffixIcon('heroicon-m-user'),

                                Forms\Components\Select::make('achievement_id')
                                    ->label('Achievement/Challenge')
                                    ->relationship('achievement', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih achievement atau challenge')
                                    ->suffixIcon('heroicon-m-trophy'),
                            ]),
                    ])->collapsible(),

                // Section 2: Progress Tracking
                Forms\Components\Section::make('📊 Progress Tracking')
                    ->description('Progress dan target pencapaian')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('current_progress')
                                    ->label('Progress Saat Ini')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->helperText('Progress yang sudah dicapai')
                                    ->suffixIcon('heroicon-m-chart-bar'),

                                Forms\Components\TextInput::make('target_progress')
                                    ->label('Target Progress')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->helperText('Target yang harus dicapai')
                                    ->suffixIcon('heroicon-m-flag'),

                                Forms\Components\DatePicker::make('period_date')
                                    ->label('Periode')
                                    ->helperText('Untuk challenge dengan reset (daily/weekly)')
                                    ->native(false),
                            ]),
                    ])->collapsible(),

                // Section 3: Status Completion
                Forms\Components\Section::make('✅ Status Completion')
                    ->description('Status penyelesaian')
                    ->schema([
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Tanggal Selesai')
                            ->helperText('Waktu achievement/challenge diselesaikan')
                            ->native(false),
                    ])->collapsible(),

                // Section 4: Informasi Tambahan
                Forms\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->description('Data tambahan dalam format JSON')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Informasi Tambahan')
                            ->helperText('Data tambahan dalam format key-value (opsional)')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Tambah Info'),
                    ])->collapsible()->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'achievement']))
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-user')
                    ->limit(25),

                Tables\Columns\TextColumn::make('achievement.name')
                    ->label('Achievement')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-trophy')
                    ->limit(30),

                Tables\Columns\TextColumn::make('achievement.type')
                    ->label('Tipe')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'achievement' => 'success',
                        'challenge' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'achievement' => 'Achievement',
                        'challenge' => 'Challenge',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('current_progress')
                    ->label('Progress')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (UserAchievement $record): string => 
                        $record->current_progress . ' / ' . $record->target_progress
                    ),

                // Tables\Columns\TextColumn::make('period_date')
                //     ->label('Periode')
                //     ->date('d M Y')
                //     ->sortable()
                //     ->toggleable()
                //     ->placeholder('One-time'),

                Tables\Columns\IconColumn::make('completed_at')
                    ->label('Selesai')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable()
                    ->getStateUsing(fn (UserAchievement $record): bool => $record->completed_at !== null),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable() // visible by default, can be toggled off
                    ->placeholder('Belum selesai'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('achievement.type')
                    ->label('Tipe')
                    ->options([
                        'achievement' => 'Achievement',
                        'challenge' => 'Challenge',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return $query->whereHas('achievement', function (Builder $q) use ($data) {
                                $q->where('type', $data['value']);
                            });
                        }
                        return $query;
                    }),

                Tables\Filters\TernaryFilter::make('completed')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Selesai')
                    ->falseLabel('Belum Selesai')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('completed_at'),
                        false: fn (Builder $query) => $query->whereNull('completed_at'),
                    ),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('achievement_id')
                    ->label('Achievement')
                    ->relationship('achievement', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('high_reward')
                    ->label('High Reward Achievement')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('achievement', fn ($q) => $q->where('coin_reward', '>=', 500))
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('completed_today')
                    ->label('Selesai Hari Ini')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereNotNull('completed_at')->whereDate('completed_at', today())
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('complete')
                    ->label('Mark Completed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (UserAchievement $record) => $record->update(['completed_at' => now()]))
                    ->requiresConfirmation()
                    ->visible(fn (UserAchievement $record): bool => $record->completed_at === null),

                Tables\Actions\Action::make('mark_incomplete')
                    ->label('Mark Incomplete')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->action(fn (UserAchievement $record) => $record->update(['completed_at' => null]))
                    ->requiresConfirmation()
                    ->visible(fn (UserAchievement $record): bool => $record->completed_at !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('complete')
                        ->label('Mark Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['completed_at' => now()]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('mark_incomplete')
                        ->label('Mark Incomplete')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['completed_at' => null]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada user achievement')
            ->emptyStateDescription('User achievement akan muncul ketika pengguna meraih achievement.')
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi User & Achievement
                Infolists\Components\Section::make('👤 Informasi User & Achievement')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Pengguna')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                Infolists\Components\TextEntry::make('achievement.name')
                                    ->label('Achievement/Challenge')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-trophy'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('achievement.type')
                                    ->label('Tipe')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'achievement' => 'success',
                                        'challenge' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'achievement' => 'Achievement',
                                        'challenge' => 'Challenge',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('achievement.reset_schedule')
                                    ->label('Reset Schedule')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'none' => 'gray',
                                        'daily' => 'info',
                                        'weekly' => 'primary',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'none' => 'One-time',
                                        'daily' => 'Daily',
                                        'weekly' => 'Weekly',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('period_date')
                                    ->label('Periode')
                                    ->date('d M Y')
                                    ->placeholder('One-time'),
                            ]),
                    ])->collapsible(),

                // Section 2: Progress Tracking
                Infolists\Components\Section::make('📊 Progress Tracking')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('current_progress')
                                    ->label('Current Progress')
                                    ->numeric()
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-chart-bar'),

                                Infolists\Components\TextEntry::make('target_progress')
                                    ->label('Target')
                                    ->numeric()
                                    ->badge()
                                    ->color('purple')
                                    ->icon('heroicon-m-flag'),

                                Infolists\Components\TextEntry::make('progress_percentage')
                                    ->label('Progress %')
                                    ->suffix('%')
                                    ->badge()
                                    ->color(fn (UserAchievement $record): string => match (true) {
                                        $record->target_progress == 0 => 'gray',
                                        ($record->current_progress / $record->target_progress * 100) >= 100 => 'success',
                                        ($record->current_progress / $record->target_progress * 100) >= 75 => 'info',
                                        ($record->current_progress / $record->target_progress * 100) >= 50 => 'warning',
                                        default => 'danger',
                                    })
                                    ->formatStateUsing(fn (UserAchievement $record): string =>
                                        $record->target_progress > 0
                                            ? round($record->current_progress / $record->target_progress * 100, 1)
                                            : '0'
                                    ),

                                Infolists\Components\IconEntry::make('completed_at')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-clock')
                                    ->trueColor('success')
                                    ->falseColor('warning')
                                    ->getStateUsing(fn (UserAchievement $record): bool => $record->completed_at !== null),
                            ]),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Tanggal Selesai')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Belum selesai')
                            ->badge()
                            ->color('success'),
                    ])->collapsible(),

                // Section 3: Reward Information
                Infolists\Components\Section::make('💰 Reward Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('achievement.coin_reward')
                                    ->label('Coin Reward')
                                    ->numeric()
                                    ->suffix(' 🪙')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-currency-dollar'),

                                Infolists\Components\TextEntry::make('achievement.reward_xp')
                                    ->label('XP Reward')
                                    ->numeric()
                                    ->suffix(' ⭐')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-star'),

                                Infolists\Components\TextEntry::make('achievement.criteria_action')
                                    ->label('Criteria Action')
                                    ->badge()
                                    ->color('indigo')
                                    ->icon('heroicon-m-bolt'),
                            ]),
                    ])->collapsible(),

                // Section 4: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (UserAchievement $record): bool => !empty($record->additional_info))
                    ->collapsible(),

                // Section 5: Riwayat
                Infolists\Components\Section::make('📅 Riwayat')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Diperoleh')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-m-calendar'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ]),
            ]);
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
            'index' => Pages\ListUserAchievements::route('/'),
            'create' => Pages\CreateUserAchievement::route('/create'),
            'view' => Pages\ViewUserAchievement::route('/{record}'),
            'edit' => Pages\EditUserAchievement::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'navigation_badge_user_achievements',
            now()->addMinutes(10),
            fn () => (string) static::getModel()::where('status', false)->count()
        );
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['user.name', 'achievement.name'];
    }
}