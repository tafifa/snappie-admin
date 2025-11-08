<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChallengeResource\Pages;
use App\Models\Challenge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationLabel = 'Challenges';

    protected static ?string $modelLabel = 'Challenge';

    protected static ?string $pluralModelLabel = 'Challenges';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi Dasar
                Forms\Components\Section::make('🔥 Informasi Challenge')
                    ->description('Informasi dasar tentang challenge')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Challenge')
                                    ->placeholder('Contoh: Weekly Explorer')
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText('Nama challenge yang akan ditampilkan kepada pengguna')
                                    ->suffixIcon('heroicon-m-fire'),

                                Forms\Components\Toggle::make('status')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Challenge aktif dapat diikuti pengguna')
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi challenge dan cara menyelesaikannya...')
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Deskripsi detail tentang challenge ini'),

                        Forms\Components\TextInput::make('challenge_type')
                            ->label('Tipe Challenge')
                            ->placeholder('Contoh: daily, weekly, monthly, special')
                            ->maxLength(100)
                            ->required()
                            ->helperText('Kategori atau tipe challenge')
                            ->suffixIcon('heroicon-m-tag'),
                    ])->collapsible(),

                // Section 2: Media & Visual
                Forms\Components\Section::make('🖼️ Media & Visual')
                    ->description('Gambar dan tampilan visual challenge')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('URL Gambar')
                            ->placeholder('https://example.com/challenge-image.png')
                            ->url()
                            ->maxLength(500)
                            ->helperText('URL gambar challenge (opsional)')
                            ->suffixIcon('heroicon-m-photo'),
                    ])->collapsible()->collapsed(),

                // Section 3: Periode Challenge
                Forms\Components\Section::make('📅 Periode Challenge')
                    ->description('Waktu mulai dan berakhir challenge')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Tanggal Mulai')
                                    ->helperText('Kapan challenge dimulai (opsional)')
                                    ->suffixIcon('heroicon-m-calendar'),

                                Forms\Components\DateTimePicker::make('ended_at')
                                    ->label('Tanggal Berakhir')
                                    ->helperText('Kapan challenge berakhir (opsional)')
                                    ->suffixIcon('heroicon-m-calendar')
                                    ->after('started_at'),
                            ]),
                    ])->collapsible(),

                // Section 4: Reward System
                Forms\Components\Section::make('⭐ Sistem Reward')
                    ->description('Reward yang didapat pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('exp_reward')
                            ->label('Reward EXP')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10000)
                            ->placeholder('500')
                            ->default(0)
                            ->helperText('Jumlah EXP yang didapat (0-10000)')
                            ->suffixIcon('heroicon-m-star'),
                    ])->collapsible(),

                // Section 5: Informasi Tambahan
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
                    ->label('Nama Challenge')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-fire')
                    ->limit(30),

                Tables\Columns\TextColumn::make('challenge_type')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-tag'),

                Tables\Columns\TextColumn::make('exp_reward')
                    ->label('EXP Reward')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-star')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Tidak ditentukan')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ended_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Tidak ditentukan')
                    ->color(function (Challenge $record): string {
                        if (!$record->ended_at) return 'gray';
                        return $record->ended_at->isPast() ? 'danger' : 'success';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Peserta')
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
                    ->placeholder('Semua Challenge')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\SelectFilter::make('challenge_type')
                    ->label('Tipe Challenge')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'special' => 'Special',
                    ]),

                Tables\Filters\Filter::make('active_period')
                    ->label('Sedang Berlangsung')
                    ->query(fn (Builder $query): Builder => 
                        $query->where(function ($q) {
                            $q->where('started_at', '<=', now())
                              ->where('ended_at', '>=', now());
                        })->orWhere(function ($q) {
                            $q->whereNull('started_at')
                              ->whereNull('ended_at');
                        })
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('high_reward')
                    ->label('High Reward')
                    ->query(fn (Builder $query): Builder => $query->where('exp_reward', '>=', 1000))
                    ->toggle(),

                Tables\Filters\Filter::make('popular')
                    ->label('Popular Challenge')
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
                    ->action(fn (Challenge $record) => $record->update(['status' => true]))
                    ->requiresConfirmation()
                    ->visible(fn (Challenge $record): bool => !$record->status),

                Tables\Actions\Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Challenge $record) => $record->update(['status' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (Challenge $record): bool => $record->status),
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
            ->emptyStateHeading('Belum ada challenge')
            ->emptyStateDescription('Mulai dengan membuat challenge pertama untuk sistem gamification.')
            ->emptyStateIcon('heroicon-o-fire');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi Challenge
                Infolists\Components\Section::make('🔥 Informasi Challenge')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\ImageEntry::make('image_url')
                                    ->label('Gambar')
                                    ->circular()
                                    ->defaultImageUrl('/images/default-challenge.png')
                                    ->size(80),

                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Nama Challenge')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg')
                                            ->icon('heroicon-m-fire'),

                                        Infolists\Components\TextEntry::make('challenge_type')
                                            ->label('Tipe Challenge')
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-m-tag'),

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

                // Section 2: Periode Challenge
                Infolists\Components\Section::make('📅 Periode Challenge')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('started_at')
                                    ->label('Tanggal Mulai')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('Tidak ditentukan')
                                    ->icon('heroicon-m-calendar'),

                                Infolists\Components\TextEntry::make('ended_at')
                                    ->label('Tanggal Berakhir')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('Tidak ditentukan')
                                    ->icon('heroicon-m-calendar'),
                            ]),
                    ]),

                // Section 3: Sistem Reward
                Infolists\Components\Section::make('⭐ Sistem Reward')
                    ->schema([
                        Infolists\Components\TextEntry::make('exp_reward')
                            ->label('EXP Reward')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-m-star'),
                    ]),

                // Section 4: Statistik Peserta
                Infolists\Components\Section::make('📊 Statistik Peserta')
                    ->schema([
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Total Peserta')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-m-users'),
                    ]),

                // Section 5: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Challenge $record): bool => !empty($record->additional_info)),

                // Section 6: Riwayat
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'view' => Pages\ViewChallenge::route('/{record}'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'navigation_badge_challenges',
            now()->addMinutes(10),
            function () {
                $activeCount = static::getModel()::where('status', true)->count();
                return $activeCount > 0 ? (string) $activeCount : null;
            }
        );
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['name', 'description', 'challenge_type'];
    }
}