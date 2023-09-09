<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'job_type_id',
        'employee_type_id',
        'industry_id',
        'description',
        'company_profile',
        'location',
        'job_type',
        'employment_type',
        'schedule'
    ];
}
