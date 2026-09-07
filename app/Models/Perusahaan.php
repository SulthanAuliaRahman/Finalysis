<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Perusahaan extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'perusahaan';

    protected $fillable = [
        'nama',
        'deskripsi',
    ];


    public function perusahaan(){
        return $this->hasMany(User::class);
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class);
    }

    public function analisis()
    {
        return $this->hasMany(Analisis::class);
    }
}
