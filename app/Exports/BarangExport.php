<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\LaporanKerusakan;
use App\Models\DisposisiKerusakan;
use App\Models\PerbaikanKerusakan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class BarangExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $filters;
    protected $rows = [];

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Barang::query();

        $jenis = $this->filters['jenis_barang'] ?? null;
        if (!empty($jenis) && !is_array($jenis)) {
            $query->where('jenis_barang', $jenis);
        } elseif (is_array($jenis) && !empty($jenis['jenis_barang'])) {
            $query->where('jenis_barang', $jenis['jenis_barang']);
        }

        $barangs = $query->orderBy('id', 'desc')->get();
        $this->rows = collect();

        foreach ($barangs as $barang) {
            $laporans = LaporanKerusakan::where('kode_barang', $barang->kode_barang)
                ->where('nup', $barang->nup)
                ->orderBy('tanggal', 'asc')
                ->get();

            if ($laporans->isEmpty()) {
                $this->rows->push([
                    'barang' => $barang,
                    'tanggal' => '-',
                    'uraian_laporan' => '-',
                    'disposisi' => '-',
                    'hasil_perbaikan' => '-',
                    'kesimpulan' => '-',
                    'is_first' => true,
                ]);
            } else {
                $is_first = true;
                foreach ($laporans as $laporan) {
                    // Ambil disposisi dari tabel disposisi_kerusakan
                    $disposisi = DisposisiKerusakan::where('laporan_kerusakan_id', $laporan->id)
                        ->pluck('isi')
                        ->filter()
                        ->implode("\n") ?: '-';

                    // Ambil hasil perbaikan & kesimpulan dari tabel perbaikan_kerusakan
                    $perbaikan = PerbaikanKerusakan::where('laporan_kerusakan_id', $laporan->id)->first();

                    $hasil_perbaikan = $perbaikan->hasil_perbaikan ?? '-';
                    $kesimpulan = $perbaikan->kesimpulan ?? '-';

                    $this->rows->push([
                        'barang' => $barang,
                        'tanggal' => $laporan->tanggal
                            ? Carbon::parse($laporan->tanggal)->translatedFormat('d F Y')
                            : '-',
                        'uraian_laporan' => $laporan->uraian_laporan ?? '-',
                        'disposisi' => $disposisi,
                        'hasil_perbaikan' => $hasil_perbaikan,
                        'kesimpulan' => $kesimpulan,
                        'is_first' => $is_first,
                    ]);
                    $is_first = false;
                }
            }
        }

        return $this->rows;
    }

    public function map($row): array
    {
        $barang = $row['barang'];

        if (!$row['is_first']) {
            return [
                '', '', '', '', '', '', '', '', '', '', '',
                $row['tanggal'],
                $row['uraian_laporan'],
                $row['disposisi'],
                $row['hasil_perbaikan'],
                $row['kesimpulan'],
            ];
        }

        return [
            $barang->jenis_barang,
            $barang->kode_barang,
            $barang->nama,
            $barang->nup,
            $barang->penanggungjawab,
            $barang->ruangan,
            $barang->pengadaan,
            $barang->kondisi,
            $barang->foto ? asset('storage/' . $barang->foto) : '-',
            $barang->bast,
            $barang->keterangan,
            $row['tanggal'],
            $row['uraian_laporan'],
            $row['disposisi'],
            $row['hasil_perbaikan'],
            $row['kesimpulan'],
        ];
    }

    public function headings(): array
    {
        return [
            [
                'Jenis Barang', 'Kode Barang', 'Nama Barang', 'NUP', 'Penanggung Jawab',
                'Ruangan', 'Tahun Pengadaan', 'Kondisi', 'Foto (URL)', 'No. BAST', 'Keterangan',
                'Histori Perbaikan', '', '', '', ''
            ],
            [
                '', '', '', '', '', '', '', '', '', '', '',
                'Tanggal Laporan', 'Uraian Laporan', 'Isi Disposisi', 'Hasil Perbaikan', 'Kesimpulan'
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge cell untuk header utama "Histori Perbaikan"
                $event->sheet->mergeCells('L1:P1');

                // Styling header
                $event->sheet->getStyle('A1:P2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Agar kolom disposisi, hasil perbaikan, kesimpulan wrap text otomatis
                $event->sheet->getStyle('N:P')->getAlignment()->setWrapText(true);
            },
        ];
    }
}
