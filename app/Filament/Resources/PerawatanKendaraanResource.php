<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerawatanKendaraanResource\Pages;
use App\Filament\Resources\PerawatanKendaraanResource\RelationManagers;
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

class PerawatanKendaraanResource extends Resource
{
    protected static ?string $model = LaporanKerusakan::class;
    protected static ?string $pluralLabel = 'Perawatan Mobil dan Motor';
    protected static ?string $navigationGroup = 'ASET';
    protected static ?string $navigationLabel = 'Perawatan';
    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    public static function getModelLabel(): string
        {
            return 'Perawatan Kendaraan';
        }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                        'Tanggal: ' . ($record->tanggal ?? 'N/A');
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
                        if ($user->hasAnyRole(['super_admin', 'kabag_tu'])) {
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
                
                    // Jika pengguna bukan super_admin, verifikator, atau teknisi, filter data berdasarkan user_id
                    if (!$user->hasRole('super_admin') && !$user->hasRole('kabag_tu') && !$user->hasRole('teknisi')) {
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
            $relations[] = RelationManagers\DisposisiKerusakanRelationManager::class;
        }

        // Tambahkan relasi PerbaikanKerusakanRelationManager untuk teknisi dan super_admin
        if (auth()->user()->hasRole('teknisi') || auth()->user()->hasRole('super_admin')) {
            // Periksa apakah ada DisposisiKerusakan dengan ditujukan_ke yang tidak kosong
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
            'index' => Pages\ListPerawatanKendaraans::route('/'),
            'create' => Pages\CreatePerawatanKendaraan::route('/create'),
            'edit' => Pages\EditPerawatanKendaraan::route('/{record}/edit'),
        ];
    }
}
