<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class LaporanKerusakan extends Model
{
    use HasFactory;
    protected $fillable = [
        'jenis_laporan', 'uraian_laporan', 'jenis_barang','nama', 'kode_barang', 'ruangan','tipe_alat','tanggal','user_id','nama_pelapor','no_ticket',
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
    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }
    public function disposisikerusakan()
    {
        return $this->hasMany(\App\Models\DisposisiKerusakan::class, 'laporan_kerusakan_id');
    }

    public function perbaikanKerusakan()
    {
        return $this->hasMany(PerbaikanKerusakan::class, 'laporan_kerusakan_id');
    }

    public function disposisikerusakanone()
    {
        return $this->hasOne(DisposisiKerusakan::class, 'laporan_kerusakan_id');
    }
    
    public static function generateNoTiket()
    {
        $today = Carbon::now()->format('Ymd');
        $lastReport = LaporanKerusakan::whereDate('created_at', Carbon::today())->latest()->first();
    
        if ($lastReport) {
            $lastNoUrut = (int) substr($lastReport->no_ticket, -3);
            $noUrut = str_pad($lastNoUrut + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $noUrut = '001';
        }
    
        $noTicket = 'LK-' . $today . '-' . $noUrut;
    
        // Cek apakah no_ticket sudah ada
        while (LaporanKerusakan::where('no_ticket', $noTicket)->exists()) {
            $noUrut = str_pad((int) $noUrut + 1, 3, '0', STR_PAD_LEFT); // Tambah 1 jika duplikat
            $noTicket = 'LK-' . $today . '-' . $noUrut;
        }
    
        return $noTicket;
    }

}
