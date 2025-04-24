<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KendaraanDinasResource\Pages;
use App\Filament\Resources\KendaraanDinasResource\RelationManagers;
use App\Models\KendaraanDinas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DateTimePicker;

class KendaraanDinasResource extends Resource
{
    protected static ?string $model = KendaraanDinas::class;
    protected static ?string $pluralLabel = 'Peminjaman Kendaraan Dinas';
    protected static ?string $navigationGroup = 'ASET';
    protected static ?string $navigationLabel = 'Kendaraan Dinas';
    protected static ?string $navigationIcon = 'heroicon-s-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('no_st')
                ->label('No Surat Tugas')
                ->required()
                ->maxLength(255)
                ->dehydrated(),

                Forms\Components\DateTimePicker::make('tanggal')
                ->label('Tanggal dan Jam')
                ->required()
                ->default(now()) // Nilai default adalah waktu saat ini
                ->displayFormat('d/m/Y H:i') // Format tampilan tanpa detik
                ->seconds(false) // Nonaktifkan detik di picker
                ->withoutSeconds(), // Pastikan detik tidak disertakan

                Forms\Components\Select::make('tujuan')
                ->label('Tujuan')
                ->required()
                ->options([
                    'Kabupaten Barito Selatan' => 'Kabupaten Barito Selatan',
                    'Kabupaten Barito Timur' => 'Kabupaten Barito Timur',
                    'Kabupaten Barito Utara' => 'Kabupaten Barito Utara',
                    'Kabupaten Gunung Mas' => '	Kabupaten Gunung Mas',
                    'Kabupaten Kapuas' => 'Kabupaten Kapuas',
                    'Kabupaten Katingan' => 'Kabupaten Katingan',
                    'Kabupaten Kotawaringin Barat' => 'Kabupaten Kotawaringin Barat',
                    'Kabupaten Kotawaringin Timur' => 'Kabupaten Kotawaringin Timur',
                    'Kabupaten Lamandau' => 'Kabupaten Lamandau',
                    'Kabupaten Murung Raya' => 'Kabupaten Murung Raya',
                    'Kabupaten Pulang Pisau' => 'Kabupaten Pulang Pisau',
                    'Kabupaten Seruyan' => 'Kabupaten Seruyan',
                    'Kabupaten Sukamara' => 'Kabupaten Sukamara',
                    'Kota Palangka Raya' => 'Kota Palangka Raya',
                    'Lainnya' => 'Lainnya',

                        ])
                ->searchable(), 


                Forms\Components\Select::make('jenis_kendaraan')
                ->label('Jenis Kendaraan')
                ->required()
                ->options([
                    'Roda 2' => 'Roda 2',
                    'Roda 4' => 'Roda 4',
                ])
                ->searchable()
                ->reactive(), // Tambahkan reactive() agar bisa memantau perubahan nilai

                

                Forms\Components\Select::make('driver')
                ->label('Apakah Menggunakan Driver?')
                ->options([
                    'Ya' => 'Ya',
                    'Tidak' => 'Tidak',
                ])
                ->hidden(fn (callable $get) => $get('jenis_kendaraan') !== 'Roda 4') // Muncul hanya jika Roda 4 dipilih
                ->searchable(),


                Forms\Components\Hidden::make('user_id')
                ->default(auth()->id())
                ->disabled()
                ->dehydrated(),

                Forms\Components\Hidden::make('nama')
                ->label('Nama PIC')
                ->required()
                ->default(auth()->user()->name) // Mengambil nama pengguna yang sedang login
                ->disabled()
                ->dehydrated(), // Memast

                Forms\Components\Hidden::make('fungsi')
                ->label('Nama PIC')
                ->required()
                ->default(auth()->user()->fungsi) // Mengambil nama pengguna yang sedang login
                ->disabled()
                ->dehydrated(), // Memast
            // Jika ingin bisa dicari
                        
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('STATUS')
                ->sortable()
                ->searchable()
                ->html() // Aktifkan rendering HTML
                ->formatStateUsing(function ($state, $record) {
                    // Default status
                    $status = 'Waiting';
                
                    // Periksa semua record permintaandriverdetail
                    if ($record->kendaraandinasdetail->isNotEmpty()) {
                        foreach ($record->kendaraandinasdetail as $detail) {
                            if (!empty(trim($detail->nama_driver))) {
                                $status = 'Driver : ' . $detail->nama_driver;
                                break; // Jika ditemukan satu record yang terisi, status diubah
                            }
                        }
                    }
                
                    // Tentukan warna dan class blinking
                    $color = $status === 'Menunggu Persetujuan' ? 'orange' : 'green';
                    $blinkingClass = $status === 'Waiting' ? 'blinking' : ''; // Hanya berkedip jika status "Waiting"
                
                    // Format tampilan
                    return '<strong>'  . '</strong><br><span style="color: ' . $color . ';" class="' . $blinkingClass . '">' . $status . '</span>';
                }),
                Tables\Columns\TextColumn::make('no_st')
                ->label('IDENTITAS PENGGUNA')
                ->formatStateUsing(function ($record) {
                    // Gabungkan nilai dari field-field yang diinginkan
                    return 
                        'No ST: ' . ($record->no_st ?? 'N/A') . '<br>' .
                        'PIC: ' . ($record->nama ?? 'N/A') . '<br>' .
                        'Fungsi: ' . ($record->fungsi ?? 'N/A') . '<br>' .
                       
                        'Waktu Penggunaan: ' . ($record->tanggal ?? 'N/A');
                })
                ->html() // Aktifkan rendering HTML
                ->sortable()
                ->searchable(),

                Tables\Columns\TextColumn::make('kendaraanDinasDetail.kendaraan')
                ->label('KENDARAAN')
                ->formatStateUsing(function ($record) {
                    $kendaraan = $record->kendaraanDinasDetail->first()?->kendaraan ?? 'N/A';

                    return 
                        ' ' . $kendaraan . '<br>' .
                        'Menggunakan Driver: ' . (($record->driver ?? false) ? 'Ya' : 'Tidak') . '<br>' .
                        'Tujuan: ' . ($record->tujuan ?? 'N/A');
                })
                ->html()
                ->sortable()
                ->searchable(),
           
               
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   //
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
            
                // Jika pengguna bukan super_admin, verifikator, atau teknisi, filter data berdasarkan user_id
                if (!$user->hasRole('super_admin') && !$user->hasRole('admin_driver')) {
                    $query->where('user_id', $user->id);
                }
            
            });
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\KendaraanDinasDetailRelationManager::class,
           
        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKendaraanDinas::route('/'),
            'create' => Pages\CreateKendaraanDinas::route('/create'),
            'edit' => Pages\EditKendaraanDinas::route('/{record}/edit'),
        ];
    }
}
