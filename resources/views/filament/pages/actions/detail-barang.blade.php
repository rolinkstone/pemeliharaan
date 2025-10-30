@php
use App\Models\LaporanKerusakan;
use App\Models\DisposisiKerusakan;
use App\Models\PerbaikanKerusakan;

$laporans = LaporanKerusakan::where('kode_barang', $record->kode_barang)
    ->where('nup', $record->nup)
    ->orderBy('tanggal', 'desc')
    ->get();
@endphp

<div class="p-4">
    <h2 class="text-lg font-semibold mb-4 text-gray-700">
        Laporan Kerusakan untuk {{ $record->nama }}  
        <span class="text-sm text-gray-500">
            (Kode: {{ $record->kode_barang }} | NUP: {{ $record->nup }})
        </span>
    </h2>

    @if($laporans->isEmpty())
        <p class="text-gray-500 text-center py-4">
            Belum ada laporan kerusakan untuk barang ini.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 rounded-lg text-sm">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left border-b w-40">Tanggal</th>
                        <th class="px-4 py-2 text-left border-b">Uraian Laporan</th>
                        <th class="px-4 py-2 text-left border-b">Isi Disposisi</th>
                        <th class="px-4 py-2 text-left border-b">Hasil Perbaikan</th>
                        <th class="px-4 py-2 text-left border-b">Kesimpulan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laporans as $laporan)
                        @php
                            // Ambil disposisi terkait laporan ini
                            $disposisi = DisposisiKerusakan::where('laporan_kerusakan_id', $laporan->id)
                                ->orderBy('created_at', 'desc')
                                ->first();

                            // Ambil hasil & kesimpulan dari tabel perbaikan_kerusakans
                            $perbaikan = PerbaikanKerusakan::where('laporan_kerusakan_id', $laporan->id)
                                ->orderBy('created_at', 'desc')
                                ->first();
                        @endphp

                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700 align-top">
                                {{ \Carbon\Carbon::parse($laporan->tanggal)->translatedFormat('d F Y') }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 align-top">
                                {{ $laporan->uraian_laporan }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 align-top">
                                {{ $disposisi->isi ?? 'Belum ada disposisi' }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 align-top">
                                {{ $perbaikan->hasil ?? 'Belum ada hasil' }}
                            </td>
                            <td class="px-4 py-2 text-gray-700 align-top">
                                {{ $perbaikan->kesimpulan ?? 'Belum ada kesimpulan' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
