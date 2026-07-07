<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReport extends Model
{
    protected $casts = [
        'financial_data' => 'array',
    ];
}
