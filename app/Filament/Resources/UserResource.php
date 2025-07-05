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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Core Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi Personal
                Forms\Components\Section::make('👤 Informasi Personal')
                    ->description('Informasi dasar pengguna')
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
                    ])->columns(2)->collapsible(),

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
                    ])->columns(1)->collapsible(),

                // Section 3: Game Stats
                Forms\Components\Section::make('🎮 Statistik Game')
                    ->description('Coin dan experience points pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('total_coin')
                            ->label('Total Coin')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->placeholder('0')
                            ->helperText('Total coin yang dimiliki pengguna')
                            ->suffixIcon('heroicon-m-currency-dollar'),

                        Forms\Components\TextInput::make('total_exp')
                            ->label('Total EXP')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->placeholder('0')
                            ->helperText('Total experience points pengguna')
                            ->suffixIcon('heroicon-m-star'),
                    ])->columns(2)->collapsible(),

                // Section 4: Status & Activity
                Forms\Components\Section::make('⚙️ Status & Aktivitas')
                    ->description('Status akun dan aktivitas terakhir')
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Status Aktif')
                            ->helperText('Apakah akun pengguna aktif?')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\DateTimePicker::make('last_login_at')
                            ->label('Login Terakhir')
                            ->placeholder('Pilih tanggal dan waktu')
                            ->helperText('Kapan pengguna terakhir kali login')
                            ->native(false),
                    ])->columns(2)->collapsible(),

                // Section 5: Informasi Tambahan
                Forms\Components\Section::make('ℹ️ Informasi Tambahan')
                    ->description('Data tambahan dan fleksibel')
                    ->schema([
                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Informasi Tambahan')
                            ->helperText('Contoh: bio, preferences, social_media, phone')
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
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Avatar')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl('https://ui-avatars.com/api/?background=random'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

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

                Tables\Columns\TextColumn::make('total_coin')
                    ->label('Coin')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->icon('heroicon-m-currency-dollar'),

                Tables\Columns\TextColumn::make('total_exp')
                    ->label('EXP')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-star'),

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

                Tables\Filters\Filter::make('high_coin')
                    ->label('Coin Tinggi')
                    ->query(fn(Builder $query): Builder => $query->where('total_coin', '>=', 100))
                    ->toggle(),

                Tables\Filters\Filter::make('high_exp')
                    ->label('EXP Tinggi')
                    ->query(fn(Builder $query): Builder => $query->where('total_exp', '>=', 500))
                    ->toggle(),

                Tables\Filters\Filter::make('recent_login')
                    ->label('Login Baru-baru ini')
                    ->query(fn(Builder $query): Builder => $query->where('last_login_at', '>=', now()->subDays(7)))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Aktifkan')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['status' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Nonaktifkan')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn($records) => $records->each->update(['status' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Personal')
                    ->schema([
                        ImageEntry::make('image_url')
                            ->label('Foto Profil')
                            ->circular()
                            ->size(100)
                            ->defaultImageUrl('https://ui-avatars.com/api/?background=random'),
                        TextEntry::make('name')
                            ->label('Nama Lengkap')
                            ->weight('bold')
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
                    ])->columns(2),

                Section::make('Statistik Game')
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
                    ])->columns(2),

                Section::make('Status & Aktivitas')
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
                            ->placeholder('Belum pernah login'),
                    ])->columns(2),

                // Section::make('Informasi Tambahan')
                //     ->schema([                        TextEntry::make('additional_info')
                //             ->label('Info Tambahan')
                //             ->formatStateUsing(function ($state) {
                //                 if (empty($state)) {
                //                     return 'Tidak ada informasi tambahan';
                //                 }
                //                 if (is_array($state) && count($state) > 0) {
                //                     return collect($state)->map(fn($value, $key) => "{$key}: {$value}")->implode(' | ');
                //                 }
                //                 return 'Tidak ada informasi tambahan';
                //             })
                //             ->columnSpanFull(),
                //         TextEntry::make('created_at')
                //             ->label('Bergabung Pada')
                //             ->dateTime('d M Y, H:i')
                //             ->since(),
                //         TextEntry::make('updated_at')
                //             ->label('Terakhir Diperbarui')
                //             ->dateTime('d M Y, H:i')
                //             ->since(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
