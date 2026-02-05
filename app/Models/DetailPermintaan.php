<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPermintaan extends Model
{
    protected $table = 'detail_permintaans';

    protected $fillable = [
        'permintaan_id',
        'barang_id',
        'jumlah',
        'bagian_id',
        'approved',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function permintaan(): BelongsTo
    {
        return $this->belongsTo(Permintaan::class, 'permintaan_id');
    }

    public function gudang(): BelongsTo
    {
        return $this->belongsTo(Gudang::class, 'barang_id', 'barang_id')
            ->where('bagian_id', $this->bagian_id);
    }
    public function verifikasi()
    {
        return $this->hasOne(DetailTerverifikasi::class, 'detail_permintaan_id');
    }
}
