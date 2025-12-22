<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppUpdateResource\Pages;
use App\Filament\Resources\AppUpdateResource\RelationManagers;
use App\Models\AppUpdate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Str;
use Filament\Infolists\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppUpdateResource extends Resource
{
    protected static ?string $model = AppUpdate::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('App Update Information')
                    ->schema([
                        Forms\Components\TextInput::make('version_name')
                            ->label('Version Name')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $code = (int) $get('version_code');
                                $platform = (string) $get('device_platform');
                                if ($code && $platform) {
                                    $ext = $platform === 'ios' ? 'ipa' : 'apk';
                                    $filename = "snappie-{$platform}-v{$code}.{$ext}";
                                    $set('apk_url', asset('app-update/' . $filename));
                                } else {
                                    $set('apk_url', null);
                                }
                            }),
                        
                        Forms\Components\TextInput::make('version_code')
                            ->label('Version Code')
                            ->numeric()
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $code = (int) $get('version_code');
                                $platform = (string) $get('device_platform');
                                if ($code && $platform) {
                                    $ext = $platform === 'ios' ? 'ipa' : 'apk';
                                    $filename = "snappie-{$platform}-v{$code}.{$ext}";
                                    $set('apk_url', asset('app-update/' . $filename));
                                } else {
                                    $set('apk_url', null);
                                }
                            }),

                        Forms\Components\Select::make('device_platform')
                            ->label('Device Platform')
                            ->required()
                            ->options([
                                'android'=> 'Android',
                                'ios'=> 'iOS',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $slug = Str::slug((string) $get('version_name'));
                                $code = (int) $get('version_code');
                                $platform = (string) $get('device_platform');
                                if ($slug && $code && $platform) {
                                    $ext = $platform === 'ios' ? 'ipa' : 'apk';
                                    $filename = "{$slug}-v{$code}.{$ext}";
                                    $set('apk_url', asset('apk/' . $filename));
                                } else {
                                    $set('apk_url', null);
                                }
                            }),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Media & Additional Information')
                    ->schema([
                        // FileUpload::make('apk_file')
                        //     ->label('Upload APK')
                        //     // ->acceptedFileTypes(['.apk'])
                        //     // ->rules(['nullable', 'mimes:apk'])
                        //     ->visibility('public')
                        //     ->disk('public')
                        //     ->dehydrated(false)
                        //     ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        //         if ($state) {
                        //             $slug = Str::slug((string) $get('version_name'));
                        //             $code = (int) $get('version_code');
                        //             $platform = (string) $get('device_platform');
                        //             $directory = "app-updates/{$platform}";
                        //             $filename = "{$slug}-v{$code}.apk";
                        //             $storedPath = $state->storeAs($directory, $filename, 'public');
                        //             $url = asset('storage/' . $storedPath);
                        //             $set('apk_url', $url);
                        //         }
                        //     })
                        //     ->columnSpanFull(),
                        Forms\Components\TextInput::make('apk_url')
                            ->label('APK URL')
                            ->maxLength(2048)
                            ->readOnly()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('changelogs')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('version_name')
                    ->label('Version')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                TextColumn::make('version_code')
                    ->label('Code')
                    ->sortable(),
                TextColumn::make('device_platform')
                    ->label('Platform')
                    ->badge()
                    ->sortable(),
                TextColumn::make('apk_url')
                    ->label('APK URL')
                    ->limit(40)
                    ->copyable()
                    ->url(fn ($record) => $record->apk_url)
                    ->openUrlInNewTab(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('device_platform')
                    ->options([
                        'android'=> 'Android',
                        'ios'=> 'iOS',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('ℹ️ Release Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('version_name')
                                    ->label('Version Name')
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),
                                TextEntry::make('version_code')
                                    ->label('Version Code')
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('device_platform')
                                    ->label('Platform')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'android' ? 'success' : 'warning'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make('APK')
                    ->schema([
                        TextEntry::make('apk_url')
                            ->label('Download URL')
                            ->url(fn ($state) => $state)
                            ->icon('heroicon-m-link')
                            ->copyable(),
                        TextEntry::make('changelogs')
                            ->label('Changelogs')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppUpdates::route('/'),
            'create' => Pages\CreateAppUpdate::route('/create'),
            'view' => Pages\ViewAppUpdate::route('/{record}'),
            'edit' => Pages\EditAppUpdate::route('/{record}/edit'),
        ];
    }
}
