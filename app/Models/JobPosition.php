<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'schedule',
        'is_active'
    ];

    public function jobType(): BelongsTo
    {
        return $this->belongsTo(JobType::class);
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }
}
