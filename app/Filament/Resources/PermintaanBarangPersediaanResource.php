<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermintaanBarangPersediaanResource\Pages;
use App\Filament\Resources\PermintaanBarangPersediaanResource\RelationManagers;
use App\Models\PermintaanBarangPersediaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Models\BarangPersediaan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;


class PermintaanBarangPersediaanResource extends Resource
{
    protected static ?string $model = PermintaanBarangPersediaan::class;
    protected static ?string $navigationGroup = 'PERSEDIAAN';
    protected static ?string $navigationLabel = 'Permintaan';
    protected static ?string $navigationIcon = 'heroicon-s-document-text';
   
    
    // Menambahkan badge dengan jumlah laporan yang belum diproses atau belum memiliki kabag_tu_id
    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user(); // Ambil pengguna yang sedang login
    
        // Cek jika pengguna memiliki role tertentu
       
    
        if ($user->hasRole('katim')) {
            return (string) static::getModel()::whereNotNull('katim_id')
                ->whereNull('kabag_tu_id')
                ->count();
        }
    
        if ($user->hasRole('kabag_tu')) {
            return (string) static::getModel()::whereNull('gudang_id')
                ->whereNotNull('kabag_tu_id')
                ->count();
        }
    
        if ($user->hasRole('admin_gudang')) {
            return (string) static::getModel()::whereNull('diserahkan_id')
                ->whereNotNull('gudang_id')
                ->count();
        }
        
