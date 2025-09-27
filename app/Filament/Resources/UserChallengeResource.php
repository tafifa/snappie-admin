<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserChallengeResource\Pages;
use App\Models\UserChallenge;
use App\Models\User;
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

class UserChallengeResource extends Resource
{
    protected static ?string $model = UserChallenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationLabel = 'User Challenges';

    protected static ?string $modelLabel = 'User Challenge';

    protected static ?string $pluralModelLabel = 'User Challenges';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Relasi User & Challenge
                Forms\Components\Section::make('👤 User & Challenge')
                    ->description('Hubungan antara pengguna dan challenge')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pengguna')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih pengguna yang mengikuti challenge')
                                    ->suffixIcon('heroicon-m-user'),

                                Forms\Components\Select::make('challenge_id')
                                    ->label('Challenge')
                                    ->relationship('challenge', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih challenge yang diikuti')
                                    ->suffixIcon('heroicon-m-fire'),
                            ]),
                    ])->collapsible(),

                // Section 2: Status & Progress
                Forms\Components\Section::make('📊 Status & Progress')
                    ->description('Status penyelesaian dan progress challenge')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label('Status Selesai')
                                    ->default(false)
                                    ->helperText('Apakah challenge sudah diselesaikan?')
                                    ->onColor('success')
                                    ->offColor('warning'),

                                Forms\Components\TextInput::make('progress')
                                    ->label('Progress (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0)
                                    ->helperText('Progress penyelesaian challenge (0-100%)')
                                    ->suffixIcon('heroicon-m-chart-bar'),
                            ]),
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
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-user')
                    ->limit(25),

                Tables\Columns\TextColumn::make('challenge.name')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-fire')
                    ->limit(30),

                Tables\Columns\TextColumn::make('challenge.challenge_type')
                    ->label('Tipe')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily' => 'info',
                        'weekly' => 'warning',
                        'monthly' => 'success',
                        'special' => 'danger',
                        default => 'gray',
                    })
                    ->icon('heroicon-m-tag'),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->numeric()
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'gray',
                        default => 'danger',
                    })
                    ->icon('heroicon-m-chart-bar'),

                Tables\Columns\TextColumn::make('challenge.exp_reward')
                    ->label('EXP Reward')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-star')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dimulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->tooltip(function (UserChallenge $record): string {
                        return 'Challenge dimulai pada: ' . $record->created_at->format('d M Y H:i');
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

                Tables\Filters\SelectFilter::make('challenge_id')
                    ->label('Challenge')
                    ->relationship('challenge', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('challenge.challenge_type')
                    ->label('Tipe Challenge')
                    ->relationship('challenge', 'challenge_type')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'special' => 'Special',
                    ]),

                Tables\Filters\Filter::make('high_progress')
                    ->label('Progress Tinggi (≥75%)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('progress', '>=', 75)
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('low_progress')
                    ->label('Progress Rendah (<25%)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('progress', '<', 25)
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('high_reward')
                    ->label('High Reward Challenge')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('challenge', fn ($q) => $q->where('exp_reward', '>=', 1000))
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (UserChallenge $record) => $record->update(['status' => true, 'progress' => 100]))
                    ->requiresConfirmation()
                    ->visible(fn (UserChallenge $record): bool => !$record->status),
                
                Tables\Actions\Action::make('mark_pending')
                    ->label('Tandai Pending')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->action(fn (UserChallenge $record) => $record->update(['status' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (UserChallenge $record): bool => $record->status),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('complete')
                        ->label('Selesaikan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => true, 'progress' => 100]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('reset_progress')
                        ->label('Reset Progress Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => false, 'progress' => 0]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada user challenge')
            ->emptyStateDescription('User challenge akan muncul ketika pengguna mengikuti challenge.')
            ->emptyStateIcon('heroicon-o-fire');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi User & Challenge
                Infolists\Components\Section::make('👤 Informasi User & Challenge')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Pengguna')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                Infolists\Components\TextEntry::make('challenge.name')
                                    ->label('Challenge')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-fire'),
                            ]),

                        Infolists\Components\TextEntry::make('challenge.description')
                            ->label('Deskripsi Challenge')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('challenge.challenge_type')
                            ->label('Tipe Challenge')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'daily' => 'info',
                                'weekly' => 'warning',
                                'monthly' => 'success',
                                'special' => 'danger',
                                default => 'gray',
                            }),
                    ]),

                // Section 2: Status & Progress
                Infolists\Components\Section::make('📊 Status & Progress')
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

                                Infolists\Components\TextEntry::make('challenge.exp_reward')
                                    ->label('EXP Reward')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-star'),
                            ]),
                    ]),

                // Section 3: Periode Challenge
                Infolists\Components\Section::make('📅 Periode Challenge')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('challenge.started_at')
                                    ->label('Dimulai')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-m-play')
                                    ->placeholder('Tidak ditentukan'),

                                Infolists\Components\TextEntry::make('challenge.ended_at')
                                    ->label('Berakhir')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-m-stop')
                                    ->placeholder('Tidak ditentukan'),
                            ]),
                    ]),

                // Section 4: Statistik User
                Infolists\Components\Section::make('📊 Statistik User')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.total_challenge')
                                    ->label('Total Challenge')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-fire'),

                                Infolists\Components\TextEntry::make('user.total_exp')
                                    ->label('Total EXP')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-star'),

                                Infolists\Components\TextEntry::make('user.level')
                                    ->label('Level')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-trophy'),
                            ]),
                    ]),

                // Section 5: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (UserChallenge $record): bool => !empty($record->additional_info)),

                // Section 6: Riwayat
                Infolists\Components\Section::make('📅 Riwayat')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Dimulai')
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
            'index' => Pages\ListUserChallenges::route('/'),
            'create' => Pages\CreateUserChallenge::route('/create'),
            'view' => Pages\ViewUserChallenge::route('/{record}'),
            'edit' => Pages\EditUserChallenge::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $completedCount = static::getModel()::where('status', true)->count();
        return $completedCount > 0 ? (string) $completedCount : null;
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['user.name', 'challenge.name', 'challenge.challenge_type'];
    }
}