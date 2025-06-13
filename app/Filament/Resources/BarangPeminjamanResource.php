<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangPeminjamanResource\Pages;
use App\Filament\Resources\BarangPeminjamanResource\RelationManagers;
use App\Models\BarangPeminjaman;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;

class BarangPeminjamanResource extends Resource
{
    protected static ?string $model = BarangPeminjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationGroup = 'Peminjaman dan Pengembalian';
    protected static ?string $navigationLabel = 'Daftar Peminjaman Barang';
    protected static ?string $pluralModelLabel = 'Daftar Pinjaman User';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('created_at', 'desc') // Paling baru di atas
            ->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'disetujui' THEN 2 
                WHEN status = 'ditolak' THEN 3 
                ELSE 4 END");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('peminjam_id')->required(),
                Forms\Components\TextInput::make('barang_id')->required(),
                Forms\Components\TextInput::make('jumlah')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Forms\Components\DatePicker::make('tanggal_pinjam')
                    ->required()
                    ->displayFormat('d M Y')
                    ->minDate(now()),
                Forms\Components\DatePicker::make('tanggal_pengembalian')
                    ->displayFormat('d M Y')
                    ->minDate(now()),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([                
                Tables\Columns\TextColumn::make('peminjam_id'),
                Tables\Columns\ImageColumn::make('barang.image')
                    ->label('Gambar')
                    ->disk('public'),
                Tables\Columns\TextColumn::make('peminjam.name')
                    ->label('Nama Peminjam')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barang.name')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barang.kode_barang')
                    ->label('Kode Barang'),
                Tables\Columns\TextColumn::make('kbarang.name')
                    ->label('Kualitas Barang'),
                Tables\Columns\TextColumn::make('jumlah'),
                Tables\Columns\TextColumn::make('tanggal_pinjam')
                    ->dateTime('d M Y'),
                Tables\Columns\TextColumn::make('tanggal_pengembalian')
                    ->dateTime('d M Y'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Atasan (wajib atasan konfrimasi)')
                    ->enum([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ])
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ])
                    ->icons([
                        'pending' => 'heroicon-o-clock',
                        'disetujui' => 'heroicon-o-check-circle',
                        'ditolak' => 'heroicon-o-x-circle',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->before(function ($record, Tables\Actions\EditAction $action) {
                        if ($record->status !== 'pending') {
                            Notification::make()
                                ->title('Peringatan')
                                ->body('Hanya peminjaman dengan status pending yang dapat diubah!')
                                ->warning()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record, Tables\Actions\DeleteAction $action) {
                        if ($record->status === 'pending') {
                            Notification::make()
                                ->title('Peringatan')
                                ->body('Mohon selesaikan proses peminjaman terlebih dahulu (setujui atau tolak) sebelum menghapus, karena dapat mempengaruhi jumlah stok barang!')
                                ->warning()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
                
                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->status = 'disetujui';
                        $record->save();
                        
                        Notification::make()
                            ->title('Peminjaman Disetujui')
                            ->body('Peminjaman barang telah disetujui.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Peminjaman')
                    ->modalSubheading('Apakah Anda yakin ingin menyetujui peminjaman ini?')
                    ->modalButton('Ya, Setujui'),
                
                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        // Kembalikan stok barang saat peminjaman ditolak
                        $barang = $record->barang;
                        if ($barang) {
                            $barang->jumlah_barang += $record->jumlah;
                            $barang->save();
                        }

                        $record->status = 'ditolak';
                        $record->save();
                        
                        Notification::make()
                            ->title('Peminjaman Ditolak')
                            ->body('Peminjaman barang telah ditolak dan stok barang telah dikembalikan.')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Peminjaman')
                    ->modalSubheading('Apakah Anda yakin ingin menolak peminjaman ini? Stok barang akan dikembalikan.')
                    ->modalButton('Ya, Tolak'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records, Tables\Actions\DeleteBulkAction $action) {
                        $hasPending = $records->contains('status', 'pending');
                        
                        if ($hasPending) {
                            Notification::make()
                                ->title('Peringatan')
                                ->body('Tidak dapat menghapus peminjaman yang masih dalam status pending (di karenakan bisa mengurangi stok sistem). Mohon selesaikan terlebih dahulu!')
                                ->warning()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
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
            'index' => Pages\ListBarangPeminjamen::route('/'),
            'create' => Pages\CreateBarangPeminjaman::route('/create'),
            'edit' => Pages\EditBarangPeminjaman::route('/{record}/edit'),
        ];
    }    
}