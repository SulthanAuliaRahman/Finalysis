<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analisis extends Model
{
    use HasFactory;

    protected $table = 'analisis';

    protected $fillable = [
        'dokumen_id',
        'ringkasan_laporan',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }

    public function likuiditas()
    {
        return $this->hasOne(AnalisisLikuiditas::class, 'analisis_id');
    }

    public function profitabilitas()
    {
        return $this->hasOne(AnalisisProfitabilitas::class, 'analisis_id');
    }

    public function solvabilitas()
    {
        return $this->hasOne(AnalisisSolvabilitas::class, 'analisis_id');
    }

    public function aktivitas()
    {
        return $this->hasOne(AnalisisAktivitas::class, 'analisis_id');
    }

    public function dupont()
    {
        return $this->hasOne(AnalisisDupont::class, 'analisis_id');
    }

    public function commonsize()
    {
        return $this->hasOne(AnalisisCommonsize::class, 'analisis_id');
    }

    public function trend()
    {
        return $this->hasOne(AnalisisTrend::class, 'analisis_id');
    }

    // Wkwkwk biar gak usah ganti gini secara terseludup panggil ke method Dokumen.
    public function getRasioTrend(): array
    {
        return $this->dokumen->getRasioTrend();
    }

    public function getDupontTrend(): array
    {
        return $this->dokumen->getDupontTrend();
    }

    public function getCommonsizeTrend(): array
    {
        return $this->dokumen->getCommonsizeTrend();
    }

    public function getAkunUtamaTrend(): array
    {
        return $this->dokumen->getAkunUtamaTrend();
    }
}