<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTerverifikasi extends Model
{
    use HasFactory;
    protected $table = 'detail_terverifikasis'; 

    protected $fillable = [
        'detail_id', 
        'barang_id',
        'jumlah',
    ];
}
