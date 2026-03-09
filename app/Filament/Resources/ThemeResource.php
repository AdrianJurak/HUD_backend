<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThemeResource\Pages;
use App\Filament\Resources\ThemeResource\RelationManagers;
use App\Models\Theme;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;


class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('likes')
                    ->required()
                    ->numeric()
                    ->default(0),
                Builder::make('layout_config')
                    ->label('Theme Layout')
                    ->blocks([
                        Block::make('speedometer')
                            ->schema([
                                TextInput::make('x')->numeric()->default(100)->required(),
                                TextInput::make('y')->numeric()->default(100)->required(),
                                TextInput::make('size')->numeric()->default(50)->label('Font size')->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                                Select::make('style')->options([
                                    'digital'=>'Digital',
                                    'gauge'=>'Gauge',
                                    'bar'=>'Bar',
                                ])->default('digital'),
                            ])->icon('heroicon-m-bolt'),

                        Block::make('tachometer')
                            ->schema([
                                TextInput::make('x')->numeric()->default(150)->required(),
                                TextInput::make('y')->numeric()->default(150)->required(),
                                TextInput::make('size')->numeric()->default(50)->label('Font Size')->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                                Select::make('style')->options([
                                    'digital'=>'Digital',
                                    'gauge'=>'Gauge',
                                    'bar'=>'Bar',
                                ])->default('digital'),
                                TextInput::make('max_rpm')->numeric()->default(7000)->label('Max RPM')->required(),
                                Checkbox::make('redline')->label('Show redline?')->default(true),
                            ])->icon('heroicon-m-arrow-path'),

                        Block::make('options')
                            ->schema([
                                TextInput::make('x')->numeric()->default(200)->required(),
                                TextInput::make('y')->numeric()->default(200)->required(),
                                TextInput::make('size')->numeric()->default(50)->label('Font Size')->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                                Select::make('type')
                                    ->options([
                                        'clock'=>'Clock',
                                        'fuel'=>'Fuel',
                                        'water_temperature'=>'Water Temperature',
                                        'oil_temperature'=>'Oil Temperature',
                                    ])->default('water_temperature'),
                            ])->icon('heroicon-m-clock')
                    ])->columnSpanFull(),
                FileUpload::make('images')
                    ->label('Theme Images')
                    ->image()
                    ->multiple()
                    ->directory('theme-images')
                    ->maxFiles(5)
                    ->reorederable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('likes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('downloads_count')
                    ->counts('download')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->label('Added at'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListThemes::route('/'),
            'create' => Pages\CreateTheme::route('/create'),
            'edit' => Pages\EditTheme::route('/{record}/edit'),
        ];
    }
}
