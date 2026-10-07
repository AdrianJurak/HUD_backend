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
use Filament\Forms\Components\Section;
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
                    ->label('User Id')
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull(),
                Builder::make('layout_config')
                    ->label('Theme Layout')
                    ->blocks([
                        Block::make('speed_label')
                            ->schema([
                                TextInput::make('offsetX')->numeric()->default(0)->required(),
                                TextInput::make('offsetY')->numeric()->default(0)->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                            ])->icon('heroicon-m-bolt'),

                        Block::make('kmh_label')
                            ->schema([
                                TextInput::make('offsetX')->numeric()->default(487)->required(),
                                TextInput::make('offsetY')->numeric()->default(170)->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                            ])->icon('heroicon-m-clock'),

                        Block::make('rpm_label')
                            ->schema([
                                TextInput::make('offsetX')->numeric()->default(4)->required(),
                                TextInput::make('offsetY')->numeric()->default(232)->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                            ])->icon('heroicon-m-arrow-path'),

                        Block::make('rpm_text_label')
                            ->schema([
                                TextInput::make('offsetX')->numeric()->default(488)->required(),
                                TextInput::make('offsetY')->numeric()->default(283)->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                            ])->icon('heroicon-m-clock'),

                        Block::make('oil_temp_label')
                            ->schema([
                                TextInput::make('offsetX')->numeric()->default(0)->required(),
                                TextInput::make('offsetY')->numeric()->default(360)->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                            ])->icon('heroicon-m-clock'),

                        Block::make('water_temp_label')
                            ->schema([
                                TextInput::make('offsetX')->numeric()->default(313)->required(),
                                TextInput::make('offsetY')->numeric()->default(360)->required(),
                                ColorPicker::make('color')->default('#32cd32'),
                            ])->icon('heroicon-m-clock')

                    ])->columnSpanFull(),
                FileUpload::make('images')
                    ->label('Theme Images')
                    ->image()
                    ->multiple()
                    ->directory('theme_images')
                    ->maxFiles(5)
                    ->reorderable()
                    ->columnSpanFull(),
                FileUpload::make('background_images')
                    ->label("Background image")
                    ->image()
                    ->directory('background_image')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('favorited_by_count')
                    ->counts('favoritedBy')
                    ->label('Likes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->counts('downloads')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->label('Added at'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated at')
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
