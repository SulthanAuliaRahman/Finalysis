<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'company_name',
        'company_type',
        'company_scale',
        'description'
    ];

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function financial_reports() {
        return $this->hasMany(FinancialReport::class);
    }

}
