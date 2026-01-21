<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barangs';

    public $incrementing = false;   
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'kode_barang',
        'nama_barang',
    ];
}
