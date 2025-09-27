<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRewardResource\Pages;
use App\Models\UserReward;
use App\Models\User;
use App\Models\Reward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class UserRewardResource extends Resource
{
    protected static ?string $model = UserReward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'User Rewards';

    protected static ?string $modelLabel = 'User Reward';

    protected static ?string $pluralModelLabel = 'User Rewards';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Relasi User & Reward
                Forms\Components\Section::make('👤 User & Reward')
                    ->description('Hubungan antara pengguna dan reward')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pengguna')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih pengguna yang mengklaim reward')
                                    ->suffixIcon('heroicon-m-user'),

                                Forms\Components\Select::make('reward_id')
                                    ->label('Reward')
                                    ->relationship('reward', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih reward yang diklaim')
                                    ->suffixIcon('heroicon-m-gift'),
                            ]),
                    ])->collapsible(),

                // Section 2: Status & Quantity
                Forms\Components\Section::make('📦 Status & Quantity')
                    ->description('Status klaim dan jumlah reward')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label('Status Diklaim')
                                    ->default(false)
                                    ->helperText('Apakah reward sudah diklaim?')
                                    ->onColor('success')
                                    ->offColor('warning'),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->helperText('Jumlah reward yang diklaim')
                                    ->suffixIcon('heroicon-m-hashtag'),
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

                Tables\Columns\TextColumn::make('reward.name')
                    ->label('Reward')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-gift')
                    ->limit(30),

                Tables\Columns\TextColumn::make('reward.coin_requirement')
                    ->label('Coin Requirement')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-currency-dollar')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reward.stock')
                    ->label('Stock Tersisa')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state <= 0 => 'danger',
                        default => 'success',
                    })
                    ->icon('heroicon-m-cube')
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
                    ->label('Diklaim')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->tooltip(function (UserReward $record): string {
                        return 'Reward diklaim pada: ' . $record->created_at->format('d M Y H:i');
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
                    ->trueLabel('Diklaim')
                    ->falseLabel('Belum Diklaim'),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('reward_id')
                    ->label('Reward')
                    ->relationship('reward', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('high_quantity')
                    ->label('Quantity Tinggi (≥5)')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('quantity', '>=', 5)
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('expensive_reward')
                    ->label('Reward Mahal (≥5000 coin)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('reward', fn ($q) => $q->where('coin_requirement', '>=', 5000))
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('high_total_cost')
                    ->label('Total Cost Tinggi (≥10000)')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereRaw('quantity * (SELECT coin_requirement FROM rewards WHERE rewards.id = user_rewards.reward_id) >= 10000')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('claimed_today')
                    ->label('Diklaim Hari Ini')
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
                    ->action(fn (UserReward $record) => $record->update(['status' => true, 'progress' => 100]))
                    ->requiresConfirmation()
                    ->visible(fn (UserReward $record): bool => !$record->status),
                
                Tables\Actions\Action::make('mark_pending')
                    ->label('Tandai Pending')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->action(fn (UserReward $record) => $record->update(['status' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (UserReward $record): bool => $record->status),

                // Tables\Actions\Action::make('claim')
                //     ->label('Klaim')
                //     ->icon('heroicon-o-check-circle')
                //     ->color('success')
                //     ->action(fn (UserReward $record) => $record->update(['status' => true]))
                //     ->requiresConfirmation()
                //     ->visible(fn (UserReward $record): bool => !$record->status),

                // Tables\Actions\Action::make('unclaim')
                //     ->label('Batalkan Klaim')
                //     ->icon('heroicon-o-x-circle')
                //     ->color('warning')
                //     ->action(fn (UserReward $record) => $record->update(['status' => false]))
                //     ->requiresConfirmation()
                //     ->visible(fn (UserReward $record): bool => $record->status),

                // Tables\Actions\Action::make('update_quantity')
                //     ->label('Update Quantity')
                //     ->icon('heroicon-o-hashtag')
                //     ->color('info')
                //     ->form([
                //         Forms\Components\TextInput::make('quantity')
                //             ->label('Jumlah')
                //             ->numeric()
                //             ->minValue(1)
                //             ->required(),
                //     ])
                //     ->action(function (UserReward $record, array $data): void {
                //         $record->update(['quantity' => $data['quantity']]);
                //     }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('claim')
                        ->label('Klaim Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => true]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('unclaim')
                        ->label('Batalkan Klaim Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada user reward')
            ->emptyStateDescription('User reward akan muncul ketika pengguna mengklaim reward.')
            ->emptyStateIcon('heroicon-o-gift');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi User & Reward
                Infolists\Components\Section::make('👤 Informasi User & Reward')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Pengguna')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                Infolists\Components\TextEntry::make('reward.name')
                                    ->label('Reward')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-gift'),
                            ]),

                        Infolists\Components\TextEntry::make('reward.description')
                            ->label('Deskripsi Reward')
                            ->columnSpanFull(),
                    ]),

                // Section 2: Status & Quantity
                Infolists\Components\Section::make('📦 Status & Quantity')
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

                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('Jumlah')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-hashtag'),

                                Infolists\Components\TextEntry::make('total_cost')
                                    ->label('Total Cost')
                                    ->getStateUsing(fn (UserReward $record): int => $record->quantity * $record->reward->coin_requirement)
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-m-calculator'),
                            ]),
                    ]),

                // Section 3: Informasi Reward
                Infolists\Components\Section::make('🎁 Informasi Reward')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('reward.coin_requirement')
                                    ->label('Coin Requirement')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-currency-dollar'),

                                Infolists\Components\TextEntry::make('reward.stock')
                                    ->label('Stock Tersisa')
                                    ->badge()
                                    ->color(fn (?int $state): string => match (true) {
                                        $state === null => 'gray',
                                        $state <= 0 => 'danger',
                                        $state <= 10 => 'warning',
                                        $state <= 50 => 'info',
                                        default => 'success',
                                    })
                                    ->icon('heroicon-m-cube'),

                                Infolists\Components\IconEntry::make('reward.status')
                                    ->label('Status Reward')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),
                    ]),

                // Section 4: Periode Reward
                Infolists\Components\Section::make('📅 Periode Reward')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('reward.started_at')
                                    ->label('Dimulai')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-m-play')
                                    ->placeholder('Tidak ditentukan'),

                                Infolists\Components\TextEntry::make('reward.ended_at')
                                    ->label('Berakhir')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-m-stop')
                                    ->placeholder('Tidak ditentukan'),
                            ]),
                    ]),

                // Section 5: Statistik User
                Infolists\Components\Section::make('📊 Statistik User')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.total_reward')
                                    ->label('Total Reward')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-gift'),

                                Infolists\Components\TextEntry::make('user.total_coin')
                                    ->label('Total Coin')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-currency-dollar'),

                                Infolists\Components\TextEntry::make('user.level')
                                    ->label('Level')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-trophy'),
                            ]),
                    ]),

                // Section 6: Informasi Tambahan
                Infolists\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('additional_info')
                            ->label('Data Tambahan')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (UserReward $record): bool => !empty($record->additional_info)),

                // Section 7: Riwayat
                Infolists\Components\Section::make('📅 Riwayat')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Diklaim')
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
            'index' => Pages\ListUserRewards::route('/'),
            'create' => Pages\CreateUserReward::route('/create'),
            'view' => Pages\ViewUserReward::route('/{record}'),
            'edit' => Pages\EditUserReward::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $claimedCount = static::getModel()::where('status', true)->count();
        return $claimedCount > 0 ? (string) $claimedCount : null;
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['user.name', 'reward.name'];
    }
}