<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermintaanPrasaranaResource\Pages;
use App\Filament\Resources\PermintaanPrasaranaResource\RelationManagers;
use App\Models\PermintaanPrasarana;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ImportAction;
use App\Imports\BarangImport;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\DB;


class PermintaanPrasaranaResource extends Resource
{
    protected static ?string $model = PermintaanPrasarana::class;

    protected static ?string $navigationGroup = 'PERMINTAAN';
    protected static ?string $navigationLabel = 'Permintaan Prasarana';
    protected static ?string $navigationIcon = 'heroicon-s-building-office';

      // Menambahkan badge dengan jumlah laporan yang belum diproses
      public static function getNavigationBadge(): ?string
      {
          // Periksa role pengguna
          if (Auth::check() && Auth::user()->hasAnyRole(['super_admin', 'kabag_tu', 'pengadaan'])) {
             return static::getModel()::whereDoesntHave('disposisipermintaan')->count();
         }
          return null; // Tidak menampilkan badge jika role tidak sesuai
      }
  
      // Warna badge (opsional)
      public static function getNavigationBadgeColor(): ?string
      {
          return 'danger'; // Warna badge (misalnya: danger, success, warning, primary)
      }
  
      // Menambahkan indicator (titik merah) jika ada laporan yang belum diproses
      public static function getNavigationIndicator(): ?string
      {
          return static::getModel()::whereDoesntHave('disposisipermintaan')->exists() ? '•' : null;
      }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('jenis_laporan')
                ->label('Jenis Laporan')
                ->required()
                ->options([
                    'Permintaan Prasarana' => 'Permintaan Prasarana',
                    'Lainnya' => 'Lainnya',
                        ])
                ->searchable(),
                
                Forms\Components\Textarea::make('uraian_laporan')
                ->label('Uraian Laporan')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('nama')
                ->label('Nama Alat')
                ->required()
                ->maxLength(255),

                Forms\Components\Textarea::make('spesifikasi')
                ->label('Spesifikasi')
                ->required()
                ->maxLength(255),

                Select::make('tipe_alat')
                ->label('Tipe Alat')
                ->required()
                ->options([
                    'Alat Laboratorium' => 'Alat Laboratorium',
                    'Alat Elektronik Perkantoran' => 'Alat Elektronik Perkantoran',
                    'Alat Elektronik Rumah Tangga' => 'Alat Elektronik Rumah Tangga',
                    'Sarana Prasarana Pendukung Bangunan' => 'Sarana Prasarana Pendukung Bangunan',
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
                    return PermintaanPrasarana::generateNoTiket();
                })
                ->disabled()
                ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('no_ticket')
                ->label('NOMOR TIKET')
                ->sortable()
                ->searchable()
                ->html()
                ->formatStateUsing(function ($state, $record) {
                    // Default status
                    $status = 'On Proses';
                
                    // Periksa semua record disposisipermintaan
                    if ($record->pemenuhanpermintaan->isNotEmpty()) {
                        foreach ($record->pemenuhanpermintaan as $pemenuhan) {
                            if (!empty($pemenuhan->hasil)) {
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
                        
                        'Nama: ' . ($record->nama ?? 'N/A') . '<br>' .
                       
                        
                        'Tipe Alat: ' . ($record->tipe_alat ?? 'N/A') . '<br>' .
                        'Tanggal: ' . ($record->tanggal ?? 'N/A');
                })
                ->html() // Aktifkan rendering HTML
                ->sortable()
                ->searchable(),

                Tables\Columns\TextColumn::make('disposisipermintaan.ditujukan_ke')
                ->label('DISPOSISI KABAG TU')
                ->formatStateUsing(function ($record) {
                    return $record->disposisipermintaan->map(function ($item) {
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

                Tables\Columns\TextColumn::make('pemenuhanpermintaan.hasil')
                ->label('TINDAKAN PEMENUHAN')
                ->formatStateUsing(function ($record) {
                    return $record->pemenuhanpermintaan->map(function ($item) {
                        return 
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
                    ->options(PermintaanPrasarana::all()->pluck('jenis_laporan', 'jenis_laporan')->unique())
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
                                $query->whereDoesntHave('disposisipermintaan', function ($subQuery) {
                                    $subQuery->whereNotNull('ditujukan_ke');
                                });
                            } elseif ($data['status'] === 'Selesai') {
                                // Logika untuk status "Selesai"
                                $query->whereHas('disposisipermintaan', function ($subQuery) {
                                    $subQuery->whereNotNull('ditujukan_ke');
                                });
                            }
                        }
                    }),
                   
                ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(function () {
                        // Ubah label berdasarkan role pengguna
                        if (auth()->user()->hasRole('pengadaan')) {
                            return 'Tindaklanjut';
                        }
                        return 'Tindaklanjut';
                    })
                 
                    ->visible(function ($record) {
                        $user = auth()->user();

                        // Jika pengguna adalah super_admin atau verifikator, tombol selalu muncul
                        if ($user->hasAnyRole(['super_admin', 'kabag_tu'])) {
                            return true;
                        }

                        // Jika pengguna adalah teknisi, tombol hanya muncul jika ditujukan_ke sudah ada record
                        if ($user->hasRole('pengadaan')  ) {
                            return $record->disposisipermintaan->contains(function ($disposisi) {
                                return !empty($disposisi->ditujukan_ke);
                            });
                        }

                        // Default: tombol tidak muncul
                        return false;
                    }),
                    
                Tables\Actions\DeleteAction::make()
                ->visible(function ($record) {
                    $hasDisposisi = $record->disposisipermintaan->contains(function ($disposisi) {
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
            
                // Jika pengguna bukan super_admin, verifikator, atau teknisi, filter data berdasarkan user_id
                if (!$user->hasRole('super_admin') && !$user->hasRole('kabag_tu') && !$user->hasRole('teknisi')&& !$user->hasRole('pengadaan')) {
                    $query->where('user_id', $user->id);
                }
                // Jika super_admin, verifikator, atau teknisi, tidak perlu filter (lihat semua data)
            });
    }

    public static function getRelations(): array
    {
        $relations = [];
    
        // Periksa apakah pengguna sudah login
        if (auth()->check()) {
            // Tambahkan relasi DisposisiKerusakanRelationManager untuk super_admin atau verifikator
            if (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('kabag_tu')) {
                $relations[] = RelationManagers\DisposisiPermintaanRelationManager::class;
            }
    
            // Tambahkan relasi PerbaikanKerusakanRelationManager untuk teknisi dan super_admin
            if (auth()->user()->hasRole('pengadaan') || auth()->user()->hasRole('super_admin')) {
                // Periksa apakah ada DisposisiKerusakan dengan ditujukan_ke yang tidak kosong
                $hasDisposisi = \App\Models\DisposisiPermintaan::whereNotNull('ditujukan_ke')->exists();
    
                if ($hasDisposisi) {
                    $relations[] = RelationManagers\PemenuhanPermintaanRelationManager::class;
                }
            }
        }
    
        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermintaanPrasaranas::route('/'),
            'create' => Pages\CreatePermintaanPrasarana::route('/create'),
            'edit' => Pages\EditPermintaanPrasarana::route('/{record}/edit'),
        ];
    }
}
