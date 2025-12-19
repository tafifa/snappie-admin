<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaceResource\Pages;
use App\Models\Place;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Group as InfolistGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaceResource extends Resource
{
    protected static ?string $model = Place::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Places';

    protected static ?string $modelLabel = 'Place';

    protected static ?string $pluralModelLabel = 'Places';

    protected static ?string $navigationGroup = 'Location Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Opsi ini diambil dari factory Anda untuk konsistensi
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
                Group::make()
                    ->schema([
                        Section::make('Informasi Utama')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\RichEditor::make('description')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('image_urls')
                                    ->multiple()
                                    ->image()
                                    ->disk('public')
                                    ->directory('places')
                                    ->reorderable(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Forms\Components\Toggle::make('status')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->required(),
                                Forms\Components\Toggle::make('partnership_status')
                                    ->label('Status Kemitraan')
                                    ->default(false)
                                    ->required(),
                            ]),

                        Section::make('Harga & Rating')
                            ->schema([
                                Forms\Components\TextInput::make('min_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR'),
                                Forms\Components\TextInput::make('max_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR'),
                            ]),
                        Section::make('Rewards')
                            ->schema([
                                Forms\Components\TextInput::make('coin_reward')->numeric(),
                                Forms\Components\TextInput::make('exp_reward')->numeric(),
                            ]),

                        Section::make('Lokasi')
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
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Group::make()
                    ->schema([
                        Section::make('Informasi Tambahan (JSON)')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('additional_info.place_detail.short_description')
                                            ->label('Deskripsi Singkat'),
                                        Forms\Components\TextInput::make('additional_info.place_detail.address')
                                            ->label('Alamat'),
                                        Forms\Components\TimePicker::make('additional_info.place_detail.opening_hours')
                                            ->label('Jam Buka'),
                                        Forms\Components\TimePicker::make('additional_info.place_detail.closing_hours')
                                            ->label('Jam Tutup'),
                                        Forms\Components\TextInput::make('additional_info.place_detail.contact_number')
                                            ->label('Nomor Kontak')
                                            ->tel(),
                                        Forms\Components\TextInput::make('additional_info.place_detail.website')
                                            ->label('Website')
                                            ->url()
                                            ->columnSpanFull(),
                                        Forms\Components\CheckboxList::make('additional_info.place_detail.opening_days')
                                            ->label('Hari Buka')
                                            ->options(['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu', 'Minggu' => 'Minggu']),
                                    ]),
                                Forms\Components\CheckboxList::make('additional_info.place_value')
                                    ->label('Nilai Tempat (Place Value)')
                                    ->options(array_combine($placeValueOptions, $placeValueOptions))
                                    ->columns(3),
                                Forms\Components\CheckboxList::make('additional_info.food_type')
                                    ->label('Jenis Makanan')
                                    ->options(array_combine($foodTypeOptions, $foodTypeOptions))
                                    ->columns(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Group::make()
                    ->schema([
                        Section::make('Menu')
                            ->schema([
                                Forms\Components\FileUpload::make('additional_info.menu_image_url')
                                    ->label('Gambar Menu')
                                    ->image()
                                    ->disk('public')
                                    ->directory('places'),
                                Forms\Components\Repeater::make('additional_info.menu')
                                    ->label('Menu Favorit')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                Forms\Components\TextInput::make('name')->required(),
                                                Forms\Components\TextInput::make('price')->numeric()->prefix('IDR'),
                                            ])
                                            ->columns(2),
                                        Group::make()
                                            ->schema([
                                                Forms\Components\FileUpload::make('image_url')
                                                    ->label('Gambar')
                                                    ->image()
                                                    ->disk('public')
                                                    ->directory('places'),
                                                Forms\Components\TextInput::make('description'),
                                            ])
                                            ->columns(1),
                                    ])
                                    ->defaultItems(1),
                            ])
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Atribut Tempat (JSON)')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                Forms\Components\Repeater::make('additional_info.place_attributes.facility')
                                                    ->label('Fasilitas')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name'),
                                                        Forms\Components\TextInput::make('description'),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1),
                                                Forms\Components\Repeater::make('additional_info.place_attributes.parking')
                                                    ->label('Parkir')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name'),
                                                        Forms\Components\TextInput::make('description'),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1),
                                                Forms\Components\Repeater::make('additional_info.place_attributes.capacity')
                                                    ->label('Kapasitas')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name'),
                                                        Forms\Components\TextInput::make('description'),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1),
                                            ])->columnSpan(1),
                                        Section::make()
                                            ->schema([
                                                Forms\Components\Repeater::make('additional_info.place_attributes.accessibility')
                                                    ->label('Aksesibilitas')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name'),
                                                        Forms\Components\TextInput::make('description'),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1),
                                                Forms\Components\Repeater::make('additional_info.place_attributes.payment')
                                                    ->label('Pembayaran')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name'),
                                                        Forms\Components\TextInput::make('description'),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1),
                                                Forms\Components\Repeater::make('additional_info.place_attributes.service')
                                                    ->label('Layanan')
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name'),
                                                        Forms\Components\TextInput::make('description'),
                                                    ])
                                                    ->columns(2)
                                                    ->defaultItems(1),
                                            ])->columnSpan(1)
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_rating')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\IconColumn::make('partnership_status')
                    ->label('Partner')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('partnership_status')
                    ->label('Kemitraan')
                    ->options([
                        1 => 'Partner',
                        0 => 'Bukan Partner',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Place $record) => $record->update(['status' => true]))
                    ->visible(fn(Place $record) => $record->status === false),
                Tables\Actions\Action::make('deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Place $record) => $record->update(['status' => false]))
                    ->visible(fn(Place $record) => $record->status === true),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistGroup::make()
                    ->schema([
                        InfolistSection::make('Informasi Utama')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Tempat'),
                                TextEntry::make('description')
                                    ->label('Deskripsi')
                                    ->html()
                                    ->columnSpanFull(),
                                ImageEntry::make('image_urls')
                                ->label('Gambar')
                                ->disk('public')
                                ->columnSpanFull()
                                ->getStateUsing(function ($record) {
                                    // Pastikan image_urls adalah array dan bukan string
                                    $imageUrls = is_string($record->image_urls)
                                    ? json_decode($record->image_urls, true)
                                    : $record->image_urls;
                                    
                                    return $imageUrls;
                                }),
                                InfolistSection::make('Lokasi')
                                    ->schema([
                                        TextEntry::make('latitude')
                                            ->label('Latitude'),
                                        TextEntry::make('longitude')
                                            ->label('Longitude'),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                InfolistGroup::make()
                    ->schema([
                        InfolistGroup::make()
                            ->schema([
                                InfolistSection::make('Status')
                                    ->schema([
                                        IconEntry::make('status')
                                            ->label('Status Aktif')
                                            ->boolean(),
                                        IconEntry::make('partnership_status')
                                            ->label('Status Kemitraan')
                                            ->boolean(),
                                    ])->columnSpan(1),

                                InfolistSection::make('Harga & Rating')
                                    ->schema([
                                        TextEntry::make('min_price')
                                            ->label('Harga Minimum')
                                            ->money('IDR'),
                                        TextEntry::make('max_price')
                                            ->label('Harga Maksimum')
                                            ->money('IDR'),
                                    ])->columnSpan(1),
                            ])
                            ->columns(2),

                        InfolistSection::make('Statistik & Reward')
                            ->schema([
                                InfolistGroup::make()
                                    ->schema([
                                        TextEntry::make('exp_reward')
                                            ->label('Reward EXP')
                                            ->badge()
                                            ->color('info'),
                                        TextEntry::make('total_checkin')
                                            ->label('Total Check-in')
                                            ->badge()
                                            ->color('primary'),

                                    ])
                                    ->columns(2),
                                InfolistGroup::make()
                                    ->schema([
                                        TextEntry::make('coin_reward')
                                            ->label('Reward Koin')
                                            ->badge()
                                            ->color('warning'),
                                        TextEntry::make('total_review')
                                            ->label('Total Review')
                                            ->badge()
                                            ->color('success'),
                                    ])
                                    ->columns(2),

                                TextEntry::make('avg_rating')
                                    ->label('Rating Rata-rata')
                                    ->badge()
                                    ->color(fn(string $state): string => match (true) {
                                        $state >= 4.5 => 'success',
                                        $state >= 4.0 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                InfolistGroup::make()
                    ->schema([
                        InfolistSection::make('Detail Tempat')
                            ->schema([
                                TextEntry::make('additional_info.place_detail.short_description')
                                    ->label('Deskripsi Singkat'),
                                TextEntry::make('additional_info.place_detail.address')
                                    ->label('Alamat'),
                                TextEntry::make('additional_info.place_detail.opening_hours')
                                    ->label('Jam Buka'),
                                TextEntry::make('additional_info.place_detail.closing_hours')
                                    ->label('Jam Tutup'),
                                TextEntry::make('additional_info.place_detail.contact_number')
                                    ->label('Nomor Kontak'),
                                TextEntry::make('additional_info.place_detail.website')
                                    ->label('Website')
                                    ->url(fn ($record) => $record->additional_info['place_detail']['website'] ?? null)
                                    ->openUrlInNewTab(),
                                TextEntry::make('additional_info.place_detail.opening_days')
                                    ->label('Hari Buka')
                                    ->badge()
                                    ->separator(','),
                            ])
                            ->columns(2),

                        InfolistSection::make('Nilai & Jenis Tempat')
                            ->schema([
                                TextEntry::make('additional_info.place_value')
                                    ->label('Nilai Tempat')
                                    ->badge()
                                    ->separator(',')
                                    ->color('success'),
                                TextEntry::make('additional_info.food_type')
                                    ->label('Jenis Makanan')
                                    ->badge()
                                    ->separator(',')
                                    ->color('warning'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                InfolistGroup::make()
                    ->schema([
                        InfolistSection::make('Menu')
                            ->schema([
                                ImageEntry::make('additional_info.menu_image_url')
                                    ->label('Gambar Menu')
                                    ->disk('public'),
                                RepeatableEntry::make('additional_info.menu')
                                    ->label('Menu Favorit')
                                    ->schema([
                                        ImageEntry::make('image_url')
                                            ->label('Gambar')
                                            ->disk('public'),
                                        TextEntry::make('name')
                                            ->label('Nama Menu'),
                                        TextEntry::make('price')
                                            ->label('Harga')
                                            ->money('IDR'),
                                        TextEntry::make('description')
                                            ->label('Deskripsi'),
                                    ])
                                    ->columns(4),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                InfolistGroup::make()
                    ->schema([
                        InfolistSection::make('Atribut Tempat')
                            ->schema([
                                InfolistGroup::make()
                                    ->schema([
                                        InfolistSection::make('Fasilitas & Parkir')
                                            ->schema([
                                                RepeatableEntry::make('additional_info.place_attributes.facility')
                                                    ->label('Fasilitas')
                                                    ->schema([
                                                        TextEntry::make('name')->label('Nama'),
                                                        TextEntry::make('description')->label('Deskripsi'),
                                                    ])
                                                    ->columns(2),
                                                RepeatableEntry::make('additional_info.place_attributes.parking')
                                                    ->label('Parkir')
                                                    ->schema([
                                                        TextEntry::make('name')->label('Nama'),
                                                        TextEntry::make('description')->label('Deskripsi'),
                                                    ])
                                                    ->columns(2),
                                                RepeatableEntry::make('additional_info.place_attributes.capacity')
                                                    ->label('Kapasitas')
                                                    ->schema([
                                                        TextEntry::make('name')->label('Nama'),
                                                        TextEntry::make('description')->label('Deskripsi'),
                                                    ])
                                                    ->columns(2),
                                            ])->columnSpan(1),
                                        InfolistSection::make('Layanan & Pembayaran')
                                            ->schema([
                                                RepeatableEntry::make('additional_info.place_attributes.accessibility')
                                                    ->label('Aksesibilitas')
                                                    ->schema([
                                                        TextEntry::make('name')->label('Nama'),
                                                        TextEntry::make('description')->label('Deskripsi'),
                                                    ])
                                                    ->columns(2),
                                                RepeatableEntry::make('additional_info.place_attributes.payment')
                                                    ->label('Pembayaran')
                                                    ->schema([
                                                        TextEntry::make('name')->label('Nama'),
                                                        TextEntry::make('description')->label('Deskripsi'),
                                                    ])
                                                    ->columns(2),
                                                RepeatableEntry::make('additional_info.place_attributes.service')
                                                    ->label('Layanan')
                                                    ->schema([
                                                        TextEntry::make('name')->label('Nama'),
                                                        TextEntry::make('description')->label('Deskripsi'),
                                                    ])
                                                    ->columns(2),
                                            ])->columnSpan(1),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                InfolistGroup::make()
                    ->schema([
                        InfolistSection::make('Informasi Sistem')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(1);
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
