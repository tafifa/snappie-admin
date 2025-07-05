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

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    
    protected static ?string $navigationGroup = 'Activity';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Review Details')
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
                                        Forms\Components\TextInput::make('address')
                                            ->maxLength(255),
                                    ]),
                            ]),
                        Forms\Components\Select::make('vote')
                            ->label('Rating')
                            ->options([
                                1 => '👍 Positive (1)',
                                0 => '👎 Negative (0)',
                            ])
                            ->default(1)
                            ->required()
                            ->native(false)
                            ->helperText('Choose whether this is a positive or negative review'),
                        Forms\Components\Select::make('status')
                            ->label('Review Status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'flagged' => 'Flagged for Review',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ]),
                
                Forms\Components\Section::make('Review Content')
                    ->description('The actual review content and media')
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label('Review Text')
                            ->rows(5)
                            ->maxLength(1000)
                            ->placeholder('Write the review content here...')
                            ->helperText('Maximum 1000 characters')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image_urls')
                            ->label('Review Images')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxSize(5120)
                            ->helperText('Upload up to 5 images for this review (max 5MB each)')
                            ->columnSpanFull(),
                    ]),
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
                Tables\Columns\TextColumn::make('content')
                    ->label('Review')
                    ->limit(100)
                    ->tooltip(function (Review $record): ?string {
                        return $record->content ? strip_tags($record->content) : null;
                    })
                    ->searchable()
                    ->wrap(),
                Tables\Columns\BadgeColumn::make('vote')
                    ->label('Rating')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => '👍 Positive',
                        0 => '👎 Negative',
                        default => 'Unknown',
                    })
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'primary' => 'flagged',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                        'heroicon-o-flag' => 'flagged',
                    ])
                    ->searchable(),
                // Tables\Columns\ImageColumn::make('image_urls')
                //     ->label('Images')
                //     ->circular()
                //     ->stacked()
                //     ->limit(3)
                //     ->limitedRemainingText()
                //     ->size(40)
                //     ->toggleable(),
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
                SelectFilter::make('vote')
                    ->label('Rating')
                    ->options([
                        1 => '👍 Positive',
                        0 => '👎 Negative',
                    ])
                    ->multiple(),
                SelectFilter::make('status')
                    ->label('Review Status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'flagged' => 'Flagged for Review',
                    ])
                    ->multiple(),
                Filter::make('has_images')
                    ->label('Has Images')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('image_urls')
                        ->where('image_urls', '!=', '[]')
                        ->where('image_urls', '!=', '')),
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
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['pending', 'flagged'])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => 'approved']))
                    ->visible(fn (Review $record) => $record->status !== 'approved'),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Review $record) => $record->update(['status' => 'rejected']))
                    ->visible(fn (Review $record) => $record->status !== 'rejected'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'approved'])),
                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'rejected'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No reviews found')
            ->emptyStateDescription('Once users start reviewing places, they will appear here.')
            ->emptyStateIcon('heroicon-o-star');
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
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $pendingCount = static::getModel()::where('status', 'pending')->count();
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
