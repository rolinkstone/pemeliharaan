<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianBarangPersediaanResource\Pages;
use App\Filament\Resources\PembelianBarangPersediaanResource\RelationManagers;
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
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;

class PembelianBarangPersediaanResource extends Resource
{
    protected static ?string $model = PermintaanBarangPersediaan::class;
 
   
    protected static ?string $pluralLabel = 'Pembelian Barang Persediaan';
    protected static ?string $navigationGroup = 'PERSEDIAAN';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';
    public static function getModelLabel(): string
        {
            return 'Pembelian Barang';
        }
        public static function canAccess(): bool
        {
            return Filament::auth()->user()->hasAnyRole(['admin_gudang', 'super_admin']);
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

                Forms\Components\Grid::make(12)
                ->schema([
                    Forms\Components\DatePicker::make('tanggal_diserahkan')
                        ->label('Tanggal')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->columnSpan(6), // Setengah lebar
                    
                        Forms\Components\FileUpload::make('bukti_bayar')
                        ->label('Upload Bukti Bayar')
                        ->directory('bukti_bayar') // Menyimpan file ke storage/app/public/bukti_bayar
                        ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg', 'application/pdf'])
                        ->maxSize(2048) // Maksimal 2MB
                        ->disk('public') // Pastikan menyimpan di disk 'public'
                        ->columnSpan(6),
                 // Setengah lebar
                ]),
                    

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
                                ->default('In') // Menambahkan nilai default "Out"
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
                        ->maxItems(5)
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->defaultItems(1),

                        
                        

                    

                            Select::make('diserahkan_id')
                            ->label('Barang Sudah Diterima?')
                            ->options([
                                1 => 'Ya', // Nilai 1 untuk "Ya"
                                0 => 'Tidak', // Nilai 0 untuk "Tidak"
                            ])
                            ->visible(function () {
                                return auth()->user()->hasRole(['admin_gudang', 'super_admin']); // Hanya muncul untuk role 'katim'
                            }),
                            
                      
                           // Wajib diisi
                ]);
        }

    public static function table(Table $table): Table
    {
        return $table
        ->query(function () {
            return static::getModel()::query()
                ->whereHas('items', function ($query) {
                    $query->where('status', 'in');
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
            
                        if (isset($data['status'])) {
                            if ($data['status'] === 'On Proses') {
                                $query->where(function ($subQuery) {
                                    $subQuery->whereNull('diserahkan_id')
                                             ->orWhere('diserahkan_id', 0); // Tambahkan kondisi jika diserahkan_id = 0
                                });
                            } elseif ($data['status'] === 'Selesai') {
                                $query->whereNotNull('diserahkan_id')
                                      ->where('diserahkan_id', '!=', 0); // Pastikan bukan 0 juga
                            }
                        }
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
              
                Tables\Actions\Action::make('bukti_bayar')
                    ->label('') // Menghapus teks label
                    ->icon('heroicon-m-arrow-down-tray') // Hanya ikon download
                    ->color('blue') // Warna tombol
                    ->url(fn ($record) => asset('storage/' . $record->bukti_bayar), true) // URL ke file
                    ->openUrlInNewTab(), // Buka di tab baru
                    Tables\Actions\EditAction::make(),

                  
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPembelianBarangPersediaans::route('/'),
            'create' => Pages\CreatePembelianBarangPersediaan::route('/create'),
            'edit' => Pages\EditPembelianBarangPersediaan::route('/{record}/edit'),
        ];
    }
}
