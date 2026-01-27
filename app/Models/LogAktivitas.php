<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'barang_id',
        'user_id',
        'gudang_id',
        'nama_barang_snapshot',
        'kode_barang_snapshot',
        'user_snapshot',
        'nama_bagian_snapshot',
        'tipe',
        'jumlah',
        'stok_awal',
        'stok_akhir',
        'keterangan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
