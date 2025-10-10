<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'timesheet_id',
        'employee_id',
        'employee_name',
        'department',
        'week_period',
        'week_start_date',
        'total_hours',
        'overtime_hours',
        'regular_rate',
        'overtime_rate',
        'regular_amount',
        'overtime_amount',
        'total_amount',
        'status',
        'processed_date',
        'processed_by',
        'timesheet_data'
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'processed_date' => 'datetime',
        'timesheet_data' => 'array',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'regular_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'regular_amount' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Calculate payroll amounts based on hours and rates
     */
    public function calculateAmounts()
    {
        $this->regular_amount = $this->total_hours * $this->regular_rate;
        $this->overtime_amount = $this->overtime_hours * $this->overtime_rate;
        $this->total_amount = $this->regular_amount + $this->overtime_amount;
        
        return $this;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute()
    {
        return "₱" . number_format($this->total_amount, 2);
    }

    /**
     * Scope for pending payroll items
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed payroll items
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}