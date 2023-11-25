<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_type_id',
        'title',
        'description',
        'is_active'
    ];

    public function jobPosition(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }
}
