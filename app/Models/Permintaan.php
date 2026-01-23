<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permintaan extends Model
{
    protected $table = 'permintaans';

    protected $fillable = [
        'user_id',
        'tanggal_permintaan'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailPermintaans(): HasMany
    {
        // Menghubungkan ke tabel detail_permintaans melalui kolom permintaan_id
        return $this->hasMany(DetailPermintaan::class, 'permintaan_id');
    }

    public function bagian()
{
     return $this->hasOneThrough(
        Bagian::class, 
        User::class, 
        'id',       
        'id',        
        'user_id',    
        'bagian_id'   
    );
}
}
