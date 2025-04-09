<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PemenuhanPermintaan extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal','nama','hasil','kesimpulan','catatan','validasi','user_id',
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


    public function permintaanPrasarana()
    {
        return $this->belongsTo(PermintaanPrasarana::class);
    }
}
