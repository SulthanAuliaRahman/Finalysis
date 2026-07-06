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
        'periode_type',
        'tahun',
        'quarter',
        'bulan',
        'statement_types',
        'ukuran_file',
        'status',
    ];

    protected $casts = [
        'statement_types' => 'array',
    ];

    protected $appends = ['periode'];

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

    public function getPeriodeAttribute()
    {
        return match ($this->periode_type) {

            'annual' => $this->tahun,
            'quarterly' =>"Q{$this->quarter} {$this->tahun}",
            'monthly' =>
                [
                    1=>"Januari",
                    2=>"Februari",
                    3=>"Maret",
                    4=>"April",
                    5=>"Mei",
                    6=>"Juni",
                    7=>"Juli",
                    8=>"Agustus",
                    9=>"September",
                    10=>"Oktober",
                    11=>"November",
                    12=>"Desember",
                ][$this->bulan]." ".$this->tahun,
        };
    }
}
