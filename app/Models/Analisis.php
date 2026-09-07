<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analisis extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'analisis';

    protected $fillable = [
        'dokumen_id',
        'ringkasan_laporan',
        'status_generate',
        'progress_current',
        'progress_total',
        'error_message',
        'section_status',
    ];

    protected $casts = [
        'section_status' => 'array',
    ];

    public const SECTIONS = [
        'likuiditas', 'profitabilitas', 'solvabilitas', 'aktivitas',
        'dupont', 'commonsize',
        'trend_akun_utama', 'trend_rasio', 'trend_dupont', 'trend_commonsize',
        'summary',
    ];

    // 4 section ini tidak wajib untuk summary, dan di-skip total kalau periode pertama
    public const TREND_SECTIONS = [
        'trend_akun_utama', 'trend_rasio', 'trend_dupont', 'trend_commonsize',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }

    public function likuiditas() { return $this->hasOne(AnalisisLikuiditas::class, 'analisis_id'); }
    public function profitabilitas() { return $this->hasOne(AnalisisProfitabilitas::class, 'analisis_id'); }
    public function solvabilitas() { return $this->hasOne(AnalisisSolvabilitas::class, 'analisis_id'); }
    public function aktivitas() { return $this->hasOne(AnalisisAktivitas::class, 'analisis_id'); }
    public function dupont() { return $this->hasOne(AnalisisDupont::class, 'analisis_id'); }
    public function commonsize() { return $this->hasOne(AnalisisCommonsize::class, 'analisis_id'); }
    public function trend() { return $this->hasOne(AnalisisTrend::class, 'analisis_id'); }

    public function getRasioTrend(): array { return $this->dokumen->getRasioTrend(); }
    public function getDupontTrend(): array { return $this->dokumen->getDupontTrend(); }
    public function getCommonsizeTrend(): array { return $this->dokumen->getCommonsizeTrend(); }
    public function getAkunUtamaTrend(): array { return $this->dokumen->getAkunUtamaTrend(); }


    public function apakahPeriodePertama(): bool
    {
        $dokumen = $this->dokumen;

        return !Dokumen::query()
            ->where('perusahaan_id', $dokumen->perusahaan_id)
            ->where('id', '!=', $dokumen->id)
            ->where('tahun', '<', $dokumen->tahun)
            ->exists();
    }

    public function sectionStatus(string $section): array
    {
        return $this->section_status[$section] ?? ['status' => 'idle', 'error_message' => null];
    }

    public function updateSectionStatus(string $section, string $status, ?string $errorMessage = null): void
    {
        $current = $this->section_status ?? [];
        $current[$section] = ['status' => $status, 'error_message' => $errorMessage];
        $this->update(['section_status' => $current]);
    }

    /**
     * Sumber kebenaran "section ini sudah berhasil" -> narasi AI-nya terisi.
     */
    public function narasiSudahAda(string $section): bool
    {
        $this->loadMissing([
            'likuiditas', 'profitabilitas', 'solvabilitas', 'aktivitas',
            'dupont', 'commonsize', 'trend',
        ]);

        return match ($section) {
            'likuiditas'       => filled($this->likuiditas?->narasi_likuiditas_AI),
            'profitabilitas'   => filled($this->profitabilitas?->narasi_profitabilitas_AI),
            'solvabilitas'     => filled($this->solvabilitas?->narasi_solvabilitas_AI),
            'aktivitas'        => filled($this->aktivitas?->narasi_aktivitas_AI),
            'dupont'           => filled($this->dupont?->narasi_dupont_AI),
            'commonsize'       => filled($this->commonsize?->narasi_commonsize_AI),
            'trend_akun_utama' => filled($this->trend?->narasi_trend_akun_utama_AI),
            'trend_rasio'      => filled($this->trend?->narasi_trend_rasio_AI),
            'trend_dupont'     => filled($this->trend?->narasi_trend_dupont_AI),
            'trend_commonsize' => filled($this->trend?->narasi_trend_commonsize_AI),
            'summary'          => filled($this->ringkasan_laporan),
            default            => false,
        };
    }

    /**
     * Syarat summary: semua section SELAIN trend sudah ada narasinya.
     */
    public function siapUntukSummary(): bool
    {
        $wajib = array_diff(self::SECTIONS, ['summary'], self::TREND_SECTIONS);

        foreach ($wajib as $section) {
            if (!$this->narasiSudahAda($section)) {
                return false;
            }
        }

        return true;
    }

    public function sectionsBelumSelesai(): array
    {
        return array_values(array_filter(
            self::SECTIONS,
            fn (string $section) => !in_array($this->sectionStatus($section)['status'], ['selesai', 'tidak_berlaku'])
        ));
    }

    /**
     * Semua section "selesai" secara final -> selesai berhasil ATAU tidak_berlaku (trend di periode pertama).
     * Dipakai buat nentuin status_generate keseluruhan & buat mengizinkan regenerasi (poin 5).
     */
    public function semuaSelesai(): bool
    {
        foreach (self::SECTIONS as $section) {
            $status = $this->sectionStatus($section)['status'];
            if (!in_array($status, ['selesai', 'tidak_berlaku'])) {
                return false;
            }
        }

        return true;
    }
}
