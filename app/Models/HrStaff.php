<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrStaff extends Model
{
    protected $table = 'hr_staffs';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'contact_number',
    ];

    // public function getActivitylogOptions(): LogOptions
    // {
    //     $logOptions = LogOptions::defaults()
    //     ->logFillable();

    //     $logOptions->setDescriptionForEvent(function (string $eventName) {
    //         $description = "{$eventName} a HR Staff account under the name of {$this->first_name} {$this->last_name}";

    //         return $description;
    //     });

    //     return $logOptions;
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
