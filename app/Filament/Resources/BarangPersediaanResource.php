<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangPersediaanResource\Pages;
use App\Filament\Resources\BarangPersediaanResource\RelationManagers;
use App\Models\BarangPersediaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\PermintaanBarangPersediaan;
use App\Models\PermintaanBarangPersediaanItem;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Carbon\Carbon;
use App\Models\Kategori;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\Action;


class BarangPersediaanResource extends Resource
{
    protected static ?string $model = BarangPersediaan::class;

    protected static ?string $navigationGroup = 'MASTER';
    protected static ?string $navigationLabel = 'Barang Persediaan';
    protected static ?string $navigationIcon = 'heroicon-s-queue-list';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([Fieldset::make('Jenis Barang')
        ->schema([
            Select::make('jenis_barang')
                ->label('Jenis Barang')
                ->options(
                    BarangPersediaan::query()
                        ->select('jenis_barang')
                        ->distinct()
                        ->pluck('jenis_barang', 'jenis_barang')
                        ->toArray()
                )
                ->searchable()
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set) => $set('jenis_barang_baru', null)),
    
                Forms\Components\TextInput::make('jenis_barang')
                ->label('Jenis Barang (Lainnya)')
                ->visible(fn (callable $get) => !in_array($get('jenis_barang'), BarangPersediaan::query()->distinct()->pluck('jenis_barang')->toArray()))
                ->required(fn (callable $get) => !in_array($get('jenis_barang'), BarangPersediaan::query()->distinct()->pluck('jenis_barang')->toArray()))
                ->formatStateUsing(fn ($state) => strtoupper($state)) // untuk ditampilkan dalam uppercase
                ->dehydrateStateUsing(fn ($state) => strtoupper($state)), // untuk disimpan dalam uppercase
        ]), // Jika ingin bisa dicari

       Fieldset::make('Kategori')
        ->schema([
            Select::make('kategori')
                ->label('Kategori')
                ->options(
                    BarangPersediaan::query()
                        ->select('kategori')
                        ->distinct()
                        ->pluck('kategori', 'kategori')
                        ->toArray()
                )
                ->searchable()
                ->reactive()
                ->afterStateUpdated(fn ($state, callable $set) => $set('kategori_baru', null)),

            Forms\Components\TextInput::make('kategori')
                ->label('Kategori (Lainnya)')
                ->extraAttributes(['style' => 'text-transform: uppercase']) // ubah tampilan
                ->visible(fn (callable $get) => !in_array($get('kategori'), BarangPersediaan::query()->distinct()->pluck('kategori')->toArray()))
                ->required(fn (callable $get) => !in_array($get('kategori'), BarangPersediaan::query()->distinct()->pluck('kategori')->toArray()))
                ->formatStateUsing(fn ($state) => strtoupper($state)) // untuk ditampilkan dalam uppercase
                ->dehydrateStateUsing(fn ($state) => strtoupper($state)), // untuk disimpan dalam uppercase
        ]),
      
