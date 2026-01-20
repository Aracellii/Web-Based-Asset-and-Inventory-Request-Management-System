<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bagian extends Model
{
    protected $table = 'bagians';
    protected $fillable = [
        'id',
        'nama_bagian',
    ];
}
