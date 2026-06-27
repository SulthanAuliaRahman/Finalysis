<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'dokumen';

    protected $fillable = [
        'perusahaan_id',
        'nama_file',
        'storage_path',
        'periode',
        'statement_types',
        'ukuran_file',
        'status',
    ];

    protected $casts = [
        'statement_types' => 'array',
    ];

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }

    public function neraca()
    {
        return $this->hasOne(Neraca::class);
    }

    public function labaRugi()
    {
        return $this->hasOne(LabaRugi::class);
    }

    public function arusKas()
    {
        return $this->hasOne(ArusKas::class);
    }

    public function chunks()
    {
        return $this->hasMany(Chunk::class);
    }
}
