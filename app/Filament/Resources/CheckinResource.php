<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckinResource\Pages;
use App\Filament\Resources\CheckinResource\RelationManagers;
use App\Models\Checkin;
use App\Models\User;
use App\Models\Place;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Grid;

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Check-ins';

    protected static ?string $modelLabel = 'Check-in';

    protected static ?string $pluralModelLabel = 'Check-ins';
    
    protected static ?string $navigationGroup = 'Activity Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📍 Check-in Details')
                    ->description('Basic information about the check-in')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('User')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                Forms\Components\Select::make('place_id')
                                    ->label('Place')
                                    ->relationship('place', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('description')
                                            ->maxLength(500),
                                    ]),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('created_at')
                                    ->label('Check-in Time')
                                    ->default(now())
                                    ->required()
                                    ->seconds(false),
                                Forms\Components\Toggle::make('status')
                                    ->label('Completed')
                                    ->onColor('success')
                                    ->offColor('warning')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-clock')
                                    ->helperText('Toggle to mark as completed')
                                    ->default(true),
                            ]),
                    ]),
                
                Forms\Components\Section::make('🗺️ Location Information')
                    ->description('GPS coordinates and location details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->placeholder('e.g., -6.2088')
                                    ->helperText('GPS latitude coordinate (required for location-based features)')
                                    ->required(),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->placeholder('e.g., 106.8456')
                                    ->helperText('GPS longitude coordinate (required for location-based features)')
                                    ->required(),
                            ]),
                    ]),
                
                Forms\Components\Section::make('📸 Media & Additional Details')
                    ->description('Check-in image and extra information')
                    ->schema([
                        Forms\Components\FileUpload::make('image_url')
                            ->label('Check-in Image')
                            ->image()
                            ->maxSize(5120)
                            ->helperText('Upload an image (max 5MB)')
                            ->disk('public')
                            ->directory('checkins')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Additional Information')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add new field')
                            ->helperText('Add any additional information as key-value pairs (e.g., device: mobile, purpose: leisure)')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-map-pin')
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Check-in Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y g:i:s A')),
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('place')
                    ->relationship('place', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status')
                    ->placeholder('All Check-ins')
                    ->trueLabel('Completed')
                    ->falseLabel('Pending'),
                Filter::make('has_image')
                    ->label('Has Image')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('image_url')
                        ->where('image_url', '!=', '')),
                Filter::make('has_location')
                    ->label('Has GPS Location')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('latitude')
                        ->whereNotNull('longitude')),
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)),
                Filter::make('pending')
                    ->label('Pending Check-ins')
                    ->query(fn (Builder $query): Builder => $query->where('status', false)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Checkin $record) => $record->update(['status' => true]))
                    ->visible(fn (Checkin $record) => $record->status === false),
                Tables\Actions\Action::make('mark_pending')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Checkin $record) => $record->update(['status' => false]))
                    ->visible(fn (Checkin $record) => $record->status === true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('complete')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => true])),
                    Tables\Actions\BulkAction::make('mark_pending')
                        ->label('Mark as Pending')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No check-ins found')
            ->emptyStateDescription('Once users start checking in to places, they will appear here.')
            ->emptyStateIcon('heroicon-o-map-pin');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi Check-in
                Section::make('📍 Informasi Check-in')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('User')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('place.name')
                                    ->label('Tempat')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-map-pin'),

                                TextEntry::make('created_at')
                                    ->label('Waktu Check-in')
                                    ->dateTime('d M Y, H:i')
                                    ->icon('heroicon-m-clock'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Selesai' : 'Pending'),
                            ]),
                    ]),

                // Section 2: Lokasi GPS
                Section::make('🗺️ Lokasi GPS')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('latitude')
                                    ->label('Latitude')
                                    ->numeric(decimalPlaces: 6)
                                    ->placeholder('Tidak tersedia'),

                                TextEntry::make('longitude')
                                    ->label('Longitude')
                                    ->numeric(decimalPlaces: 6)
                                    ->placeholder('Tidak tersedia'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->latitude && $record->longitude),

                // Section 3: Gambar Check-in
                Section::make('📸 Gambar Check-in')
                    ->schema([
                        ImageEntry::make('image_url')
                            ->label('Check-in Images')
                            ->size(200)
                            ->square()
                            ->columnSpanFull()
                            ->placeholder('Gambar tidak tersedia'),
                    ]),

                // Section 4: Informasi Tambahan
                Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                        KeyValueEntry::make('additional_info')
                            ->label('Detail Tambahan')
                            ->visible(fn ($state) => !empty($state) && (is_array($state) || is_object($state))),
                    ])
                    ->visible(fn ($record) => !empty($record->additional_info)),

                // Section 5: Timestamp
                Section::make('⏰ Riwayat')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d M Y, H:i:s')
                                    ->icon('heroicon-m-plus-circle'),

                                TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime('d M Y, H:i:s')
                                    ->icon('heroicon-m-pencil-square'),
                            ]),
                    ])
                    ->collapsible(),
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
            'index' => Pages\ListCheckins::route('/'),
            'create' => Pages\CreateCheckin::route('/create'),
            'view' => Pages\ViewCheckin::route('/{record}'),
            'edit' => Pages\EditCheckin::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', false)->count() ?: null;
    }
    
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'place']);
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'place.name', 'status'];
    }
}