        Forms\Components\Grid::make(3)
        ->schema([
            Forms\Components\TextInput::make('nama_barang')
                ->label('Nama Barang')
                ->required()
                ->maxLength(255),
    
            Forms\Components\TextInput::make('satuan')
                ->label('Satuan')
                ->required()
                ->maxLength(255),
    
            Forms\Components\TextInput::make('saldo_awal')
                ->label('Saldo Awal')
                ->required()
                ->numeric()
                ->maxLength(255),
        ]),
    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jenis_barang')->label('JENIS BARANG')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kategori')->label('KATEGORI')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('nama_barang')->label('NAMA BARANG')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('satuan')->label('SATUAN')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('saldo_awal')->label('SALDO AWAL')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('saldo_akhir')
                ->label('SALDO AKHIR')
                ->sortable(query: function ($query, $direction) {
                    $bulan = session('bulan', now()->month);
                    $tahun = session('tahun', now()->year);
                    $tanggal_akhir = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
                
                    return $query->orderByRaw('(
                        saldo_awal - (
                            SELECT COALESCE(SUM(CASE WHEN pb.status = "out" THEN pbi.jumlah ELSE 0 END), 0)
                            FROM permintaan_barang_persediaan_items AS pbi
                            JOIN permintaan_barang_persediaans AS pb
                            ON pbi.permintaan_barang_persediaan_id = pb.id
                            WHERE pbi.barang_persediaan_id = barang_persediaans.id
                            AND pb.diserahkan_id = 1
                            AND pb.tanggal_diserahkan <= ?
                        ) + (
                            SELECT COALESCE(SUM(CASE WHEN pb.status = "in" THEN pbi.jumlah ELSE 0 END), 0)
                            FROM permintaan_barang_persediaan_items AS pbi
                            JOIN permintaan_barang_persediaans AS pb
                            ON pbi.permintaan_barang_persediaan_id = pb.id
                            WHERE pbi.barang_persediaan_id = barang_persediaans.id
                            AND pb.diserahkan_id = 1
                            AND pb.tanggal_diserahkan <= ?
                        )
                    ) ' . $direction, [$tanggal_akhir, $tanggal_akhir]);
                })
                ->getStateUsing(function (BarangPersediaan $record) {
                    $bulan = session('bulan', now()->month);
                    $tahun = session('tahun', now()->year);
                
                    $tanggal_akhir = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    $totalOut = PermintaanBarangPersediaanItem::where('barang_persediaan_id', $record->id)
                        ->whereHas('permintaanBarangPersediaan', function ($query) use ($tanggal_akhir) {
                            $query->where('diserahkan_id', 1)
                                ->where('status', 'out')
                                ->whereDate('tanggal_diserahkan', '<=', $tanggal_akhir);
                        })
                        ->sum('jumlah');
                
                    $totalIn = PermintaanBarangPersediaanItem::where('barang_persediaan_id', $record->id)
                        ->whereHas('permintaanBarangPersediaan', function ($query) use ($tanggal_akhir) {
                            $query->where('diserahkan_id', 1)
                                ->where('status', 'in')
                                ->whereDate('tanggal_diserahkan', '<=', $tanggal_akhir);
                        })
                        ->sum('jumlah');
                
                    return $record->saldo_awal - $totalOut + $totalIn;
                }),
                    ])
                    ->headerActions([
                        Action::make('pilih_bulan')
                            ->label('Monitoring Stock Opname')
                            ->form([
                                Select::make('tahun')
                                ->label('Tahun')
                                ->options(collect(range(2024, now()->year))
                                    ->mapWithKeys(fn ($year) => [$year => $year])
                                    ->toArray())
                                ->default(session('tahun', now()->year))
                                ->reactive(),
                                Select::make('bulan')
                                    ->label('Bulan')
                                    ->options([
                                        '1' => 'Januari',
                                        '2' => 'Februari',
                                        '3' => 'Maret',
                                        '4' => 'April',
                                        '5' => 'Mei',
                                        '6' => 'Juni',
                                        '7' => 'Juli',
                                        '8' => 'Agustus',
                                        '9' => 'September',
                                        '10' => 'Oktober',
                                        '11' => 'November',
                                        '12' => 'Desember',
                                    ])
                                    ->default(session('bulan', now()->month))
                                    ->reactive(),
                    
                             
                            ])
                            ->action(function ($data, $livewire) {
                                session([
                                    'bulan' => $data['bulan'],
                                    'tahun' => $data['tahun'],
                                ]);
                    
                                \Log::info('Bulan yang dipilih: ' . session('bulan'));
                                \Log::info('Tahun yang dipilih: ' . session('tahun'));
                    
                                $livewire->resetTable();
                            }),
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
            'index' => Pages\ListBarangPersediaans::route('/'),
            'create' => Pages\CreateBarangPersediaan::route('/create'),
            'edit' => Pages\EditBarangPersediaan::route('/{record}/edit'),
          
        ];
    }
}
