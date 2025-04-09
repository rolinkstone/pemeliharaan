<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class PermintaanPrasarana extends Model
{
    use HasFactory;
    protected $fillable = [
        'jenis_laporan','uraian_laporan','nama','spesifikasi','tipe_alat','tanggal','user_id', 'nama_pelapor','no_ticket',
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
    public function disposisipermintaan()
    {
        return $this->hasMany(\App\Models\DisposisiPermintaan::class, 'permintaan_prasarana_id');
    }

    public function pemenuhanPermintaan()
    {
        return $this->hasMany(PemenuhanPermintaan::class, 'permintaan_prasarana_id');
    }

    public function disposisipermintaanone()
    {
        return $this->hasOne(DisposisiPermintaan::class, 'permintaan_prasarana_id');
    }
    
    public static function generateNoTiket()
    {
        $today = Carbon::now()->format('Ymd');
        $lastReport = PermintaanPrasarana::whereDate('created_at', Carbon::today())->latest()->first();
    
        if ($lastReport) {
            $lastNoUrut = (int) substr($lastReport->no_ticket, -3);
            $noUrut = str_pad($lastNoUrut + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $noUrut = '001';
        }
    
        $noTicket = 'PP-' . $today . '-' . $noUrut;
    
        // Cek apakah no_ticket sudah ada
        while (PermintaanPrasarana::where('no_ticket', $noTicket)->exists()) {
            $noUrut = str_pad((int) $noUrut + 1, 3, '0', STR_PAD_LEFT); // Tambah 1 jika duplikat
            $noTicket = 'PP-' . $today . '-' . $noUrut;
        }
    
        return $noTicket;
    }

}
