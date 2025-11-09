<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
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
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Group as InfolistGroup;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Reviews';

    protected static ?string $modelLabel = 'Review';

    protected static ?string $pluralModelLabel = 'Reviews';
    
    protected static ?string $navigationGroup = 'Activity Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📝 Review Details')
                    ->description('Basic information about the review')
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
                                Forms\Components\Select::make('rating')
                                    ->label('Rating')
                                    ->options([
                                        1 => '⭐ 1 Star',
                                        2 => '⭐⭐ 2 Stars',
                                        3 => '⭐⭐⭐ 3 Stars',
                                        4 => '⭐⭐⭐⭐ 4 Stars',
                                        5 => '⭐⭐⭐⭐⭐ 5 Stars',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\Toggle::make('status')
                                    ->label('Approved')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->helperText('Toggle to approve/reject this review')
                                    ->default(true),
                            ]),
                    ]),
                
                Forms\Components\Section::make('📝 Review Content')
                    ->description('The actual review content and media')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Review Text')
                            ->rows(5)
                            ->maxLength(1000)
                            ->placeholder('Write the review content here...')
                            ->helperText('Maximum 1000 characters')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image_urls')
                            ->label('Review Images')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(5120)
                            ->helperText('Upload up to 5 images (max 5MB each) - will be automatically uploaded to Cloudinary')
                            ->disk('public')
                            ->directory('temp-reviews')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('🔧 Additional Information')
                    ->description('Extra data for this review (optional)')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Additional Data')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add new field')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'place']))
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

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn (string $state): string => str_repeat('⭐', (int) $state))
                    ->sortable()
                    ->alignCenter(),


                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y g:i:s A')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
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
                SelectFilter::make('rating')
                    ->options([
                        1 => '⭐ 1 Star',
                        2 => '⭐⭐ 2 Stars',
                        3 => '⭐⭐⭐ 3 Stars',
                        4 => '⭐⭐⭐⭐ 4 Stars',
                        5 => '⭐⭐⭐⭐⭐ 5 Stars',
                    ])
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('status')
                    ->label('Review Status')
                    ->placeholder('All Reviews')
                    ->trueLabel('Approved')
                    ->falseLabel('Rejected'),
                Filter::make('has_images')
                    ->label('Has Images')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('image_urls')
                        ->where('image_urls', '!=', '[]')
                        ->where('image_urls', '!=', '')),
                Filter::make('high_rating')
                    ->label('High Rating (4-5 stars)')
                    ->query(fn (Builder $query): Builder => $query->where('rating', '>=', 4)),
                Filter::make('low_rating')
                    ->label('Low Rating (1-2 stars)')
                    ->query(fn (Builder $query): Builder => $query->where('rating', '<=', 2)),
                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
                Filter::make('needs_review')
                    ->label('Needs Review')
                    ->query(fn (Builder $query): Builder => $query->where('status', false)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => true]))
                    ->visible(fn (Review $record) => $record->status === false),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => false]))
                    ->visible(fn (Review $record) => $record->status === true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => true])),
                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('No reviews found')
            ->emptyStateDescription('Once users start reviewing places, they will appear here.')
            ->emptyStateIcon('heroicon-o-star');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('📝 Review Information')
                    ->description('Basic review details')
                    ->schema([
                        InfolistGroup::make([
                            TextEntry::make('user.name')
                                ->label('Reviewer')
                                ->icon('heroicon-m-user')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('place.name')
                                ->label('Place')
                                ->icon('heroicon-m-map-pin')
                                ->weight(FontWeight::Bold),
                        ])->columns(2),
                        
                        InfolistGroup::make([
                            TextEntry::make('rating')
                                ->label('Rating')
                                ->formatStateUsing(fn (string $state): string => str_repeat('⭐', (int) $state) . " ({$state}/5)")
                                ->color('warning'),
                            TextEntry::make('status')
                                ->label('Status')
                                ->formatStateUsing(fn (bool $state): string => $state ? 'Approved' : 'Pending Review')
                                ->badge()
                                ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                        ])->columns(3),
                    ]),

                InfolistSection::make('💬 Review Content')
                    ->description('The actual review content and media')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Review Text')
                            ->prose()
                            ->columnSpanFull(),
                            
                        ImageEntry::make('image_urls')
                            ->label('Review Images')
                            ->size(200)
                            ->square()
                            ->columnSpanFull()
                            ->placeholder('Gambar tidak tersedia'),
                    ]),

                InfolistSection::make('📅 Timestamps')
                    ->description('Review creation and modification dates')
                    ->schema([
                        InfolistGroup::make([
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime('F j, Y g:i A')
                                ->since(),
                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime('F j, Y g:i A')
                                ->since(),
                        ])->columns(2),
                    ])
                    ->collapsible(),

                InfolistSection::make('🔧 Additional Information')
                    ->description('Extra data stored with this review')
                    ->schema([
                        TextEntry::make('additional_info.review_type')
                            ->label('Additional Data')
                    ])
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
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'navigation_badge_reviews',
            now()->addMinutes(10),
            fn () => static::getModel()::where('status', false)->count() ?: null
        );
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = \Illuminate\Support\Facades\Cache::remember(
            'navigation_badge_reviews',
            now()->addMinutes(10),
            fn () => static::getModel()::where('status', false)->count()
        );
        return $pendingCount > 0 ? 'warning' : null;
    }
    
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'place']);
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['content', 'user.name', 'place.name', 'status'];
    }
}
