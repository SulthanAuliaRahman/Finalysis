<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    protected $fillable = [
        'nama',
        'sektor',
        'deskripsi',
    ];

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class);
    }
}
