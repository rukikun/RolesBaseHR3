<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSwapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'target_employee_id',
        'requester_shift_id',
        'target_shift_id',
        'shift_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function targetEmployee()
    {
        return $this->belongsTo(Employee::class, 'target_employee_id');
    }

    public function requesterShift()
    {
        return $this->belongsTo(Shift::class, 'requester_shift_id');
    }

    public function targetShift()
    {
        return $this->belongsTo(Shift::class, 'target_shift_id');
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
}
