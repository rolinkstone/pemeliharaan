<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanKerusakanResource\Pages;
use App\Filament\Resources\LaporanKerusakanResource\RelationManagers;
use App\Models\Barang;
use App\Models\LaporanKerusakan;
use App\Models\DisposisiKerusakan;
use App\Models\PerbaikanKerusakan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ImportAction;
use App\Imports\BarangImport;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use App\Models\User;

class LaporanKerusakanResource extends Resource
{
    protected static ?string $model = LaporanKerusakan::class;
    protected static ?string $pluralLabel = 'Pengaduan Kerusakan';
    protected static ?string $navigationGroup = 'ASET';
    protected static ?string $navigationLabel = 'Kerusakan';
    protected static ?string $navigationIcon = 'heroicon-s-wrench-screwdriver';

     // Menambahkan badge dengan jumlah laporan yang belum diproses
     public static function getNavigationBadge(): ?string
        {
            if (!Auth::check()) {
                return null;
            }

            $query = static::getModel()::whereDoesntHave('disposisikerusakan');

            if (Auth::user()->hasRole('super_admin')) {
                // super_admin: tampilkan semua data yang belum punya disposisikerusakan
                return $query->count();
            }

            if (Auth::user()->hasRole('kabag_tu')) {
                $query->where(function ($q) {
                    $q->whereIn('kabag_tu_id', [0, 1]);

                    if (Auth::user()->hasRole('katim')) {
                        $q->orWhere('katim_id', Auth::id());
                    }
                });

                return $query->count();
            }

            if (Auth::user()->hasRole('katim')) {
                $query->where('katim_id', Auth::id());
                return $query->count();
            }

            return null;
        }


 
     // Warna badge (opsional)
     public static function getNavigationBadgeColor(): ?string
     {
         return 'danger'; // Warna badge (misalnya: danger, success, warning, primary)
     }
 
     // Menambahkan indicator (titik merah) jika ada laporan yang belum diproses
     public static function getNavigationIndicator(): ?string
     {
         return static::getModel()::whereDoesntHave('disposisikerusakan')->exists() ? '•' : null;
     }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('jenis_laporan')
                ->label('Jenis Laporan')
                ->required()
                ->options([
                    'Kerusakan' => 'Kerusakan',
                    'Lainnya' => 'Lainnya',
                        ])
                ->searchable(), // Jika ingin bisa dicari

                Forms\Components\Textarea::make('uraian_laporan')
                ->label('Uraian Laporan')
                ->required()
                ->maxLength(255),

                Select::make('jenis_barang')
                ->label('Jenis Barang')
                ->required()
                ->options(Barang::distinct()->pluck('jenis_barang', 'jenis_barang')->toArray()) // Pastikan hanya jenis unik
                ->searchable()
                ->live() // Pastikan perubahan langsung diproses
                ->extraAttributes(['wire:key' => 'jenis_barang']), // Mencegah wire issue di Livewire

                Select::make('nama')
                ->label('Nama Barang')
                ->required()
                ->options(function (callable $get) {
                    $jenisBarang = $get('jenis_barang');

                    if (!$jenisBarang) {
                        return [];
                    }

                    // Menggunakan nama dan nup sebagai value
                    return Barang::where('jenis_barang', $jenisBarang)
                        ->pluck(DB::raw("CONCAT(nama, ' - NUP : ', nup)"), 'id'); // Gabungkan nama dan nup
                })
                ->reactive() // Memastikan perubahan di-refresh otomatis
                ->disabled(fn (callable $get) => !$get('jenis_barang'))
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $barang = Barang::where('nama', $state)->first(); // Mencari barang berdasarkan nama

