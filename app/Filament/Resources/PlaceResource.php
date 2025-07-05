<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Filament\Resources\PlaceResource\RelationManagers;
use App\Models\Place;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Support\Str;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    protected static ?string $navigationGroup = 'Core Data';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi Dasar
                Forms\Components\Section::make('📍 Informasi Dasar Tempat')
                    ->description('Masukkan informasi dasar tentang tempat ini')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tempat')
                            ->placeholder('Masukkan nama tempat')
                            ->maxLength(255)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL')
                            ->placeholder('Auto-generated from name')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->helperText('Slug akan otomatis dibuat dari nama tempat'),

                        Forms\Components\Select::make('category')
                            ->label('Kategori Tempat')
                            ->options([
                                'cafeEatery' => '☕ Cafe & Eatery',
                                'tradisional' => '🍜 Tradisional',
                                'foodcourt' => '🍽️ Food Court',
                                'streetfood' => '🌮 Street Food',
                                'restaurant' => '🍴 Restaurant',
                            ])
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2)->collapsible(),

                // Section 2: Deskripsi & Alamat
                Forms\Components\Section::make('📝 Deskripsi & Lokasi')
                    ->description('Berikan deskripsi lengkap dan alamat tempat')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Deskripsi Lengkap')
                            ->placeholder('Masukkan deskripsi lengkap tempat')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->placeholder('Masukkan alamat lengkap tempat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(1)->collapsible(),

                // Section 3: Koordinat GPS
                Forms\Components\Section::make('🗺️ Koordinat GPS')
                    ->description('Masukkan koordinat latitude dan longitude untuk pemetaan')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.000001)
                            ->placeholder('Contoh: -6.200000')
                            ->helperText('Koordinat lintang (rentang: -90 sampai 90)'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.000001)
                            ->placeholder('Contoh: 106.816666')
                            ->helperText('Koordinat bujur (rentang: -180 sampai 180)'),
                    ])->columns(2)->collapsible(),

                // Section 4: Media & Gambar
                Forms\Components\Section::make('🖼️ Media & Gambar')
                    ->description('Upload atau masukkan URL gambar-gambar tempat')
                    ->schema([
                        Forms\Components\Repeater::make('image_urls')
                            ->label('URL Gambar')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label('URL Gambar')
                                    ->url()
                                    ->placeholder('https://example.com/image.jpg')
                                    ->required()
                                    ->suffixIcon('heroicon-m-photo'),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->addActionLabel('➕ Tambah Gambar')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ])->columns(1)->collapsible(),

                // Section 5: Status & Partnership
                Forms\Components\Section::make('⚙️ Status & Partnership')
                    ->description('Atur status aktif dan kemitraan tempat')
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Status Aktif')
                            ->helperText('Apakah tempat ini aktif dan dapat dikunjungi?')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('partnership_status')
                            ->label('Status Partnership')
                            ->helperText('Apakah tempat ini memiliki kemitraan dengan aplikasi?')
                            ->default(false)
                            ->inline(false),
                    ])->columns(2)->collapsible(),

                // Section 6: Misi & Game
                Forms\Components\Section::make('🎮 Misi & Game')
                    ->description('Setup misi dan petunjuk untuk game')
                    ->schema([
                        Forms\Components\Textarea::make('clue_mission')
                            ->label('Petunjuk Misi')
                            ->placeholder('Contoh: Cari patung di depan gerbang utama yang menghadap ke arah timur')
                            ->rows(4)
                            ->helperText('Berikan petunjuk yang jelas untuk membantu pengguna menemukan lokasi')
                            ->columnSpanFull(),
                    ])->columns(1)->collapsible(),

                // Section 7: Sistem Reward
                Forms\Components\Section::make('🏆 Sistem Reward')
                    ->description('Tentukan reward yang didapat pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('exp_reward')
                            ->label('Reward EXP')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->placeholder('10')
                            ->default(10)
                            ->helperText('Experience points yang didapat (0-1000)')
                            ->suffixIcon('heroicon-m-star'),

                        Forms\Components\TextInput::make('coin_reward')
                            ->label('Reward Coin')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1000)
                            ->placeholder('10')
                            ->default(10)
                            ->helperText('Coin yang didapat (0-1000)')
                            ->suffixIcon('heroicon-m-currency-dollar'),
                    ])->columns(2)->collapsible(),

                // Section 8: Informasi Tambahan
                Forms\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->description('Tambahkan informasi opsional lainnya')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Informasi Tambahan')
                            ->helperText('Contoh: website, kapasitas, jam_buka, nomor_telepon, fasilitas')
                            ->keyLabel('Kunci')
                            ->valueLabel('Nilai')
                            ->addActionLabel('➕ Tambah Info')
                            ->columnSpanFull(),
                    ])->columns(1)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Tempat')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cafeEatery' => 'info',
                        'tradisional' => 'warning',
                        'foodcourt' => 'success',
                        'streetfood' => 'danger',
                        'restaurant' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->address;
                    }),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('partnership_status')
                    ->label('Partnership')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('exp_reward')
                    ->label('EXP')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('coin_reward')
                    ->label('Coin')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'cafeEatery' => 'Cafe & Eatery',
                        'tradisional' => 'Tradisional',
                        'foodcourt' => 'Food Court',
                        'streetfood' => 'Street Food',
                        'restaurant' => 'Restaurant',
                    ]),

                Tables\Filters\TernaryFilter::make('status')
                    ->label('Status Aktif'),

                Tables\Filters\TernaryFilter::make('partnership_status')
                    ->label('Partnership'),
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
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Umum Tempat')
                    ->schema([
                        TextEntry::make('name')->label('Nama Tempat'),
                        TextEntry::make('slug')->label('Slug'),
                        TextEntry::make('category')
                            ->label('Kategori')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'cafeEatery' => 'info',
                                'tradisional' => 'warning',
                                'foodcourt' => 'success',
                                'streetfood' => 'danger',
                                'restaurant' => 'primary',
                                default => 'gray',
                            }),
                        TextEntry::make('address')->label('Alamat Lengkap'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->html()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Lokasi & Media')
                    ->schema([
                        TextEntry::make('latitude')->label('Latitude'),
                        TextEntry::make('longitude')->label('Longitude'),
                        TextEntry::make('image_urls')
                            ->label('Gambar')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return 'Tidak ada gambar';
                                }
                                if (is_array($state)) {
                                    return count($state) . ' gambar tersedia';
                                }
                                return 'Tidak ada gambar';
                            })
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Status & Partnership')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status Aktif')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),
                        TextEntry::make('partnership_status')
                            ->label('Status Partnership')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Partner' : 'Bukan Partner'),
                    ])->columns(2),

                Section::make('Detail Misi & Reward')
                    ->schema([
                        TextEntry::make('clue_mission')->label('Clue Misi')->columnSpanFull(),
                        TextEntry::make('exp_reward')->label('Reward EXP')->numeric(),
                        TextEntry::make('coin_reward')->label('Reward Coin')->numeric(),
                    ])->columns(2),

                // Section::make('Informasi Tambahan')
                //     ->schema([
                //         TextEntry::make('additional_info')
                //             ->label('Info Tambahan')
                //             ->formatStateUsing(function ($state) {
                //                 if (empty($state)) {
                //                     return 'Tidak ada informasi tambahan';
                //                 }
                //                 if (is_array($state)) {
                //                     return collect($state)->map(fn($value, $key) => "{$key}: {$value}")->implode(' | ');
                //                 }
                //                 // If it's a string, try to decode it
                //                 if (is_string($state)) {
                //                     $decoded = json_decode($state, true);
                //                     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                //                         return collect($decoded)->map(fn($value, $key) => "{$key}: {$value}")->implode(' | ');
                //                     }
                //                 }
                //                 return 'Tidak ada informasi tambahan';
                //             })
                //             ->columnSpanFull(),
                //         TextEntry::make('created_at')->label('Dibuat Pada')->dateTime(),
                //         TextEntry::make('updated_at')->label('Diperbarui Pada')->dateTime(),
                //     ])->columns(2),
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
            'index' => Pages\ListPlaces::route('/'),
            'create' => Pages\CreatePlace::route('/create'),
            'view' => Pages\ViewPlace::route('/{record}'),
            'edit' => Pages\EditPlace::route('/{record}/edit'),
        ];
    }
}
