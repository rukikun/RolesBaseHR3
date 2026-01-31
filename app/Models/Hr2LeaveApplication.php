<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hr2LeaveApplication extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr2_leave_applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hr2_id',
        'employee_id',
        'leave_id',
        'application_date',
        'leave_type',
        'leave_days',
        'days_requested',
        'status',
        'reason',
        'start_date',
        'end_date',
        'contact_info',
        'applied_date',
        'approved_by',
        'approved_date',
        'remarks',
        'hr2_created_at',
        'hr2_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'application_date' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'applied_date' => 'datetime',
        'approved_date' => 'datetime',
        'hr2_created_at' => 'datetime',
        'hr2_updated_at' => 'datetime',
        'leave_days' => 'integer',
        'days_requested' => 'integer',
    ];

    /**
     * Get the status badge class based on status.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match(strtolower($this->status)) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected', 'declined' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Scope to get only pending applications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope to get only approved applications.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    /**
     * Scope to get only rejected applications.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }
}
