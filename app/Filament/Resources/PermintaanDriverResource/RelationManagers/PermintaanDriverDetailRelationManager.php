<?php

namespace App\Filament\Resources\PermintaanDriverResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;

class PermintaanDriverDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'PermintaanDriverDetail';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('nama_driver')
                ->label('Nama Driver')
                ->required()
                ->options([
                    'Agus' => 'Agus',
                    'Saipul' => 'Saipul',
                    'Bobby' => 'Bobby',
                    'Ucup' => 'Ucup',
                    'Lainnya' => 'Lainnya',
                   
                        ])
                ->searchable(), // Jika ingin bisa dicari
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            
            ->columns([
                Tables\Columns\TextColumn::make('nama_driver')->label('NAMA DRIVER')->sortable()->searchable(),
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
