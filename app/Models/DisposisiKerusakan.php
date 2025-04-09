<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class DisposisiKerusakan extends Model
{
    use HasFactory;
    protected $fillable = [
        'ditujukan_ke','isi','tanggal','diserahkan',
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
    public function disposisikerusakan(): HasMany
    {
        return $this->hasMany(DisposisiKerusakan::class);
    }
    
}
