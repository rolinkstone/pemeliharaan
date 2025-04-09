<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Str;

class Barang extends Model
{
    use HasFactory, HasRoles;
    protected $fillable = [
        'jenis_barang', 'kode_barang', 'nama','nup', 'penanggungjawab', 'ruangan','pengadaan','kondisi','foto','bast','keterangan',
    ];
    protected $casts = [
        
        'foto' => 'array',
    ];
    
   
    public function getIncrementing()
    {
        return false;
    }
    public function getKeyType()
    {
        return 'string';
    }
   

}