                        if ($barang) {
                            // Set kode_barang berdasarkan barang yang dipilih
                            $set('kode_barang', $barang->kode_barang);
                            // Set lokasi/ruangan berdasarkan barang yang dipilih
                            $set('ruangan', $barang->ruangan);
                        }
                    } else {
                        // Reset jika tidak ada pilihan
                        $set('kode_barang', ''); 
                        $set('ruangan', ''); // Reset lokasi
                    }
                }),

                        
              
                Forms\Components\TextInput::make('kode_barang')
                ->label('Kode Barang')
                ->required()
                ->maxLength(255)
                ->disabled()
                ->dehydrated(), // Memastikan nilai tetap tersimpan ke database, // Menonaktifkan input agar tidak bisa diubah, // Nonaktifkan input kode_barang agar tidak bisa diubah manual

                Forms\Components\TextInput::make('ruangan')
                ->label('Lokasi')
                ->required()
                ->maxLength(255)
                ->disabled()
                ->dehydrated(), // Memastikan nilai tetap tersimpan ke database, // Menonaktifkan input agar tidak bisa diubah, // Nonaktifkan input ruangan agar tidak bisa diubah manual

                Select::make('tipe_alat')
                ->label('Tipe Alat')
                ->required()
                ->options([
                    'Alat Laboratorium' => 'Alat Laboratorium',
                    'Alat Elektronik Perkantoran' => 'Alat Elektronik Perkantoran',
                    'Alat Elektronik Rumah Tangga' => 'Alat Elektronik Rumah Tangga',
                    'Sarana Prasarana Pendukung Bangunan' => 'Sarana Prasarana Pendukung Bangunan',
                    'Kendaraan Roda 4' => 'Kendaraan Roda 4',
                    'Kendaraan Roda 2' => 'Kendaraan Roda 2',
                    'Lainnya' => 'Lainnya',
                         ])
                ->searchable(), // Jika ingin bisa dicari

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

                Forms\Components\Hidden::make('nama_pelapor')
                ->label('Nama Pelapor')
                ->required()
                ->default(auth()->user()->name) // Mengambil nama pengguna yang sedang login
                ->disabled()
                ->dehydrated(), // Memastikan nilai tetap tersimpan ke database, // Menonaktifkan input agar tidak bisa diubah

                Forms\Components\Hidden::make('no_ticket')
                ->default(function () {
                    return LaporanKerusakan::generateNoTiket();
                })
                ->disabled()
                ->dehydrated(),
                Forms\Components\Select::make('katim_id')
                ->label('Ketua TIM Kerja')
                ->options(function () {
                            return User::whereHas('roles', function ($query) {
                                $query->where('name', 'katim'); // Ambil user dengan role "katim"
                            })->pluck('name', 'id'); // Ambil nama user sebagai label, id sebagai value
                        })
                ->required()
                ->disabled(fn (string $operation): bool => $operation === 'edit')
                ->searchable(),


                Forms\Components\Select::make('kabag_tu_id')
                ->label('Persetujuan Katim')
                ->options([
                    1 => 'Ya',   // Nilai 1 untuk "Ya"
                    0 => 'Tidak', // Nilai 0 untuk "Tidak"
                ])
                ->visible(function () {
                    return auth()->user()->hasRole('katim'); // Hanya muncul untuk role 'katim'
                }),

            ]);
            
    }

    public static function table(Table $table): Table
    {
        return $table
        ->query(
            static::getEloquentQuery()
                ->when(auth()->user()->hasRole('super_admin'), fn ($query) => $query)
                ->when(auth()->user()->hasRole('kabag_tu'), function ($query) {
                    $query->where(function ($q) {
                        $q->whereIn('kabag_tu_id', [0, 1]);
        
                        // Jika juga katim, tampilkan juga data dia sebagai katim
                        if (auth()->user()->hasRole('katim')) {
                            $q->orWhere('katim_id', auth()->id());
                        }
                    });
                })
                ->when(
                    auth()->user()->hasRole('katim') && !auth()->user()->hasRole('kabag_tu'),
                    fn ($query) => $query->where('katim_id', auth()->id())
                )
        )
        
        
        
        
            ->columns([
                TextColumn::make('no_ticket')
                ->label('NOMOR TIKET')
                ->sortable()
                ->searchable()
                ->html()
                ->formatStateUsing(function ($state, $record) {
                    // Default status
                    $status = 'On Proses';
                
                    // Periksa semua record disposisikerusakan
                    if ($record->perbaikankerusakan->isNotEmpty()) {
                        foreach ($record->perbaikankerusakan as $perbaikan) {
                            if (!empty($perbaikan->kerusakan)) {
                                $status = 'Selesai';
                                break; // Jika ditemukan satu record yang terisi, status Selesai
                            }
                        }
                    }
                    
                    // Format tampilan
                    return '<strong>' . $state . '</strong><br><span style="color: ' . ($status === 'On Proses' ? 'orange' : 'green') . ';">' . $status . '</span>';
                }),
                

                Tables\Columns\TextColumn::make('jenis_laporan')
                ->label('IDENTITAS SARANA PRASARANA')
                ->formatStateUsing(function ($record) {
                    // Gabungkan nilai dari field-field yang diinginkan
                    return 
                        'Jenis Laporan: ' . ($record->jenis_laporan ?? 'N/A') . '<br>' .
                        'Uraian Laporan: <div style="white-space: normal; width: 300px;">' . ($record->uraian_laporan ?? 'N/A') . '</div><br>' .
                        'Jenis Barang: ' . ($record->jenis_barang ?? 'N/A') . '<br>' .
                        'Nama: ' . ($record->nama ?? 'N/A') . '<br>' .
                        'Kode Barang: ' . ($record->kode_barang ?? 'N/A') . '<br>' .
                        'Ruangan: ' . ($record->ruangan ?? 'N/A') . '<br>' .
                        'Tipe Alat: ' . ($record->tipe_alat ?? 'N/A') . '<br>' .
                        'Tanggal: ' . ($record->tipe_alat ?? 'N/A') . '<br>' .'<br>'.

                       'Persetujuan Kabag/Katim: ' . ($record->kabag_tu_id == 1 
                                                ? 'Ya' 
                                                : ($record->kabag_tu_id == 0 
                                                    ? '-' 
                                                    : 'Belum diproses Katim'));
                })
                ->html() // Aktifkan rendering HTML
                ->sortable()
                ->searchable(),

               
                Tables\Columns\TextColumn::make('disposisikerusakan.ditujukan_ke')
                ->label('DISPOSISI KABAG TU')
                ->formatStateUsing(function ($record) {
                    return $record->disposisikerusakan->map(function ($item) {
                        return 
                            'Ditujukan Ke: ' . ($item->ditujukan_ke ?? 'N/A') . '<br>' .
                            'Isi: ' . ($item->isi ?? 'N/A') . '<br>' .'<br>' .
                            '<strong>TINDAK LANJUT</strong><br>' .
                            'Diserahkan: ' . ($item->diserahkan ?? 'N/A') . '<br>' .
                            'Tanggal: ' . ($item->tanggal ?? 'N/A');
                    })->implode('<hr>'); // Pemisah antar disposisi
                })
                ->html()
                ->sortable()
                ->searchable(),

                Tables\Columns\TextColumn::make('perbaikankerusakan.kerusakan')
                ->label('TINDAKAN PERBAIKAN')
                ->formatStateUsing(function ($record) {
                    return $record->perbaikankerusakan->map(function ($item) {
                        return 
                            'Kerusakan: ' . ($item->kerusakan ?? 'N/A') . '<br>' .
                            'Hasil: ' . ($item->hasil ?? 'N/A') . '<br>' .'<br>' .
                            'Tanggal: ' . ($item->tanggal ?? 'N/A') . '<br>' .'<br>' .
                             'Nama Petugas: ' . ($item->nama ?? 'N/A') . '<br>' .'<br>' .
                           '<strong>PENYELESAIAN TINDAK LANJUT </strong><br>' .
                            'kesimpulan: ' . ($item->kesimpulan ?? 'N/A') . '<br>' .
                            'catatan: ' . ($item->catatan ?? 'N/A');
                    })->implode('<hr>'); // Pemisah antar disposisi
                })
                ->html()
                ->sortable()
                ->searchable(),
               
            ])
            ->filters([
                Filter::make('jenis_laporan')
                    ->form([
                        Grid::make(20)
                            ->schema([
                                Select::make('jenis_laporan')
                                    ->label('Select Jenis Laporan')
                                    ->options(LaporanKerusakan::all()->pluck('jenis_laporan', 'jenis_laporan')->unique())
                                    ->placeholder('Select Jenis Laporan')
                                    ->columnSpan(10)
                                    ->searchable()
                                    ->reactive(),

                                // Tambahkan filter status
                                Select::make('status')
                                    ->label('Select Status')
                                    ->options([
                                        'On Proses' => 'On Proses',
                                        'Selesai' => 'Selesai',
                                    ])
                                    ->placeholder('Select Status')
                                    ->columnSpan(10)
                                    ->searchable()
                                    ->reactive(),
                                            ]),
                                    ])
                                    ->query(function (Builder $query, array $data) {
                                        // Filter berdasarkan jenis_laporan
                                        if (!empty($data['jenis_laporan'])) {
                                            $query->where('jenis_laporan', $data['jenis_laporan']);
                                        }

                                        // Filter berdasarkan status
                                        if (!empty($data['status'])) {
                                            if ($data['status'] === 'On Proses') {
                                                // Logika untuk status "On Proses"
                                                $query->whereDoesntHave('perbaikankerusakan', function ($subQuery) {
                                                    $subQuery->whereNotNull('kesimpulan');
                                                });
                                            } elseif ($data['status'] === 'Selesai') {
                                                // Logika untuk status "Selesai"
                                                $query->whereHas('perbaikankerusakan', function ($subQuery) {
                                                    $subQuery->whereNotNull('kesimpulan');
                                                });
                                            }
                                        }
                                    }),
                                
                                ], layout: FiltersLayout::AboveContent)

              

                ->actions([
                    Tables\Actions\EditAction::make()
                    ->label(function () {
                        // Ubah label berdasarkan role pengguna
                        if (auth()->user()->hasRole('teknisi')) {
                            return 'Perbaikan';
                        }
                        return 'Disposisi';
                    })
                 
                    ->visible(function ($record) {
                        $user = auth()->user();

                        // Jika pengguna adalah super_admin atau verifikator, tombol selalu muncul
                        if ($user->hasAnyRole(['super_admin', 'kabag_tu', 'katim'])) {
                            return true;
                        }

                        // Jika pengguna adalah teknisi, tombol hanya muncul jika ditujukan_ke sudah ada record
                        if ($user->hasRole('teknisi')) {
                            return $record->disposisikerusakan->contains(function ($disposisi) {
                                return !empty($disposisi->ditujukan_ke);
                            });
                        }

                        // Default: tombol tidak muncul
                        return false;
                    }),
                    
                   
                    Tables\Actions\Action::make('ticket')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer') // Hanya menampilkan ikon printer
                    ->url(fn ($record) => route('ticket-pdf', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->color('success'),
                                
                Tables\Actions\DeleteAction::make()
                        ->visible(function ($record) {
                            $hasDisposisi = $record->disposisikerusakan->contains(function ($disposisi) {
                                return !empty($disposisi->ditujukan_ke);
                            });
                            return !$hasDisposisi;
                        }),
                ])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                       //
                    ]),
                ])
                ->modifyQueryUsing(function (Builder $query) {
                    $user = auth()->user();
                
                    // Jika bukan super_admin atau kabag_tu, hanya tampilkan data berdasarkan katim_id
                    if (!$user->hasRole('super_admin') && !$user->hasRole('kabag_tu')) {
                        $query->where('katim_id', $user->id);
                    }
                
                    // Jika super_admin atau kabag_tu, tampilkan semua data
                });
                
    }

    public static function getRelations(): array
    {
        $relations = [];
    
        if (auth()->check()) {
            $user = auth()->user();
    
            // Ambil record yang sedang diedit/dilihat
            $record = request()->route('record'); // Ini akan berisi ID atau model, tergantung routing
    
            // Ambil instance model LaporanKerusakan
            $laporanKerusakan = $record instanceof \App\Models\LaporanKerusakan
                ? $record
                : \App\Models\LaporanKerusakan::find($record);
    
            // Tampilkan DisposisiKerusakan jika role sesuai dan kabag_tu_id == 1
            if (
                $user->hasRole('super_admin') || $user->hasRole('kabag_tu')
            ) {
                if ($laporanKerusakan && $laporanKerusakan->kabag_tu_id == 1) {
                    $relations[] = RelationManagers\DisposisiKerusakanRelationManager::class;
                }
            }
    
            // Tampilkan PerbaikanKerusakan jika teknisi/super_admin dan ada disposisi
            if ($user->hasRole('teknisi') || $user->hasRole('super_admin')) {
                $hasDisposisi = \App\Models\DisposisiKerusakan::whereNotNull('ditujukan_ke')->exists();
    
                if ($hasDisposisi) {
                    $relations[] = RelationManagers\PerbaikanKerusakanRelationManager::class;
                }
            }
        }
    
        return $relations;
    }
    
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanKerusakans::route('/'),
            'create' => Pages\CreateLaporanKerusakan::route('/create'),
            'edit' => Pages\EditLaporanKerusakan::route('/{record}/edit'),
           
        ];
    }
}
