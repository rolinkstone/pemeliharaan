<?php

namespace App\Exports;

use App\Models\LaporanKerusakan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanKerusakanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $record;

    public function __construct($record)
    {
        $this->record = $record;
    }

    public function collection()
    {
        return LaporanKerusakan::where('kode_barang', $this->record->kode_barang)
            ->where('nup', $this->record->nup)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function map($laporan): array
    {
        return [
            $laporan->tanggal,
            $laporan->uraian_laporan,
            optional($laporan->disposisiKerusakan()->latest()->first())->isi ?? 'Belum ada disposisi',
            optional($laporan->perbaikanKerusakan()->latest()->first())->hasil ?? 'Belum ada hasil',
            optional($laporan->perbaikanKerusakan()->latest()->first())->kesimpulan ?? 'Belum ada kesimpulan',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Uraian Laporan',
            'Isi Disposisi',
            'Hasil Perbaikan',
            'Kesimpulan',
        ];
    }
}
