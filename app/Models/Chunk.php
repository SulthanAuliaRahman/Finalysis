<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chunk extends Model
{
    use HasFactory;

    protected $table = 'chunks';

    public $timestamps = false;

    protected $fillable = [
        'dokumen_id',
        'chunk_index',
        'text',
        'metadata',
        'has_table',
    ];

    protected $casts = [
        'metadata' => 'array',
        'has_table' => 'boolean',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }
}
