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

class CheckinResource extends Resource
{
    protected static ?string $model = Checkin::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationGroup = 'Activity';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Check-in Details')
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
                                        Forms\Components\TextInput::make('address')
                                            ->maxLength(255),
                                    ]),
                            ]),
                        Forms\Components\DateTimePicker::make('time')
                            ->label('Check-in Time')
                            ->default(now())
                            ->required()
                            ->seconds(false),
                        Forms\Components\Select::make('check_in_status')
                            ->label('Check-in Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ]),
                
                Forms\Components\Section::make('Location Information')
                    ->description('GPS coordinates and location details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->placeholder('e.g., 40.7128')
                                    ->helperText('GPS latitude coordinate'),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->placeholder('e.g., -74.0060')
                                    ->helperText('GPS longitude coordinate'),
                            ]),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Mission Details')
                    ->description('Information about any associated mission')
                    ->schema([
                        Forms\Components\FileUpload::make('mission_image_url')
                            ->label('Mission Image')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxSize(5120)
                            ->helperText('Upload an image related to the mission (max 5MB)'),
                        Forms\Components\Select::make('mission_status')
                            ->label('Mission Status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                        Forms\Components\DateTimePicker::make('mission_completed_at')
                            ->label('Mission Completed At')
                            ->seconds(false),
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
                    ->weight(FontWeight::Medium),
                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                Tables\Columns\TextColumn::make('time')
                    ->label('Check-in Time')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->time->format('F j, Y g:i:s A')),
                // Tables\Columns\BadgeColumn::make('check_in_status')
                //     ->label('Status')
                //     ->colors([
                //         'warning' => 'pending',
                //         'success' => 'completed',
                //         'danger' => ['cancelled', 'failed'],
                //     ])
                //     ->icons([
                //         'heroicon-o-clock' => 'pending',
                //         'heroicon-o-check-circle' => 'completed',
                //         'heroicon-o-x-circle' => ['cancelled', 'failed'],
                //     ])
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('location')
                    // ->label('GPS Location')
                    // ->formatStateUsing(function ($state) {
                    //     if (!$state || !is_array($state)) {
                    //         return 'Not available';
                    //     }
                    //     $lat = $state['latitude'] ?? $state[0] ?? null;
                    //     $lng = $state['longitude'] ?? $state[1] ?? null;
                    //     if ($lat && $lng) {
                    //         return sprintf('%.6f, %.6f', $lat, $lng);
                    //     }
                    //     return 'Invalid coordinates';
                    // })
                    // ->tooltip(function ($state) {
                    //     if (!$state || !is_array($state)) {
                    //         return null;
                    //     }
                    //     $lat = $state['latitude'] ?? $state[0] ?? null;
                    //     $lng = $state['longitude'] ?? $state[1] ?? null;
                    //     if ($lat && $lng) {
                    //         return "Latitude: {$lat}, Longitude: {$lng}";
                    //     }
                    //     return null;
                    // })
                    // ->toggleable(),
                Tables\Columns\BadgeColumn::make('mission_status')
                    ->label('Mission')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => ['failed', 'cancelled'],
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-play' => 'in_progress',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => ['failed', 'cancelled'],
                    ])
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('mission_image_url')
                    ->label('Mission Image')
                    ->circular()
                    ->size(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mission_completed_at')
                    ->label('Mission Completed')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->placeholder('Not completed')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
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
                SelectFilter::make('check_in_status')
                    ->label('Check-in Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),
                SelectFilter::make('mission_status')
                    ->label('Mission Status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                Filter::make('has_mission_image')
                    ->label('Has Mission Image')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('mission_image_url')),
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('time', today())),
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('time', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
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
            ->defaultSort('time', 'desc')
            ->emptyStateHeading('No check-ins found')
            ->emptyStateDescription('Once users start checking in to places, they will appear here.')
            ->emptyStateIcon('heroicon-o-map-pin');
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
        return static::getModel()::count();
    }
    
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'place']);
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'place.name', 'check_in_status', 'mission_status'];
    }
}
