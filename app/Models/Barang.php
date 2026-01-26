<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use SoftDeletes;
    protected $table = 'barangs';  
    protected $fillable = [
        'id',
        'kode_barang',
        'nama_barang',
    ];
}
