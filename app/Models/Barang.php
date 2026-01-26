<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
            $gudangs = Gudang::where('barang_id', $barang->id)->with('bagian')->get();
            
            foreach ($gudangs as $gudang) {
                if ($gudang->stok > 0) {
                    LogAktivitas::create([
                        'barang_id' => $barang->id,
                        'user_id' => Auth::id(),
                        'gudang_id' => $gudang->id,
                        'nama_barang_snapshot' => $barang->nama_barang,
                        'kode_barang_snapshot' => $barang->kode_barang,
                        'user_snapshot' => Auth::user()->name ?? 'System',
                        'nama_bagian_snapshot' => $gudang->bagian->nama_bagian ?? '',
                        'tipe' => 'keluar',
                        'jumlah' => $gudang->stok,
                        'stok_awal' => $gudang->stok,
                        'stok_akhir' => 0,
                    ]);
                }
            }
            
            // Set semua stok di gudang menjadi 0 ketika barang dihapus
            Gudang::where('barang_id', $barang->id)->update(['stok' => 0]);
        });
    }

    public function gudangs()
    {
        return $this->hasMany(Gudang::class);
    }
}
