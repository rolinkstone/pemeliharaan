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
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class PerbaikanKerusakanRelationManager extends RelationManager
{
    protected static string $relationship = 'perbaikanKerusakan';
    protected static ?string $recordTitleAttribute = 'nama';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->default(Carbon::now()->format('Y-m-d'))
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id())
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Hidden::make('nama')
                    ->label('Nama Petugas')
                    ->required()
                    ->default(auth()->user()->name)
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextArea::make('kerusakan')
                    ->label('Kerusakan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextArea::make('hasil')
                    ->label('Hasil Perbaikan')
                    ->required()
                    ->maxLength(255),

                Select::make('kesimpulan')
                    ->label('Kesimpulan')
                    ->required()
                    ->options([
                        'Dapat Digunakan Kembali' => 'Dapat Digunakan Kembali',
                        'Perlu Perbaikan Lebih Lanjut' => 'Perlu Perbaikan Lebih Lanjut',
                        'Tidak Dapat Digunakan Kembali' => 'Tidak Dapat Digunakan Kembali',
                        'Dihapus Dari Daftar BMN' => 'Dihapus Dari Daftar BMN',
                    ])
                    ->searchable(),

                Forms\Components\TextArea::make('catatan')
                    ->label('Catatan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\hidden::make('validasi')
                    ->label('Ditujukan Ke')
                    ->default(function ($get, $set) {
                        $disposisi = $this->getOwnerRecord()->disposisikerusakanone;
                        return $disposisi?->ditujukan_ke;
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('kerusakan'),
                Tables\Columns\TextColumn::make('hasil'),
                Tables\Columns\TextColumn::make('kesimpulan'),
                Tables\Columns\TextColumn::make('tanggal'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Validasi field 'validasi'
                        if (empty($data['validasi'])) {
                            // Kirim notifikasi error
                            Notification::make()
                                ->title('Input Perbaikan Gagal')
                                ->body('Disposisi Kabag TU Belum Diinput.')
                                ->danger()
                                ->send();

                            // Lempar ValidationException
                            throw ValidationException::withMessages([
                                'validasi' => 'Disposisi Kabag TU Belum Diinput.',
                            ]);
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Validasi field 'validasi'
                        if (empty($data['validasi'])) {
                            // Kirim notifikasi error
                            Notification::make()
                                ->title('Validasi Gagal')
                                ->body('Field validasi tidak boleh kosong.')
                                ->danger()
                                ->send();

                            // Lempar ValidationException
                            throw ValidationException::withMessages([
                                'validasi' => 'Field validasi tidak boleh kosong.',
                            ]);
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}