<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class PermintaanBarangPersediaan extends Model
{
    use HasFactory;
    protected $fillable = [
         'tanggal',  'fungsi', 'katim_id', 'user_id','nama_pelapor','no_ticket','kabag_tu_id','gudang_id','diserahkan_id','bukti_bayar','tanggal_diserahkan',
    ];
   
   
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
            
        });
    }
    public function items()
        {
            return $this->hasMany(PermintaanBarangPersediaanItem::class, 'permintaan_barang_persediaan_id');
        }
        public function kabagTu()
        {
            return $this->belongsTo(User::class, 'kabag_tu_id'); // Relasi untuk kabag_tu_id
        }
        
        public function katim()
        {
            return $this->belongsTo(User::class, 'katim_id'); // Relasi untuk katim_id
        }
        public function permintaanBarangPersediaanItems()
        {
            return $this->hasMany(PermintaanBarangPersediaanItem::class, 'barang_persediaan_id');
        }

    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }
     
    public static function generateNoTiket()
    {
        $today = Carbon::now()->format('Ymd');
        $lastReport = PermintaanBarangPersediaan::whereDate('created_at', Carbon::today())->latest()->first();
    
        if ($lastReport) {
            $lastNoUrut = (int) substr($lastReport->no_ticket, -3);
            $noUrut = str_pad($lastNoUrut + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $noUrut = '001';
        }
    
        $noTicket = 'PBP-' . $today . '-' . $noUrut;
    
        // Cek apakah no_ticket sudah ada
        while (PermintaanBarangPersediaan::where('no_ticket', $noTicket)->exists()) {
            $noUrut = str_pad((int) $noUrut + 1, 3, '0', STR_PAD_LEFT); // Tambah 1 jika duplikat
            $noTicket = 'PBP-' . $today . '-' . $noUrut;
        }
    
        return $noTicket;
    }
}
