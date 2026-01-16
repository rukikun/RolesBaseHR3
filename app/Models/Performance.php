<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Performance extends Model
{
    protected $fillable = [
        'metric_date',
        'attendance_rate',
        'productivity_score',
        'quality_score',
        'overtime_hours',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'attendance_rate' => 'float',
        'productivity_score' => 'float',
        'quality_score' => 'float',
        'overtime_hours' => 'float',
    ];
}