        return '0'; // Jika tidak termasuk role di atas, tetap tampil "0"
    }

        // Warna badge (opsional)
     public static function getNavigationBadgeColor(): ?string
     {
         return 'danger'; // Warna badge (misalnya: danger, success, warning, primary)
     }

   
   

    public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Forms\Components\hidden::make('fungsi')
                    ->label('Nama PIC')
                    ->required()
                    ->default(auth()->user()->fungsi) // Mengambil nama pengguna yang sedang login
                    ->disabled()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(), // Memast
                // Jika ingin bisa dicari

                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->required()
                        ->displayFormat('d/m/Y') 
                        ->disabled(fn (string $operation): bool => $operation === 'edit')// Format tampilan tanggal
                        ->format('Y-m-d'), // Format penyimpanan tanggal di database

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

                    Forms\Components\hidden::make('user_id')
                        ->default(auth()->id())
                        ->disabled()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated(),

                    Forms\Components\hidden::make('nama_pelapor')
                        ->label('Nama Pelapor')
                        ->required()
                        ->default(auth()->user()->name) // Mengambil nama pengguna yang sedang login
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\hidden::make('no_ticket')
                        ->default(function () {
                            return PermintaanBarangPersediaan::generateNoTiket();
                        })
                        ->disabled()
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated(),

                    Repeater::make('items')
                        ->relationship('items') // Menyatakan hubungan dengan model terkait
                        ->schema([
                            Select::make('kategori')
                                ->options(function () {
                                    // Ambil semua kategori unik dari model BarangPersediaan
                                    return BarangPersediaan::distinct()->pluck('kategori', 'kategori');
                                })
                                ->required()
                                ->label('Kategori')
                                ->reactive()
                                ->disabled(fn (string $operation): bool => $operation === 'edit'), // Membuat field ini reactive agar bisa mempengaruhi field lainnya

                                Select::make('nama_barang')
                                ->options(function (callable $get) {
                                    $kategori = $get('kategori');
                                    return $kategori ? BarangPersediaan::where('kategori', $kategori)->pluck('nama_barang', 'nama_barang') : [];
                                })
                                ->required()
                                ->label('Nama Barang')
                                ->searchable()
                                ->reactive()
                                ->disabled(fn (string $operation): bool => $operation === 'edit')
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $barang = BarangPersediaan::where('nama_barang', $state)->first();
                                    if ($barang) {
                                        $set('satuan', $barang->satuan);
                                        $set('barang_persediaan_id', $barang->id); // Update barang_persediaan_id dengan ID barang yang dipilih
                                    }
                                }),

                            TextInput::make('satuan')
                                ->required()
                                ->label('Satuan')
                                ->disabled()
                                ->dehydrated(), // Menonaktifkan field satuan agar tidak bisa diubah manual

                            TextInput::make('jumlah')
                                ->numeric()
                                ->required()
                                ->label('Jumlah'),

                            Forms\Components\hidden::make('status')
                                ->required()
                                ->label('Status')
                                ->default('Out') // Menambahkan nilai default "Out"
                                ->disabled()
                                ->dehydrated(),

                           

                            Forms\Components\hidden::make('barang_persediaan_id')
                            ->required()
                            ->label('ID Barang')
                            ->disabled()
                            ->dehydrated(),

                        ])
                        ->columns(4)
                        ->columnSpan('full')
                        ->label('Daftar Barang')
                        ->createItemButtonLabel('Tambah Barang')
                        ->maxItems(10)
                      
                        ->defaultItems(1),

                        
                        Select::make('kabag_tu_id')
                        ->label('Persetujuan Katim')
                        ->options([
                            1 => 'Ya',   // Nilai 1 untuk "Ya"
                            0 => 'Tidak', // Nilai 0 untuk "Tidak"
                        ])
                        ->visible(function () {
                            return auth()->user()->hasRole('katim'); // Hanya muncul untuk role 'katim'
                        })
                        ->disabled(function ($record) {
                            // Nonaktifkan jika bukan role 'katim' atau jika gudang_id = 1
                            return !auth()->user()->hasRole('katim') || $record->gudang_id == 1;
                        }), // Wajib diisi

                        Select::make('gudang_id')
                        ->label('Persetujuan Kabag TU')
                        ->options([
                            1 => 'Ya',   // Nilai 1 untuk "Ya"
                            0 => 'Tidak', // Nilai 0 untuk "Tidak"
                        ])
                        ->visible(function () {
                            return auth()->user()->hasRole('kabag_tu'); // Hanya muncul untuk role 'kabag_tu'
                        })
                        ->disabled(function ($record) {
                            // Nonaktifkan jika bukan role 'kabag_tu' atau jika diserahkan_id = 1
                            return !auth()->user()->hasRole('kabag_tu') || $record->diserahkan_id == 1;
                        }),
                    

                            Select::make('diserahkan_id')
                            ->label('Sudah diserahkan?')
                            ->options([
                                1 => 'Ya', // Nilai 1 untuk "Ya"
                                0 => 'Tidak', // Nilai 0 untuk "Tidak"
                            ])
                            ->visible(function () {
                                return auth()->user()->hasRole('admin_gudang'); // Hanya muncul untuk role 'katim'
                            })
                            ->disabled(function () {
                                return !auth()->user()->hasRole('admin_gudang'); // Nonaktifkan jika bukan role 'katim'
                            }), // Wajib diisi
                            Forms\Components\DatePicker::make('tanggal_diserahkan')
                            ->label('Tanggal Diserahkan')
                            ->required()
                            ->displayFormat('d/m/Y') 
                            ->disabled(fn (string $operation): bool => $operation === 'edit')// Format tampilan tanggal
                            ->format('Y-m-d')
                            ->visible(function () {
                                return auth()->user()->hasRole('admin_gudang'); // Hanya muncul untuk role 'katim'
                            })
                            ->disabled(function () {
                                return !auth()->user()->hasRole('admin_gudang'); // Nonaktifkan jika bukan role 'katim'
                            }), // Wajib diisi // Format penyimpanan tanggal di database
                ]);
        }

    public static function table(Table $table): Table
    {
        return $table
        ->query(function () {
            return static::getModel()::query()
                ->whereHas('items', function ($query) {
                    $query->where('status', 'Out');
                });
        })
            ->columns([
                Tables\Columns\TextColumn::make('no_ticket')
                ->label('NOMOR')
                ->formatStateUsing(function ($record) {
                    // Tentukan status dan warna
                    if ($record->diserahkan_id == 1) {
                        $status = '<span style="color: green;">Selesai</span>';
                    } else {
                        $status = '<span style="color: gray;">On Proses</span>';
                    }

                    // Gabungkan no_ticket dengan status
                    return "{$record->no_ticket} <br> <small>Status: {$status}</small>";
                })
                ->html() // Aktifkan rendering HTML
                ->sortable()
                ->searchable(),
               
                
                Tables\Columns\TextColumn::make('items.kategori')
                ->label('IDENTITAS BARANG')
                ->formatStateUsing(function ($record) {
                    // Ambil tanggal dari model utama
                    $tanggal = $record->tanggal ? 'Tanggal: ' . $record->tanggal . '<br>' : '';
                    $fungsi = $record->fungsi ? 'Unit: ' . $record->fungsi . '<br><br>' : '';
            
                    // Pastikan relasi 'items' dimuat
                    if ($record->relationLoaded('items') && $record->items->isNotEmpty()) {
                        $itemsData = $record->items->map(function ($item) {
                            return 
                                'Nama Barang: ' . ($item->nama_barang ?? 'N/A') . '<br>' .
                                sprintf('Jumlah: %s %s', ($item->jumlah ?? 'N/A'), ($item->satuan ?? 'N/A'));
                        })->implode('<hr>'); // Pisahkan setiap item dengan garis horizontal
            
                        return $tanggal . $fungsi . $itemsData;
                    }
            
                    return $tanggal . 'Tidak ada data';
                })
                ->html() // Aktifkan rendering HTML
                ->sortable()
                ->searchable(),
                
                Tables\Columns\TextColumn::make('kabag_tu_id')
                ->label('VALIDASI')
                ->html() // Aktifkan rendering HTML
                ->sortable()
                ->searchable()
                ->formatStateUsing(function ($record) {
                    $status = '';

                  // Validasi oleh Katim
                  if ($record->kabag_tu_id == 1) {
                    $status .= '<span class="text-green-500 font-bold"> diverifikasi oleh Katim ✅</span><br>';
                    } elseif ($record->kabag_tu_id === 0) {
                        $status .= '<span class="text-red-500 font-bold"> diverifikasi oleh Katim ❌</span><br>';
                    } elseif ($record->kabag_tu_id === null) { // Cek eksplisit untuk null
                        $status .= '<span class="text-yellow-500 font-bold"> diverifikasi oleh Katim ⏳</span><br>';
                    }
                                    

                    // Validasi oleh Kabag TU
                    if ($record->gudang_id == 1) {
                        $status .= '<span class="text-green-500 font-bold">  disetujui oleh Kabag TU ✅</span><br>';
                    } elseif ($record->gudang_id === 0) {
                        $status .= '<span class="text-red-500 font-bold">  disetujui oleh Kabag TU ❌</span><br>';
                    } elseif ($record->gudang_id === NULL){ // Jika null
                        $status .= '<span class="text-yellow-500 font-bold">  disetujui oleh Kabag TU ⏳</span><br>';
                    }

                   

                    return $status;
                }),
                
            ])
            ->filters([
                Filter::make('fungsi')
                    ->form([
                        Grid::make(20)
                            ->schema([
                                Select::make('fungsi')
                                    ->label('Select Fungsi')
                                    ->options(PermintaanBarangPersediaan::query()->distinct()->pluck('fungsi', 'fungsi'))
                                    ->placeholder('Select Fungsi')
                                    ->columnSpan(10)
                                    ->searchable()
                                    ->reactive(),
            
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
                        // Filter berdasarkan fungsi
                        if (!empty($data['fungsi'])) {
                            $query->where('fungsi', $data['fungsi']);
                        }
            
                        // Filter berdasarkan status
                        if (isset($data['status'])) {
                            if ($data['status'] === 'On Proses') {
                                $query->whereNull('diserahkan_id'); // Belum diserahkan
                            } elseif ($data['status'] === 'Selesai') {
                                $query->whereNotNull('diserahkan_id'); // Sudah diserahkan
                            }
                        }
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                ->label(function () {
                    // Ubah label berdasarkan role pengguna
                    if (auth()->user()->hasRole('admin_gudang')) {
                        return 'Persetujuan';
                    }
                    return 'Persetujuan';
                })
             
                ->visible(function ($record) {
                    $user = auth()->user();

                    // Jika pengguna adalah super_admin atau verifikator, tombol selalu muncul
                    if ($user->hasAnyRole(['super_admin', 'katim', 'kabag_tu', 'admin_gudang'])) {
                        return true;
                    }

                   

                    // Default: tombol tidak muncul
                    return false;
                }),

                Tables\Actions\DeleteAction::make()
                ->hidden(function ($record) {
                    // Sembunyikan tombol delete jika kabag_tu_id sudah diisi
                    return !is_null($record->kabag_tu_id);
                }),

                Tables\Actions\Action::make('spb')
                ->label('SPB & SBBK')
                ->icon('heroicon-o-printer') // Hanya menampilkan ikon printer
                ->url(fn ($record) => route('spb-pdf', ['id' => $record->id]))
                ->openUrlInNewTab()
                ->color('success') // Tombol berwarna hijau
                ->visible(fn ($record) => $record->diserahkan_id == 1),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   //
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
            
                // Jika pengguna bukan super_admin, katim, atau kabag_tu, filter data berdasarkan user_id
               if ($user->hasRole('katim')) {
                    $query->where('katim_id', $user->id);
                }
                // Jika pengguna adalah kabag_tu, hanya tampilkan data dengan kabag_tu_id = 1
                elseif ($user->hasRole('kabag_tu')) {
                    $query->where('kabag_tu_id', 1);
                }
                elseif ($user->hasRole('admin_gudang')) {
                    $query->where('kabag_tu_id', 1)
                          ->where('gudang_id', 1);
                }
                else {
                    $query->where('user_id', $user->id);
                }
                // Jika super_admin, tidak perlu filter (lihat semua data)
            });
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
            'index' => Pages\ListPermintaanBarangPersediaans::route('/'),
            'create' => Pages\CreatePermintaanBarangPersediaan::route('/create'),
            'edit' => Pages\EditPermintaanBarangPersediaan::route('/{record}/edit'),
            'delete' => Pages\EditPermintaanBarangPersediaan::route('/{record}/delete'),
        ];
    }
}
