<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barangs';

    
    protected $fillable = [
        'id',
        'kode_barang',
        'nama_barang',
    ];
}
