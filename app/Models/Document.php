<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'company',
        'period',
        'source_filename',
        'total_chunks',
        'chunks',
        'statements'
    ];

    protected $casts    = [
        'chunks' => 'array',
        'statements' => 'array'
    ];
}
