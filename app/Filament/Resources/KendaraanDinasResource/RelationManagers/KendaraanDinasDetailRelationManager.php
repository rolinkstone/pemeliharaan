<?php

namespace App\Filament\Resources\KendaraanDinasResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\KendaraanDinas;
use Filament\Forms\Components\TextInput;
use App\Models\Barang;



class KendaraanDinasDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'KendaraanDinasDetail';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                

                Forms\Components\Select::make('driver')
                ->label('Apakah Menggunakan Driver?')
                ->options([
                    'Ya' => 'Ya',
                    'Tidak' => 'Tidak',
                ])
                ->default(function ($record) {
                    // Ambil nilai Driver dari relasi KendaraanDinas
                    return $record->KendaraanDinas->Driver ?? null;
                }),

                Forms\Components\Select::make('nama_driver')
                ->label('Nama Driver')
                ->options([
                    'Agus' => 'Agus',
                    'Saipul' => 'Saipul',
                    'Bobby' => 'Bobby',
                    'Ucup' => 'Ucup',
                    'Lainnya' => 'Lainnya',
                ])
                ->searchable(),

                Forms\Components\Select::make('kendaraan')
                ->label('Kendaraan')
                ->options(fn () => 
                    Barang::where('jenis_barang', 'Kendaraan Roda 4')
                        ->pluck('nama', 'nama') // display & store as nama
                )
                ->searchable(),
               

                // Jika ingin bisa dicari
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('nama_driver')->label('NAMA DRIVER')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kendaraan')->label('KENDARAAN YANG DIGUNAKAN')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
