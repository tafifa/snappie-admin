<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RewardResource\Pages;
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

class RewardResource extends Resource
{
    protected static ?string $model = Reward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Rewards';

    protected static ?string $modelLabel = 'Reward';

    protected static ?string $pluralModelLabel = 'Rewards';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi Dasar
                Forms\Components\Section::make('🎁 Informasi Reward')
                    ->description('Informasi dasar tentang reward')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Reward')
                                    ->placeholder('Contoh: Voucher Diskon 50%')
                                    ->maxLength(255)
                                    ->required()
                                    ->helperText('Nama reward yang akan ditampilkan kepada pengguna')
                                    ->suffixIcon('heroicon-m-gift'),

                                Forms\Components\Toggle::make('status')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Reward aktif dapat diklaim pengguna')
                                    ->onColor('success')
                                    ->offColor('danger'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi reward dan cara menggunakannya...')
                            ->maxLength(1000)
                            ->rows(3)
                            ->helperText('Deskripsi detail tentang reward ini'),
                    ])->collapsible(),

                // Section 2: Media & Visual
                Forms\Components\Section::make('🖼️ Media & Visual')
                    ->description('Gambar dan tampilan visual reward')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('URL Gambar')
                            ->placeholder('https://example.com/reward-image.png')
                            ->url()
                            ->maxLength(500)
                            ->helperText('URL gambar reward (opsional)')
                            ->suffixIcon('heroicon-m-photo'),
                    ])->collapsible()->collapsed(),

                // Section 3: Requirement & Stock
                Forms\Components\Section::make('💰 Requirement & Stock')
                    ->description('Syarat dan ketersediaan reward')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('coin_requirement')
                                    ->label('Coin Requirement')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100000)
                                    ->placeholder('1000')
                                    ->required()
                                    ->helperText('Jumlah coin yang dibutuhkan (1-100000)')
                                    ->suffixIcon('heroicon-m-currency-dollar'),

                                Forms\Components\TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('100')
                                    ->required()
                                    ->helperText('Jumlah stock reward yang tersedia')
                                    ->suffixIcon('heroicon-m-cube'),
                            ]),
                    ])->collapsible(),

                // Section 4: Periode Reward
                Forms\Components\Section::make('📅 Periode Reward')
                    ->description('Waktu mulai dan berakhir reward')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Tanggal Mulai')
                                    ->helperText('Kapan reward mulai tersedia (opsional)')
                                    ->suffixIcon('heroicon-m-calendar'),

                                Forms\Components\DateTimePicker::make('ended_at')
                                    ->label('Tanggal Berakhir')
                                    ->helperText('Kapan reward berakhir (opsional)')
                                    ->suffixIcon('heroicon-m-calendar')
                                    ->after('started_at'),
                            ]),
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
                    ->label('Nama Reward')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-gift')
                    ->limit(30),

                Tables\Columns\TextColumn::make('coin_requirement')
                    ->label('Coin Required')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-currency-dollar'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(function (Reward $record): string {
                        if ($record->stock == 0) return 'danger';
                        if ($record->stock <= 10) return 'warning';
                        return 'success';
                    })
                    ->icon('heroicon-m-cube'),

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
                    ->color(function (Reward $record): string {
                        if (!$record->ended_at) return 'gray';
                        return $record->ended_at->isPast() ? 'danger' : 'success';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Diklaim')
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
                    ->placeholder('Semua Reward')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),

                Tables\Filters\Filter::make('in_stock')
                    ->label('Tersedia')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '>', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Habis')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '=', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock Rendah')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('stock', [1, 10]))
                    ->toggle(),

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

                Tables\Filters\Filter::make('expensive')
                    ->label('High Cost')
                    ->query(fn (Builder $query): Builder => $query->where('coin_requirement', '>=', 5000))
                    ->toggle(),

                Tables\Filters\Filter::make('popular')
                    ->label('Popular Reward')
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
                    ->action(fn (Reward $record) => $record->update(['status' => true]))
                    ->requiresConfirmation()
                    ->visible(fn (Reward $record): bool => !$record->status),

                Tables\Actions\Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Reward $record) => $record->update(['status' => false]))
                    ->requiresConfirmation()
                    ->visible(fn (Reward $record): bool => $record->status),

                Tables\Actions\Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-o-cube')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('additional_stock')
                            ->label('Tambah Stock')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->action(function (Reward $record, array $data) {
                        $record->update(['stock' => $record->stock + $data['additional_stock']]);
                    })
                    ->visible(fn (Reward $record): bool => $record->stock <= 10),
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
            ->emptyStateHeading('Belum ada reward')
            ->emptyStateDescription('Mulai dengan membuat reward pertama untuk sistem gamification.')
            ->emptyStateIcon('heroicon-o-gift');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi Reward
                Infolists\Components\Section::make('🎁 Informasi Reward')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\ImageEntry::make('image_url')
                                    ->label('Gambar')
                                    ->circular()
                                    ->defaultImageUrl('/images/default-reward.png')
                                    ->size(80),

                                Infolists\Components\Grid::make(1)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Nama Reward')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg')
                                            ->icon('heroicon-m-gift'),

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

                // Section 2: Requirement & Stock
                Infolists\Components\Section::make('💰 Requirement & Stock')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('coin_requirement')
                                    ->label('Coin Required')
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-currency-dollar'),

                                Infolists\Components\TextEntry::make('stock')
                                    ->label('Stock Tersedia')
                                    ->badge()
                                    ->color(function (Reward $record): string {
                                        if ($record->stock == 0) return 'danger';
                                        if ($record->stock <= 10) return 'warning';
                                        return 'success';
                                    })
                                    ->icon('heroicon-m-cube'),
                            ]),
                    ]),

                // Section 3: Periode Reward
                Infolists\Components\Section::make('📅 Periode Reward')
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

                // Section 4: Statistik Klaim
                Infolists\Components\Section::make('📊 Statistik Klaim')
                    ->schema([
                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Total Diklaim')
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
                    ->visible(fn (Reward $record): bool => !empty($record->additional_info)),

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
            'index' => Pages\ListRewards::route('/'),
            'create' => Pages\CreateReward::route('/create'),
            'view' => Pages\ViewReward::route('/{record}'),
            'edit' => Pages\EditReward::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'navigation_badge_rewards',
            now()->addMinutes(10),
            function () {
                $activeCount = static::getModel()::where('status', true)->count();
                return $activeCount > 0 ? (string) $activeCount : null;
            }
        );
    }

    public static function getGlobalSearchAttributes(): array
    {
        return ['name', 'description'];
    }
}