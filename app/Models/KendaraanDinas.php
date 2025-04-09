<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class KendaraanDinas extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_st', 'tanggal', 'tujuan','jenis_kendaraan', 'driver', 'user_id','nama','fungsi',
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
    
    public function kendaraandinasdetail()
    {
        return $this->hasMany(KendaraanDinasDetail::class, 'kendaraan_dinas_id');
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
