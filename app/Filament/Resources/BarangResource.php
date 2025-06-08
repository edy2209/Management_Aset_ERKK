<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive';
    protected static ?string $navigationLabel = 'Tambah Barang';
    protected static ?string $navigationGroup = 'Detail Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category barang')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\Select::make('kbarang_id')
                    ->label('Quality Barang')
                    ->relationship('kbarang', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_barang')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jumlah_barang')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->options([
                        true => 'Boleh Dipinjam',
                        false => 'Inventaris (Tidak Bisa Dipinjam)',
                    ])
                    ->required()
                    ->default(true),
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Barang')
                    ->image()
                    ->directory('barang-images')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kbarang.name')
                    ->label('Quality Barang'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('kode_barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jumlah_barang'),
                Tables\Columns\IconColumn::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => (bool) $record->status_pinjam),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pinjam')
                    ->label('Status Pinjam')
                    ->options([
                        true => 'Boleh Dipinjam',
                        false => 'Tidak Bisa Dipinjam',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }    
}