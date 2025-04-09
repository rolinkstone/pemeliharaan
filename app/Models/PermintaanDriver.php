<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PermintaanDriver extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal','tanggal_awal','tanggal_akhir','tujuan','kegiatan','user_id','user_id', 'nama',
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

    public function permintaandriverdetail()
    {
        return $this->hasMany(PermintaanDriverDetail::class, 'permintaan_driver_id');
    }

  

    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }
}


