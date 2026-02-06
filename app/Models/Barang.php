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
    
    protected static function booted(): void
    {
        static::deleting(function (Barang $barang) {
            $gudangs = Gudang::where('barang_id', $barang->id)->get();
            
            foreach ($gudangs as $gudang) {
                if ($gudang->stok > 0) {
                    $gudang->stok = 0;
                    $gudang->save(); 
                }
            }
        });
    }

    public function gudangs()
    {
        return $this->hasMany(Gudang::class);
    }
}
