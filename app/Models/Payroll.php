<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class Payroll extends Model
{
    use HasFactory;

    // Explicitly specify the database connection
    protected $connection = 'mysql';
    protected $table = 'payroll';
    
    protected $fillable = [
        'timesheet_id',
        'employee_id',
        'department',
        'week_period',
        'total_hours',
        'overtime_hours',
        'hourly_rate',
        'overtime_rate',
        'regular_amount',
        'overtime_amount',
        'total_amount',
        'status',
        'processed_at',
        'paid_at',
        'notes'
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using the correct database from config
        $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
    }

    protected $casts = [
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'regular_amount' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByWeekPeriod($query, $weekPeriod)
    {
        return $query->where('week_period', $weekPeriod);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'processed' => 'success',
            'paid' => 'primary',
            default => 'secondary'
        };
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getFormattedRegularAmountAttribute()
    {
        return '$' . number_format($this->regular_amount, 2);
    }

    public function getFormattedOvertimeAmountAttribute()
    {
        return '$' . number_format($this->overtime_amount, 2);
    }

    // Mutators
    public function setTotalAmountAttribute($value)
    {
        $this->attributes['total_amount'] = round($value, 2);
    }

    public function setRegularAmountAttribute($value)
    {
        $this->attributes['regular_amount'] = round($value, 2);
    }

    public function setOvertimeAmountAttribute($value)
    {
        $this->attributes['overtime_amount'] = round($value, 2);
    }

    // Helper methods
    public function calculateTotalAmount()
    {
        return $this->regular_amount + $this->overtime_amount;
    }

    public function canBeProcessed()
    {
        return $this->status === 'pending';
    }

    public function canBePaid()
    {
        return $this->status === 'processed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isProcessed()
    {
        return $this->status === 'processed';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }
}
