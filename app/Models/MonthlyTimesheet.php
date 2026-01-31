<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MonthlyTimesheet extends Model
{
    use HasFactory;

    protected $table = 'monthly_timesheets';

    protected $fillable = [
        'employee_id',
        'employee_name',
        'department',
        'month_start_date',
        'total_hours',
        'overtime_hours',
        'timesheet_count',
        'source_timesheet_ids',
        'generated_at'
    ];

    protected $casts = [
        'month_start_date' => 'date',
        'generated_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'source_timesheet_ids' => 'array'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForMonth($query, Carbon $month)
    {
        return $query->where('month_start_date', $month->startOfMonth()->format('Y-m-d'));
    }
}
