<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    protected $fillable = [
        'buku_id',
        'judul',
        'penulis',
        'penerbit',
        'kategori_id',
        'stok',
        'tahun_terbit',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function peminjam(): HasMany
    {
        return $this->hasMany(Peminjam::class, 'buku_id');
    }
}
