<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $foodTypeOptions = [
            'Non-Sup',
            'Mi Instan',
            'Menu Komposit',
            'Sup/Soto',
            'Menu Campuran',
            'Minuman dan Tambahan',
            'Liwetan',
            'Gaya Padang',
            'Gaya Tionghoa',
            'Makanan Cepat Saji',
            'Makanan Tradisional',
            'Makanan Kemasan',
            'Buah-buahan'
        ];

        $placeValueOptions = [
            'Harga Terjangkau',
            'Rasa Autentik',
            'Menu Unik/Variasi',
            'Buka 24 Jam',
            'Jaringan Lancar',
            'Estetika/Instagrammable',
            'Suasana Tenang',
            'Suasana Homey',
            'Bersejarah/Tradisional',
            'Pet Friendly',
            'Ramah Keluarga',
            'Pelayanan Ramah',
            'Rapat/Diskusi',
            'Nongkrong',
            'Work From Cafe'
        ];

        return $form
            ->schema([
                // Section 1: Informasi Personal
                Forms\Components\Section::make('👤 Informasi Personal')
                    ->description('Informasi dasar pengguna')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->placeholder('Masukkan nama lengkap')
                                    ->maxLength(255)
                                    ->required(),

                                Forms\Components\TextInput::make('username')
                                    ->label('Username')
                                    ->placeholder('Masukkan username unik')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->helperText('Username harus unik dan tidak boleh sama dengan pengguna lain'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->placeholder('user@example.com')
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->suffixIcon('heroicon-m-envelope'),

                                Forms\Components\Select::make('additional_info.user_detail.gender')
                                    ->label('Gender')
                                    ->options([
                                        'male' => 'Male',
                                        'female' => 'Female',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->helperText('Pilih gender pengguna'),
                            ]),
                    ])->collapsible(),

                // Section 2: Media & Profil
                Forms\Components\Section::make('🖼️ Media & Profil')
                    ->description('Gambar profil dan informasi visual')
                    ->schema([
                        Forms\Components\Textarea::make('image_url')
                            ->label('URL Gambar Profil')
                            ->placeholder('https://example.com/profile-image.jpg')
                            ->rows(3)
                            ->helperText('Masukkan URL gambar profil pengguna')
                            ->columnSpanFull(),
                    ])->collapsible(),

                // Section 3: Informasi Tambahan
                Forms\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->description('Data tambahan dan fleksibel')
                    ->schema([
                        Forms\Components\CheckboxList::make('additional_info.user_preferences.place_value')
                            ->label('Nilai Tempat (Place Value)')
                            ->options(array_combine($placeValueOptions, $placeValueOptions))
                            ->columns(3),
                        Forms\Components\CheckboxList::make('additional_info.user_preferences.food_type')
                            ->label('Jenis Makanan')
                            ->options(array_combine($foodTypeOptions, $foodTypeOptions))
                            ->columns(3),
                    ])->collapsible(),

                Forms\Components\Section::make('📁 Daftar Tersimpan')
                    ->description('Kelola daftar tempat, postingan, dan artikel yang disimpan pengguna')
                    ->schema([
                        Forms\Components\Repeater::make('saved_items')
                            ->label('Item Tersimpan')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('Kategori')
                                    ->options([
                                        'saved_places' => 'Tempat',
                                        'saved_posts' => 'Postingan',
                                        'saved_articles' => 'Artikel',
                                    ])
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('item_id')
                                    ->label('ID Item')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->grid(2)
                            ->columnSpanFull()
                            ->reorderable()
                            ->addActionLabel('Tambah item'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with([]))
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=random'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->limit(30),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->native(false),

                Filter::make('high_coin')
                    ->label('Coin Tinggi (≥100)')
                    ->query(fn(Builder $query): Builder => $query->where('total_coin', '>=', 100)),

                Filter::make('high_exp')
                    ->label('EXP Tinggi (≥500)')
                    ->query(fn(Builder $query): Builder => $query->where('total_exp', '>=', 500)),

                Filter::make('popular_user')
                    ->label('User Populer (≥10 Followers)')
                    ->query(fn(Builder $query): Builder => $query->where('total_follower', '>=', 10)),

                Filter::make('active_user')
                    ->label('User Aktif (≥5 Check-ins)')
                    ->query(fn(Builder $query): Builder => $query->where('total_checkin', '>=', 5)),

                Filter::make('recent_login')
                    ->label('Login Baru-baru ini')
                    ->query(fn(Builder $query): Builder => $query->where('last_login_at', '>=', now()->subDays(7))),

                Filter::make('today')
                    ->label('Bergabung Hari Ini')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today())),

                Filter::make('this_week')
                    ->label('Bergabung Minggu Ini')
                    ->query(fn(Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(User $record) => $record->update(['status' => true]))
                    ->visible(fn(User $record) => $record->status === false),
                Tables\Actions\Action::make('deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(User $record) => $record->update(['status' => false]))
                    ->visible(fn(User $record) => $record->status === true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['status' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['status' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('No users found')
            ->emptyStateDescription('Once users register, they will appear here.')
            ->emptyStateIcon('heroicon-o-user-group');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section 1: Informasi Personal
                Section::make('👤 Informasi Personal')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('image_url')
                                    ->label('Foto Profil')
                                    ->circular()
                                    ->size(100)
                                    ->defaultImageUrl('https://ui-avatars.com/api/?background=random'),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->label('Nama Lengkap')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg'),

                                        TextEntry::make('username')
                                            ->label('Username')
                                            ->badge()
                                            ->color('info')
                                            ->copyable(),

                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->icon('heroicon-m-envelope')
                                            ->copyable(),

                                        TextEntry::make('additional_info.user_detail.gender')
                                            ->label('Gender')
                                            ->icon('heroicon-m-rectangle-stack')
                                            ->copyable(),

                                        TextEntry::make('additional_info.user_detail.bio')
                                            ->label('Bio')
                                            ->icon('heroicon-m-rectangle-stack')
                                            ->copyable(),

                                        TextEntry::make('additional_info.user_detail.phone')
                                            ->label('Phone')
                                            ->icon('heroicon-m-phone')
                                            ->copyable(),
                                        
                                        TextEntry::make('additional_info.user_detail.date_of_birth')
                                            ->label('Date of Birth')
                                            ->icon('heroicon-m-calendar')
                                            ->copyable(),
                                    ]),
                            ]),
                    ]),

                // Section 2: Statistik Game
                Section::make('🎮 Statistik Game')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_coin')
                                    ->label('Total Coin')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-currency-dollar'),

                                TextEntry::make('total_exp')
                                    ->label('Total EXP')
                                    ->numeric()
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-star'),

                                TextEntry::make('total_following')
                                    ->label('Following')
                                    ->numeric()
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-user-plus'),

                                TextEntry::make('total_follower')
                                    ->label('Followers')
                                    ->numeric()
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-m-users'),
                            ]),
                    ]),

                // Section 3: Aktivitas Pengguna
                Section::make('📊 Aktivitas Pengguna')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_checkin')
                                    ->label('Check-ins')
                                    ->numeric()
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-map-pin'),

                                TextEntry::make('total_post')
                                    ->label('Posts')
                                    ->numeric()
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-m-document-text'),

                                TextEntry::make('total_article')
                                    ->label('Articles')
                                    ->numeric()
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-m-newspaper'),

                                TextEntry::make('total_review')
                                    ->label('Reviews')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-m-chat-bubble-left-ellipsis'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_achievement')
                                    ->label('Achievements')
                                    ->numeric()
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-trophy'),

                                TextEntry::make('total_challenge')
                                    ->label('Challenges')
                                    ->numeric()
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-m-fire'),
                            ]),
                    ]),

                // Section 4: Status & Aktivitas
                Section::make('⚙️ Status & Aktivitas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status Akun')
                                    ->badge()
                                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn(bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),

                                TextEntry::make('last_login_at')
                                    ->label('Login Terakhir')
                                    ->dateTime('d M Y, H:i')
                                    ->since()
                                    ->placeholder('Belum pernah login')
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ]),

                // Section 5: Informasi Tambahan
                Section::make('ℹ️ Informasi Tambahan')
                    ->schema([
                         Section::make('Nilai & Jenis Tempat')
                            ->schema([
                                TextEntry::make('additional_info.user_preferences.place_value')
                                    ->label('Nilai Tempat')
                                    ->badge()
                                    ->separator(',')
                                    ->color('success'),
                                TextEntry::make('additional_info.user_preferences.food_type')
                                    ->label('Jenis Makanan')
                                    ->badge()
                                    ->separator(',')
                                    ->color('warning'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('📁 Daftar Tersimpan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('additional_info.user_saved.saved_places')
                                    ->label('Saved Places')
                                    ->badge()
                                    ->separator(',')
                                    ->color('info'),
                                TextEntry::make('additional_info.user_saved.saved_posts')
                                    ->label('Saved Posts')
                                    ->badge()
                                    ->separator(',')
                                    ->color('primary'),
                                TextEntry::make('additional_info.user_saved.saved_articles')
                                    ->label('Saved Articles')
                                    ->badge()
                                    ->separator(',')
                                    ->color('secondary'),
                            ]),
                    ])
                    ->collapsible(),

                // Section 6: Timestamp
                Section::make('⏰ Riwayat')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Bergabung Pada')
                                    ->dateTime('d M Y, H:i:s')
                                    ->since()
                                    ->icon('heroicon-m-plus-circle'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
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
            RelationManagers\FollowersRelationManager::class,
            RelationManagers\FollowingRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'username', 'email'];
    }
}
