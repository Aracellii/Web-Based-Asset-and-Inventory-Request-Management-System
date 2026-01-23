<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barang extends Model
{
    use HasFactory;
    
    protected $table = 'barangs';  
    protected $fillable = [
        'id',
        'kode_barang',
        'nama_barang',
    ];

    /**
     * Relasi ke tabel gudang
     */
    public function gudangs()
    {
        return $this->hasMany(Gudang::class);
    }

    /**
     * Get total stok dari semua bidang
     */
    public function getTotalStokAttribute()
    {
        return $this->gudangs()->sum('stok');
    }
}
