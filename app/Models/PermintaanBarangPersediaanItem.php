<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PermintaanBarangPersediaanItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'permintaan_barang_persediaan_id','kategori', 'nama_barang','satuan', 'jumlah', 'status','barang_persediaan_id',
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
   public function permintaan()
    {
        return $this->belongsTo(PermintaanBarangPersediaan::class, 'permintaan_barang_persediaan_id');
    }
    public function permintaanBarangPersediaan()
    {
        return $this->belongsTo(PermintaanBarangPersediaan::class, 'permintaan_barang_persediaan_id');
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
