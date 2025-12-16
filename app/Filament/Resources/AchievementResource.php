<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
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

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Achievements';

    protected static ?string $modelLabel = 'Achievement';

    protected static ?string $pluralModelLabel = 'Achievements';

    protected static ?string $navigationGroup = 'Gamification Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi Dasar
                Forms\Components\Section::make('🏆 Informasi Dasar')
                    ->description('Informasi dasar tentang achievement/challenge')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('Kode Unik')
                                    ->placeholder('ach_first_checkin')
                                    ->maxLength(50)
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Kode unik untuk achievement')
                                    ->suffixIcon('heroicon-m-hashtag'),

                                Forms\Components\Select::make('type')
                                    ->label('Tipe')
                                    ->options([
                                        'achievement' => 'Achievement',
                                        'challenge' => 'Challenge',
                                    ])
                                    ->required()
                                    ->default('achievement')
                                    ->helperText('Achievement: one-time, Challenge: repeatable')
                                    ->live(),

                                Forms\Components\Toggle::make('status')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Aktif dapat diperoleh pengguna')
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama')
                            ->placeholder('Contoh: First Check-in')
                            ->maxLength(255)
                            ->required()
                            ->helperText('Nama yang akan ditampilkan')
                            ->suffixIcon('heroicon-m-trophy'),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi dan cara mendapatkannya...')
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Deskripsi detail'),
                    ])->collapsible(),

                // Section 2: Kriteria
                Forms\Components\Section::make('🎯 Kriteria Achievement')
                    ->description('Kriteria yang harus dipenuhi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('criteria_action')
                                    ->label('Action Type')
                                    ->placeholder('checkin, review, rating_5_star')
                                    ->maxLength(50)
                                    ->required()
                                    ->helperText('Tipe aksi yang harus dilakukan')
                                    ->suffixIcon('heroicon-m-bolt'),

                                Forms\Components\TextInput::make('criteria_target')
                                    ->label('Target')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->helperText('Jumlah target yang harus dicapai')
                                    ->suffixIcon('heroicon-m-flag'),
                            ]),
                    ])->collapsible(),

                // Section 3: Reset Schedule (untuk Challenge)
                Forms\Components\Section::make('🔄 Reset Schedule')
                    ->description('Jadwal reset untuk challenge')
                    ->schema([
                        Forms\Components\Select::make('reset_schedule')
                            ->label('Reset Schedule')
                            ->options([
                                'none' => 'None (One-time)',
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                            ])
                            ->required()
                            ->default('none')
                            ->helperText('none: one-time, daily: reset setiap hari, weekly: reset setiap minggu'),
                    ])->collapsible(),

                // Section 4: Media & Visual
                Forms\Components\Section::make('🖼️ Media & Visual')
                    ->description('Gambar dan tampilan visual')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('URL Gambar')
                            ->placeholder('https://example.com/image.png')
                            ->url()
                            ->maxLength(500)
                            ->helperText('URL gambar (opsional)')
                            ->suffixIcon('heroicon-m-photo'),

                        Forms\Components\TextInput::make('display_order')
                            ->label('Urutan Tampilan')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Urutan tampilan di list')
                            ->suffixIcon('heroicon-m-bars-3'),
                    ])->collapsible()->collapsed(),

                // Section 5: Reward System
                Forms\Components\Section::make('💰 Sistem Reward')
                    ->description('Reward yang didapat pengguna')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('coin_reward')
                                    ->label('Reward Coin')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10000)
                                    ->default(0)
                                    ->helperText('Jumlah coin (0-10000)')
                                    ->suffixIcon('heroicon-m-currency-dollar'),

                                Forms\Components\TextInput::make('reward_xp')
                                    ->label('Reward XP')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10000)
                                    ->default(0)
                                    ->helperText('Jumlah XP (0-10000)')
                                    ->suffixIcon('heroicon-m-star'),
                            ]),
                    ])->collapsible(),

                // Section 6: Informasi Tambahan
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
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-hashtag')
                    ->limit(20),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-trophy')
                    ->limit(30),

                Tables\Columns\TextColumn::make('type')
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

                Tables\Columns\TextColumn::make('reset_schedule')
                    ->label('Reset')
                    ->sortable()
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

                Tables\Columns\TextColumn::make('criteria_action')
                    ->label('Action')
                    ->searchable()
                    ->badge()
                    ->color('indigo')
                    ->icon('heroicon-m-bolt')
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('criteria_target')
                    ->label('Target')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('purple')
                    ->icon('heroicon-m-flag'),

                Tables\Columns\TextColumn::make('coin_reward')
                    ->label('Coin')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-currency-dollar')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reward_xp')
                    ->label('XP')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-star')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'achievement' => 'Achievement',
                        'challenge' => 'Challenge',
                    ])
                    ->placeholder('Semua Tipe'),

                Tables\Filters\SelectFilter::make('reset_schedule')
                    ->label('Reset Schedule')
                    ->options([
                        'none' => 'One-time',
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                    ])
                    ->placeholder('Semua Reset'),

                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\Filter::make('high_reward')
                    ->label('High Reward')
                    ->query(fn (Builder $query): Builder => $query->where('coin_reward', '>=', 500))
                    ->toggle(),

                Tables\Filters\Filter::make('popular')
                    ->label('Popular Achievement')
                    ->query(fn (Builder $query): Builder => $query->withCount('users')->having('users_count', '>=', 10))
                    ->toggle(),

                Tables\Filters\Filter::make('today')
                    ->label('Dibuat Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),

                Tables\Filters\Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Achievement $record) => $record->update(['status' => true]))
                    ->requiresConfirmation()
                    ->visible(fn (Achievement $record): bool => !$record->status),

                Tables\Actions\Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Achievement $record) => $record->update(['status' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (Achievement $record): bool => $record->status),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['status' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada achievement')
            ->emptyStateDescription('Mulai dengan membuat achievement pertama untuk sistem gamification.')
            ->emptyStateIcon('heroicon-o-trophy');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi Dasar
                Infolists\Components\Section::make('🏆 Informasi Dasar')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\ImageEntry::make('image_url')
                                    ->label('Gambar')
                                    ->circular()
                                    ->defaultImageUrl('/images/default-achievement.png')
                                    ->size(80),

                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('code')
                                            ->label('Kode')
                                            ->badge()
                                            ->color('gray')
                                            ->icon('heroicon-m-hashtag'),

                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Nama')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg')
                                            ->icon('heroicon-m-trophy'),

                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('type')
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

                                                Infolists\Components\TextEntry::make('reset_schedule')
                                                    ->label('Reset')
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

                                                Infolists\Components\IconEntry::make('status')
                                                    ->label('Status')
                                                    ->boolean()
                                                    ->trueIcon('heroicon-o-check-circle')
                                                    ->falseIcon('heroicon-o-x-circle')
                                                    ->trueColor('success')
                                                    ->falseColor('danger'),
                                            ]),
                                    ]),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])->collapsible(),

                // Section 2: Kriteria
                Infolists\Components\Section::make('🎯 Kriteria Achievement')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('criteria_action')
                                    ->label('Action Type')
                                    ->badge()
                                    ->color('indigo')
                                    ->icon('heroicon-m-bolt'),

                                Infolists\Components\TextEntry::make('criteria_target')
                                    ->label('Target')
                                    ->numeric()
                                    ->badge()
                                    ->color('purple')
                                    ->icon('heroicon-m-flag'),
                            ]),
                    ])->collapsible(),

                // Section 3: Reward System
                Infolists\Components\Section::make('💰 Sistem Reward')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('coin_reward')
                                    ->label('Coin Reward')
                                    ->numeric()
                                    ->suffix(' 🪙')
                                    ->badge()
                                    ->color('warning'),

                                Infolists\Components\TextEntry::make('reward_xp')
                                    ->label('XP Reward')
                                    ->numeric()
                                    ->suffix(' ⭐')
                                    ->badge()
                                    ->color('info'),

                                Infolists\Components\TextEntry::make('display_order')
                                    ->label('Urutan Tampilan')
                                    ->numeric()
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ])->collapsible(),

                // Section 4: Statistik Pengguna
                Infolists\Components\Section::make('📊 Statistik Pengguna')
                    ->schema([
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Total Pengguna yang Meraih')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-users'),
                    ])->collapsible(),

                // Section 5: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Achievement $record): bool => !empty($record->additional_info))
                    ->collapsible(),

                // Section 5: Riwayat
                Infolists\Components\Section::make('📅 Riwayat')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dibuat')
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
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'view' => Pages\ViewAchievement::route('/{record}'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['name', 'description'];
    }
}