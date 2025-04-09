<?php
namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function cetakPdf($id)
    {
        // Ambil data dari database atau sumber lainnya
        $ticket = [
            'nomor_tiket' => 'LK-20250222-004',
            'identitas_sarana' => 'Jenis Laporan: Kerusakan, Laporan: edwased',
            'disposisi' => 'Ditujukan Ke: Tim TI, Isi: Mohon Diperbaiki',
            'tindakan_perbaikan' => 'Kerusakan: dasdas, Hasil: dasdas, Tanggal: 2025-02-24'
        ];

        // Load view dan kirim data ke view
        $pdf = Pdf::loadView('pdf.ticket', compact('ticket'));

        // Download PDF
        return $pdf->download('ticket.pdf');
    }
}