<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Gudang extends Model
{
    use HasFactory;
    protected $fillable = [
        'barang_id',
        'bagian_id',
        'stok',
        'kode_barang',
    ];

    protected static function booted(): void
    {
        static::updating(function (Gudang $gudang) {
            $stokLama = $gudang->getOriginal('stok');
            $stokBaru = $gudang->stok;
            $selisih = $stokBaru - $stokLama;

            if ($selisih != 0) {
                LogAktivitas::create([
                    'barang_id' => $gudang->barang_id,
                    'user_id' => Auth::id(),
                    'gudang_id' => $gudang->id,
                    'nama_barang_snapshot' => $gudang->barang->nama_barang ?? '',
                    'kode_barang_snapshot' => $gudang->barang->kode_barang ?? '',
                    'user_snapshot' => Auth::user()->name ?? 'System',
                    'nama_bagian_snapshot' => $gudang->bagian->nama_bagian ?? '',
                    'tipe' => $selisih > 0 ? 'masuk' : 'keluar',
                    'jumlah' => abs($selisih),
                    'stok_awal' => $stokLama,
                    'stok_akhir' => $stokBaru,
                ]);
            }
        });

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

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }
}
