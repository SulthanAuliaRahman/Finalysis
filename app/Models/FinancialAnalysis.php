<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAnalysis extends Model
{
    protected $fillable = [
        'financial_report_id',
        'analysis_result',
        'ratio_results',
    ];

    protected $casts = [
        'ratio_results' => 'array',
    ];
}
