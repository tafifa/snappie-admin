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

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi Dasar
                Forms\Components\Section::make('🏆 Informasi Achievement')
                    ->description('Informasi dasar tentang achievement')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Achievement')
                                    ->placeholder('Contoh: First Check-in')
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText('Nama achievement yang akan ditampilkan kepada pengguna')
                                    ->suffixIcon('heroicon-m-trophy'),

                                Forms\Components\Toggle::make('status')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Achievement aktif dapat diperoleh pengguna')
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi achievement dan cara mendapatkannya...')
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Deskripsi detail tentang achievement ini'),
                    ])->collapsible(),

                // Section 2: Media & Visual
                Forms\Components\Section::make('🖼️ Media & Visual')
                    ->description('Gambar dan tampilan visual achievement')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('URL Gambar')
                            ->placeholder('https://example.com/achievement-image.png')
                            ->url()
                            ->maxLength(500)
                            ->helperText('URL gambar achievement (opsional)')
                            ->suffixIcon('heroicon-m-photo'),
                    ])->collapsible()->collapsed(),

                // Section 3: Reward System
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
                                    ->placeholder('100')
                                    ->default(0)
                                    ->helperText('Jumlah coin yang didapat (0-10000)')
                                    ->suffixIcon('heroicon-m-currency-dollar'),

                                Forms\Components\Placeholder::make('exp_info')
                                    ->label('Info EXP')
                                    ->content('EXP reward dapat dikonfigurasi melalui sistem gamification')
                                    ->helperText('Lihat GamificationService untuk pengaturan EXP'),
                            ]),
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
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Achievement')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-trophy')
                    ->limit(30),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Achievement $record): ?string {
                        return $record->description;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('coin_reward')
                    ->label('Coin Reward')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-currency-dollar')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Total Pengguna')
                    ->counts('users')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-users')
                    ->sortable()
                    ->toggleable(),
                    
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
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua Achievement')
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
                // Section 1: Informasi Achievement
                Infolists\Components\Section::make('🏆 Informasi Achievement')
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
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Nama Achievement')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg')
                                            ->icon('heroicon-m-trophy'),

                                        Infolists\Components\IconEntry::make('status')
                                            ->label('Status')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-x-circle')
                                            ->trueColor('success')
                                            ->falseColor('danger'),
                                    ]),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),

                // Section 2: Sistem Reward
                Infolists\Components\Section::make('💰 Sistem Reward')
                    ->schema([
                        Infolists\Components\TextEntry::make('coin_reward')
                            ->label('Coin Reward')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-m-currency-dollar'),
                    ]),

                // Section 3: Statistik Pengguna
                Infolists\Components\Section::make('📊 Statistik Pengguna')
                    ->schema([
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Total Pengguna yang Meraih')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-users'),
                    ]),

                // Section 4: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Achievement $record): bool => !empty($record->additional_info)),

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

    public static function getNavigationBadge(): ?string
    {
        $activeCount = static::getModel()::where('status', true)->count();
        return $activeCount > 0 ? (string) $activeCount : null;
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['name', 'description'];
    }
}