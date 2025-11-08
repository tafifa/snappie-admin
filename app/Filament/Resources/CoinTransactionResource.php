<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoinTransactionResource\Pages;
use App\Models\CoinTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoinTransactionResource extends Resource
{
    protected static ?string $model = CoinTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Coin Transactions';

    protected static ?string $modelLabel = 'Coin Transaction';

    protected static ?string $pluralModelLabel = 'Coin Transactions';

    protected static ?string $navigationGroup = 'Gamification';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('💰 Transaction Details')
                    ->description('Informasi detail transaksi koin')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('User')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Pilih pengguna yang melakukan transaksi')
                                    ->suffixIcon('heroicon-m-user'),

                                Forms\Components\TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Jumlah koin (positif untuk penambahan, negatif untuk pengurangan)')
                                    ->suffixIcon('heroicon-m-currency-dollar'),
                            ]),
                    ]),

                Forms\Components\Section::make('🔗 Related Information')
                    ->description('Informasi terkait sumber transaksi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('related_to_type')
                                    ->label('Related Type')
                                    ->required()
                                    ->helperText('Tipe model yang terkait (e.g., App\\Models\\Checkin)')
                                    ->suffixIcon('heroicon-m-tag'),

                                Forms\Components\TextInput::make('related_to_id')
                                    ->label('Related ID')
                                    ->numeric()
                                    ->required()
                                    ->helperText('ID dari model yang terkait')
                                    ->suffixIcon('heroicon-m-hashtag'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user']))
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->description(fn (CoinTransaction $record): string => $record->user->email ?? '')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '') . number_format($state) . ' coins'),

                Tables\Columns\TextColumn::make('related_to_type')
                    ->label('Source Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('related_to_id')
                    ->label('Source ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Transaction Date')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn (CoinTransaction $record): string => $record->created_at->format('M d, Y H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('amount_positive')
                    ->label('Positive Transactions')
                    ->query(fn (Builder $query): Builder => $query->where('amount', '>', 0)),

                Tables\Filters\Filter::make('amount_negative')
                    ->label('Negative Transactions')
                    ->query(fn (Builder $query): Builder => $query->where('amount', '<', 0)),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->poll('30s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\Section::make('💰 Transaction Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label('Transaction ID')
                                    ->badge()
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('amount')
                                    ->label('Amount')
                                    ->badge()
                                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '') . number_format($state) . ' coins'),
                            ])->columns(2),

                        Infolists\Components\Section::make('👤 User Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('User Name')
                                    ->icon('heroicon-m-user'),
                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Email')
                                    ->icon('heroicon-m-envelope'),
                                Infolists\Components\TextEntry::make('user.total_coin')
                                    ->label('Current Total Coins')
                                    ->badge()
                                    ->color('warning')
                                    ->formatStateUsing(fn (int $state): string => number_format($state) . ' coins'),
                            ])->columns(2),

                        Infolists\Components\Section::make('🔗 Source Information')
                            ->schema([
                                Infolists\Components\TextEntry::make('related_to_type')
                                    ->label('Source Type')
                                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                                    ->badge()
                                    ->color('info'),
                                Infolists\Components\TextEntry::make('related_to_id')
                                    ->label('Source ID')
                                    ->badge()
                                    ->color('gray'),
                            ])->columns(2),

                        Infolists\Components\Section::make('📅 Timeline')
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Transaction Date')
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
            'index' => Pages\ListCoinTransactions::route('/'),
            'create' => Pages\CreateCoinTransaction::route('/create'),
            'view' => Pages\ViewCoinTransaction::route('/{record}'),
            'edit' => Pages\EditCoinTransaction::route('/{record}/edit'),
        ];
    }
}