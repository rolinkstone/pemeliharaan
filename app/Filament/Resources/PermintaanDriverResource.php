<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermintaanDriverResource\Pages;
use App\Filament\Resources\PermintaanDriverResource\RelationManagers;
use App\Models\PermintaanDriver;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;


class PermintaanDriverResource extends Resource
{
    protected static ?string $model = PermintaanDriver::class;

    protected static ?string $navigationGroup = 'ASET';
    protected static ?string $navigationLabel = 'Permintaan Driver';
    protected static ?string $navigationIcon = 'heroicon-s-user';



    public static function getNavigationBadge(): ?string
    {
        // Periksa role pengguna
        if (Auth::check() && Auth::user()->hasAnyRole(['super_admin', 'admin_driver'])) {
           return static::getModel()::whereDoesntHave('permintaandriverdetail')->count();
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
         return static::getModel()::whereDoesntHave('disposisikerusakan')->exists() ? '•' : null;
     }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(Carbon::now()->format('Y-m-d'))
                ->disabled()
                ->dehydrated(),

                DatePicker::make('tanggal_awal')
                ->label('Tanggal Berangkat')
                ->required()
                ->displayFormat('d/m/Y') // Format tampilan tanggal
                ->format('Y-m-d'), // Format penyimpanan tanggal di database

                DatePicker::make('tanggal_akhir')
                ->label('Tanggal Pulang')
                ->required()
                ->displayFormat('d/m/Y') // Format tampilan tanggal
                ->format('Y-m-d'), // Format penyimpanan tanggal di database

                Select::make('tujuan')
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

                Forms\Components\Textarea::make('kegiatan')
                ->label('Kegiatan')
                ->required()
                ->maxLength(255),

                Forms\Components\Hidden::make('user_id')
                ->default(auth()->id())
                ->disabled()
                ->dehydrated(),

                Forms\Components\TextInput::make('nama')
                ->label('Nama PIC')
                ->required()
                ->default(auth()->user()->name) // Mengambil nama pengguna yang sedang login
                ->disabled()
                ->dehydrated(), // Memastikan nilai tetap tersimpan ke database, // Menonaktifkan input agar tidak bisa diubah
            ]);
    }

    public static function table(Table $table): Table
    {
    
        return $table
            ->columns([
               
                Tables\Columns\TextColumn::make('tanggal')
                ->label('Status')
                ->sortable()
                ->searchable()
                ->html() // Aktifkan rendering HTML
                ->formatStateUsing(function ($state, $record) {
                    // Default status
                    $status = 'Waiting';
                
                    // Periksa semua record permintaandriverdetail
                    if ($record->permintaandriverdetail->isNotEmpty()) {
                        foreach ($record->permintaandriverdetail as $detail) {
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
                
                Tables\Columns\TextColumn::make('tanggal_awal')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tanggal_akhir')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tujuan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kegiatan')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nama')->label('Nama PIC')->sortable()->searchable(),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->label('Tambahkan Driver'),
                Tables\Actions\DeleteAction::make()
                ->visible(function ($record) {
                    $hasDetail = $record->permintaandriverdetail->contains(function ($detail) {
                        return !empty($detail->nama_driver);
                    });
                    return !$hasDetail;
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
                if (!$user->hasRole('super_admin') && !$user->hasRole('admin_driver')) {
                    $query->where('user_id', $user->id);
                }
            
                // Pastikan relasi permintaandriverdetail dimuat
                $query->with('permintaandriverdetail');
            });
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PermintaanDriverDetailRelationManager::class,
           
        ];
    }


    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermintaanDrivers::route('/'),
            'create' => Pages\CreatePermintaanDriver::route('/create'),
            'edit' => Pages\EditPermintaanDriver::route('/{record}/edit'),
            
        ];
    }
}
