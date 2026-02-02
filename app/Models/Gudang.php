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
        static::updated(function (Gudang $gudang) {
            // Ambil stok lama
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
        
        static::created(function (Gudang $gudang) {
            // Log untuk record baru dengan stok > 0
            if ($gudang->stok > 0) {
                LogAktivitas::create([
                    'barang_id' => $gudang->barang_id,
                    'user_id' => Auth::id(),
                    'gudang_id' => $gudang->id,
                    'nama_barang_snapshot' => $gudang->barang->nama_barang ?? '',
                    'kode_barang_snapshot' => $gudang->barang->kode_barang ?? '',
                    'user_snapshot' => Auth::user()->name ?? 'System',
                    'nama_bagian_snapshot' => $gudang->bagian->nama_bagian ?? '',
                    'tipe' => 'Masuk',
                    'keterangan' => $gudang->keteranganOtomatis ?? 'Stok Awal',
                    'jumlah' => $gudang->stok,
                    'stok_awal' => 0,
                    'stok_akhir' => $gudang->stok,
                ]);
            }
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
