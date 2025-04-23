<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\LaporanKerusakan;
use App\Models\DisposisiKerusakan; // Ganti dengan model Anda
use App\Models\PerbaikanKerusakan; // Tambahkan ini di bagian atas


use App\Models\User; // sesuaikan jika modelnya Pegawai atau lainnya

class TicketController extends Controller
{
    public function ticketPdf($id)
{
    $record = LaporanKerusakan::findOrFail($id);
    
    $dompdf = new Dompdf();
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true); // ini penting
    $dompdf->setOptions($options);

    $logo_path = public_path('logo/BADAN_POM.png');
    $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
    $logo_data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);

    $katim = User::find($record->katim_id); // ini ambil nama berdasarkan id
    $kabag_tu = User::find($record->kabag_tu_id); // ini ambil nama berdasarkan id
    $items = DisposisiKerusakan::where('laporan_kerusakan_id', $record->id)->get();
    // Tambahan untuk ambil data dari PerbaikanKerusakan
    $perbaikanItems = PerbaikanKerusakan::where('laporan_kerusakan_id', $record->id)->get();

    $data = [
        'jenis_laporan' => $record->jenis_laporan,
        'no_ticket' => $record->no_ticket,
        'uraian_laporan' => $record->uraian_laporan,
        'jenis_barang' => $record->jenis_barang,
        'nama' => $record->nama,
        'kode_barang' => $record->kode_barang,
        'ruangan' => $record->ruangan,
        'tipe_alat' => $record->tipe_alat,
        'tanggal' => $record->tanggal,

        'items' => $items,
        'perbaikanItems' => $perbaikanItems, // kirim ke view
        
        
        'nama_pelapor' => $record->nama_pelapor,
      
        'logo_base64' => $logo_base64,
      
    ];

    $html = view('pdf.ticket', $data)->render();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $fileName = $record->id . '.pdf';

    return $dompdf->stream($fileName, [
        'Attachment' => false, // <= ini kuncinya agar tampil di browser
    ]);
}

   
   
}
