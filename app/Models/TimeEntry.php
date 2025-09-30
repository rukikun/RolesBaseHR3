<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
        'hours_worked',
        'overtime_hours',
        'status',
        'notes'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Accessors
    public function getTotalHoursAttribute()
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return null;
        }

        try {
            $clockIn = Carbon::createFromFormat('H:i:s', $this->clock_in_time);
            $clockOut = Carbon::createFromFormat('H:i:s', $this->clock_out_time);
            
            // Handle overnight shifts
            if ($clockOut->lt($clockIn)) {
                $clockOut->addDay();
            }

            $totalMinutes = $clockOut->diffInMinutes($clockIn) - ($this->break_duration ?? 0);
            return round($totalMinutes / 60, 2);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getFormattedClockInAttribute()
    {
        if (!$this->clock_in_time) return null;
        
        try {
            // Handle both TIME format (HH:MM:SS) and time string
            if (is_string($this->clock_in_time)) {
                return Carbon::createFromFormat('H:i:s', $this->clock_in_time)->format('g:i A');
            }
            return Carbon::parse($this->clock_in_time)->format('g:i A');
        } catch (Exception $e) {
            return $this->clock_in_time;
        }
    }

    public function getFormattedClockOutAttribute()
    {
        if (!$this->clock_out_time) return null;
        
        try {
            // Handle both TIME format (HH:MM:SS) and time string
            if (is_string($this->clock_out_time)) {
                return Carbon::createFromFormat('H:i:s', $this->clock_out_time)->format('g:i A');
            }
            return Carbon::parse($this->clock_out_time)->format('g:i A');
        } catch (Exception $e) {
            return $this->clock_out_time;
        }
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('work_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('work_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('work_date', now()->month)
                    ->whereYear('work_date', now()->year);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Helper methods
    public function clockIn($time = null)
    {
        $this->clock_in_time = $time ?? now()->format('H:i:s');
        $this->save();
        return $this;
    }

    public function clockOut($time = null)
    {
        $this->clock_out_time = $time ?? now()->format('H:i:s');
        $this->save();
        return $this;
    }

    public function approve($approver_id)
    {
        $this->status = 'approved';
        $this->approved_by = $approver_id;
        $this->approved_at = now();
        $this->save();
        return $this;
    }
}
