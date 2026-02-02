<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Barang;

class Gudang extends Model
{
    use HasFactory;
    protected $fillable = [
        'barang_id',
        'bagian_id',
        'stok',
        'kode_barang',
    ];
    public $keteranganOtomatis = 'Penyesuaian Stok';
    protected static function booted(): void
    {
        static::saved(function (Gudang $gudang) {
            // Ambil stok lama. Jika record baru, original adalah 0
            $stokLama = $gudang->getOriginal('stok') ?? 0;
            $stokBaru = $gudang->stok;
            $selisih = $stokBaru - $stokLama;

            // Hanya catat log jika ada perubahan angka
            if ($selisih != 0) {
                LogAktivitas::create([
                    'barang_id' => $gudang->barang_id,
                    'user_id' => Auth::id(),
                    'gudang_id' => $gudang->id,
                    'nama_barang_snapshot' => $gudang->barang->nama_barang ?? '',
                    'kode_barang_snapshot' => $gudang->barang->kode_barang ?? '',
                    'user_snapshot' => Auth::user()->name ?? 'System',
                    'nama_bagian_snapshot' => $gudang->bagian->nama_bagian ?? '',
                    'tipe' => $selisih > 0 ? 'Masuk' : 'Keluar',
                    'keterangan' => $gudang->keteranganOtomatis ?? 'Penyesuaian Stok',
                    'jumlah' => abs($selisih),
                    'stok_awal' => $stokLama,
                    'stok_akhir' => $stokBaru,
                ]);
            }
        });
        
        // static::updating(function (Gudang $gudang) {
        //     $stokLama = $gudang->getOriginal('stok');
        //     $stokBaru = $gudang->stok;
        //     $selisih = $stokBaru - $stokLama;

        //     if ($selisih != 0) {
        //         LogAktivitas::create([
        //             'barang_id' => $gudang->barang_id,
        //             'user_id' => Auth::id(),
        //             'gudang_id' => $gudang->id,
        //             'nama_barang_snapshot' => $gudang->barang->nama_barang ?? '',
        //             'kode_barang_snapshot' => $gudang->barang->kode_barang ?? '',
        //             'user_snapshot' => Auth::user()->name ?? 'System',
        //             'nama_bagian_snapshot' => $gudang->bagian->nama_bagian ?? '',
        //             'tipe' => $selisih > 0 ? 'Masuk' : 'Keluar',
        //             'keterangan' => $gudang->keteranganOtomatis,
        //             'jumlah' => abs($selisih),
        //             'stok_awal' => $stokLama,
        //             'stok_akhir' => $stokBaru,
        //         ]);
        //     }
        // });
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
