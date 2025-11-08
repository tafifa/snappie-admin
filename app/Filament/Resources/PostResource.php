<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Grid;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('place_id')
                            ->label('Place')
                            ->relationship('place', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->maxLength(2000)
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('status')
                            ->label('Active Status')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('total_like')
                            ->label('Total Likes')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('total_comment')
                            ->label('Total Comments')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),

                Forms\Components\Section::make('Media & Additional Information')
                    ->schema([
                        Forms\Components\FileUpload::make('image_urls')
                            ->label('Post Images')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(5120)
                            ->helperText('Upload up to 5 images (max 5MB each) - will be automatically uploaded to Cloudinary')
                            ->disk('public')
                            ->directory('temp-posts')
                            ->visibility('public')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Additional Information')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'place']))
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('place.name')
                    ->label('Place')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_like')
                    ->label('Likes')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_comment')
                    ->label('Comments')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active Posts')
                    ->falseLabel('Inactive Posts')
                    ->native(false),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('place')
                    ->relationship('place', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Post $record) => $record->update(['status' => true]))
                    ->visible(fn (Post $record) => $record->status === false),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Post $record) => $record->update(['status' => false]))
                    ->visible(fn (Post $record) => $record->status === true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Post Information
                Section::make('💬 Post Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('User')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('place.name')
                                    ->label('Place')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-map-pin'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                                    ->icon(fn(bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),
                            ]),

                        TextEntry::make('content')
                            ->label('Content')
                            ->columnSpanFull(),
                    ]),

                // Section 2: Media
                Section::make('🖼️ Media')
                    ->schema([
                        ImageEntry::make('image_urls')
                            ->label('Post Images')
                            ->size(200)
                            ->square()
                            ->columnSpanFull()
                            ->placeholder('Gambar tidak tersedia'),
                    ])
                    ->collapsible(),

                // Section 3: Statistics
                Section::make('📊 Statistics')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_like')
                                    ->label('Total Likes')
                                    ->numeric()
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-m-heart'),

                                TextEntry::make('total_comment')
                                    ->label('Total Comments')
                                    ->numeric()
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-chat-bubble-left-ellipsis'),
                            ]),
                    ]),

                // Section 4: Additional Information
                Section::make('ℹ️ Additional Information')
                    ->schema([
                        KeyValueEntry::make('additional_info')
                            ->label('Additional Data')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Section 5: Timestamps
                Section::make('⏰ Timeline')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d M Y, H:i:s')
                                    ->since()
                                    ->icon('heroicon-m-plus-circle'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M Y, H:i:s')
                                    ->since()
                                    ->icon('heroicon-m-pencil-square'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LikesRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}