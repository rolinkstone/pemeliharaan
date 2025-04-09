<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KendaraanDinasDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'kendaraan','nama_driver',
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
    
  
    public function KendaraanDinas()
    {
        return $this->belongsTo(KendaraanDinas::class, 'kendaraan_dinas_id'); // Sesuaikan foreign key jika diperlukan
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
