<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;
    protected $fillable = [
        'barang_id',
        'bagian_id',
        'stok',
        'kode_barang',
        
    ];
    public function barang()
{
    return $this->belongsTo(Barang::class);
}

public function bagian()
{
    return $this->belongsTo(Bagian::class);
}
}
