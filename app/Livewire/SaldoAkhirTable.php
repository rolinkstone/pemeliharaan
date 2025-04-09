<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BarangPersediaan;
use App\Models\PermintaanBarangPersediaanItem;

class SaldoAkhirTable extends Component
{
    public $selectedMonth = 1; // Default bulan Januari

    public function render()
    {
        // Ambil data barang persediaan
        $barangPersediaan = BarangPersediaan::with(['permintaanItems' => function ($query) {
            $query->whereHas('permintaanBarangPersediaan', function ($q) {
                $q->where('diserahkan_id', 1)
                  ->where('status', 'out')
                  ->whereMonth('tanggal', '<=', $this->selectedMonth); // Filter berdasarkan bulan
            });
        }])->get();

        // Hitung saldo akhir untuk setiap barang
        $barangPersediaan->each(function ($barang) {
            $totalDiminta = $barang->permintaanItems->sum('jumlah');
            $barang->saldo_akhir = $barang->saldo_awal - $totalDiminta;
        });

        return view('livewire.saldo-akhir-table', [
            'barangPersediaan' => $barangPersediaan,
        ]);
    }
}