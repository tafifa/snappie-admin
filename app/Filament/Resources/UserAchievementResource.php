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
                    ->description('Hubungan antara pengguna dan achievement')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pengguna')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih pengguna yang mendapat achievement')
                                    ->suffixIcon('heroicon-m-user'),

                                Forms\Components\Select::make('achievement_id')
                                    ->label('Achievement')
                                    ->relationship('achievement', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih achievement yang diperoleh')
                                    ->suffixIcon('heroicon-m-trophy'),
                            ]),
                    ])->collapsible(),

                // Section 2: Status Achievement
                Forms\Components\Section::make('✅ Status Achievement')
                    ->description('Status penyelesaian achievement')
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Status Selesai')
                            ->default(false)
                            ->helperText('Apakah achievement sudah diselesaikan?')
                            ->onColor('success')
                            ->offColor('warning'),
                    ])->collapsible(),

                // Section 3: Informasi Tambahan
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

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diperoleh')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->tooltip(function (UserAchievement $record): string {
                        return 'Achievement diperoleh pada: ' . $record->created_at->format('d M Y H:i');
                    }),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Selesai')
                    ->falseLabel('Belum Selesai'),

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
                        $query->where('status', true)->whereDate('updated_at', today())
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
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (UserAchievement $record) => $record->update(['status' => true]))
                    ->requiresConfirmation()
                    ->visible(fn (UserAchievement $record): bool => !$record->status),

                Tables\Actions\Action::make('mark_pending')
                    ->label('Tandai Pending')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->action(fn (UserAchievement $record) => $record->update(['status' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (UserAchievement $record): bool => $record->status),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('complete')
                        ->label('Selesaikan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('mark_pending')
                        ->label('Tandai Pending Terpilih')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => false]))
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
                                    ->label('Achievement')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-trophy'),
                            ]),
                    ]),

                // Section 2: Status & Reward
                Infolists\Components\Section::make('✅ Status & Reward')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\IconEntry::make('status')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-clock')
                                    ->trueColor('success')
                                    ->falseColor('warning'),
                                
                                // TODO: Add progress description
                                Infolists\Components\TextEntry::make('progress')
                                    ->label('Progress')
                                    ->suffix('%')
                                    ->badge()
                                    ->color(fn (?int $state): string => match (true) {
                                        $state >= 100 => 'success',
                                        $state >= 75 => 'info',
                                        $state >= 50 => 'warning',
                                        $state >= 25 => 'gray',
                                        default => 'danger',
                                    })
                                    ->icon('heroicon-m-chart-bar'),

                                Infolists\Components\TextEntry::make('achievement.coin_reward')
                                    ->label('Coin Reward')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-currency-dollar'),
                            ]),
                    ]),

                // Section 4: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (UserAchievement $record): bool => !empty($record->additional_info)),

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
            function () {
                $completedCount = static::getModel()::where('status', true)->count();
                return $completedCount > 0 ? (string) $completedCount : null;
            }
        );
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['user.name', 'achievement.name'];
    }
}