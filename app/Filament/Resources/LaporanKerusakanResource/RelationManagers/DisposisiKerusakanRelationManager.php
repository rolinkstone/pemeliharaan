<?php

namespace App\Filament\Resources\LaporanKerusakanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Forms\Components\Select;

class DisposisiKerusakanRelationManager extends RelationManager
{
    protected static string $relationship = 'DisposisiKerusakan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ditujukan_ke')
                ->label('Ditujukan Ke')
                ->required()
                ->maxLength(255),

                Forms\Components\Textarea::make('isi')
                ->label('isi')
                ->required()
                ->maxLength(255),

                Forms\Components\Hidden::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(Carbon::now()->format('Y-m-d'))
                ->disabled()
                ->dehydrated(),

                Select::make('diserahkan')
                ->label('Diserahkan Kepada')
                ->required()
                ->options([
                    'TIM TI' => 'TIM TI',
                    'Penyedia Jasa' => 'Penyedia Jasa',
                    'Teknisi Internal' => 'Teknisi Internal',
                        ])
                ->searchable(), // Jika ingin bisa dicari
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('ditujukan_ke'),
                Tables\Columns\TextColumn::make('isi'),
                Tables\Columns\TextColumn::make('diserahkan'),
                Tables\Columns\TextColumn::make('tanggal'),
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
