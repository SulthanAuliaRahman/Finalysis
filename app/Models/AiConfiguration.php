<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AiConfiguration extends Model
{
    use HasFactory;

    protected $table = 'ai_configuration';

    protected $fillable = [
        'name',
        'llm_provider',
        'base_url',
        'llm_model',
        'llm_api_key',
        'is_active',
        'priority',
        'is_limited',
        'limited_until',
        'request_count',
        'last_request_at',
    ];

    protected $casts = [
        'llm_api_key'    => 'encrypted',
        'is_active'      => 'boolean',
        'is_limited'     => 'boolean',
        'limited_until'  => 'datetime',
        'last_request_at'=> 'datetime',
        'priority'       => 'integer',
        'request_count'  => 'integer',
    ];

    /* ──────────────────────────────────────────────
     *  Scopes
     * ────────────────────────────────────────────── */

    /**
     * Config yang sedang aktif (hanya boleh 1).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Config yang tidak sedang rate-limited.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_limited', false)
              ->orWhere('limited_until', '<=', now());
        });
    }

    /* ──────────────────────────────────────────────
     *  Instance Methods
     * ────────────────────────────────────────────── */

    /**
     * Tandai config ini sedang kena rate limit.
     */
    public function markAsLimited(int $cooldownMinutes = 60): void
    {
        $this->update([
            'is_limited'    => true,
            'limited_until' => now()->addMinutes($cooldownMinutes),
        ]);
    }

    /**
     * Hapus flag rate limit dari config ini.
     */
    public function clearLimit(): void
    {
        $this->update([
            'is_limited'    => false,
            'limited_until' => null,
        ]);
    }

    /**
     * Cek apakah rate limit sudah expired dan auto-reset.
     */
    public function isCurrentlyLimited(): bool
    {
        if (!$this->is_limited) {
            return false;
        }

        // Cooldown sudah lewat → auto-reset
        if ($this->limited_until && $this->limited_until->lte(now())) {
            $this->clearLimit();
            return false;
        }

        return true;
    }

    /**
     * Catat request baru pada config ini.
     */
    public function recordRequest(): void
    {
        $this->increment('request_count');
        $this->update(['last_request_at' => now()]);
    }

    /* ──────────────────────────────────────────────
     *  Static Methods
     * ────────────────────────────────────────────── */

    /**
     * Ambil config aktif. Jika sedang limited, cari pengganti.
     * Mengembalikan null jika semua config sedang limited.
     */
    public static function resolveActiveConfig(): ?self
    {
        $active = static::active()->first();

        // Tidak ada config aktif sama sekali
        if (!$active) {
            // Coba ambil config manapun yang available
            return static::getNextAvailable();
        }

        // Config aktif tidak sedang limited → gunakan
        if (!$active->isCurrentlyLimited()) {
            return $active;
        }

        // Config aktif sedang limited → cari pengganti
        return static::getNextAvailable($active);
    }

    /**
     * Cari config pengganti berdasarkan priority (ascending).
     * Exclude config yang sedang limited.
     */
    public static function getNextAvailable(?self $excludeConfig = null): ?self
    {
        $query = static::available()->orderBy('priority', 'asc');

        if ($excludeConfig) {
            $query->where('id', '!=', $excludeConfig->id);
        }

        $next = $query->first();

        if ($next) {
            // Auto-activate pengganti
            static::query()->update(['is_active' => false]);
            $next->update(['is_active' => true]);
        }

        return $next;
    }
}
