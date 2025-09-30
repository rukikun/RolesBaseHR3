<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'shift_date',
        'status',
        'notes',
        'requested_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('shift_date', $date);
    }
}
