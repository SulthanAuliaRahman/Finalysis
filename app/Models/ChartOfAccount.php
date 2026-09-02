<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'dokumen_id',
        'nama_akun',
        'kelompok_akun',
        'sub_kelompok_akun',
        'nilai_akun',
    ];

    protected $casts = [
        'nilai_akun' => 'decimal:2',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
}
