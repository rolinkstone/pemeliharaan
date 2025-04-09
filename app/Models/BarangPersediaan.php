<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangPersediaan extends Model
{
    use HasFactory;
    protected $fillable = [
        'jenis_barang', 'kategori', 'nama_barang','satuan', 'saldo_awal', 
    ];
   
    
   
    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }

    public function permintaanBarangPersediaanItems(): HasMany
    {
        return $this->hasMany(PermintaanBarangPersediaanItem::class, 'barang_persediaan_id');
    }

    public function permintaanBarangPersediaan(): HasMany
    {
        return $this->hasMany(PermintaanBarangPersediaan::class, 'barang_persediaan_id');
    }
}
