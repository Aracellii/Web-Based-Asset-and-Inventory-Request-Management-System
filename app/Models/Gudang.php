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
