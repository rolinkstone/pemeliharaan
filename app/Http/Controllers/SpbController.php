<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\PermintaanBarangPersediaan;
use App\Models\PermintaanBarangPersediaanItem; // Ganti dengan model Anda
use App\Models\User; // sesuaikan jika modelnya Pegawai atau lainnya

class SpbController extends Controller
{
    public function spbPdf($id)
{
    $record = PermintaanBarangPersediaan::findOrFail($id);
    $details = PermintaanBarangPersediaanItem::where('permintaan_barang_persediaan_id', $record->id)->get();
    $details = $details->isEmpty() ? null : $details;

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
    $items = PermintaanBarangPersediaanItem::where('permintaan_barang_persediaan_id', $record->id)->get();

    $data = [
        'fungsi' => $record->fungsi,
        'no_ticket' => $record->no_ticket,
        'nama_pelapor' => $record->nama_pelapor,
        'tanggal' => $record->tanggal,
        'logo_base64' => $logo_base64,
        'nama_pelapor' => $record->nama_pelapor,
        'nama_katim' => $katim ? $katim->name : '-', // ganti katim_id ke nama katim
        'nama_kabag_tu' => $kabag_tu ? $kabag_tu->name : '-', // ganti katim_id ke nama katim
        'items' => $items,
    ];

    $html = view('pdf.spb', $data)->render();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $fileName = $record->id . '.pdf';

    return $dompdf->stream($fileName, [
        'Attachment' => false, // <= ini kuncinya agar tampil di browser
    ]);
}

   
   
}
