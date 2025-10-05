<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DatabaseConnectionTrait;

class LeaveType extends Model
{
    use HasFactory, DatabaseConnectionTrait;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_days_per_year',
        'carry_forward',
        'requires_approval',
        'requires_medical_certificate',
        'min_notice_days',
        'max_consecutive_days',
        'is_paid',
        'color_code',
        'is_active'
    ];

    protected $casts = [
        'max_days_per_year' => 'integer',
        'carry_forward' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_medical_certificate' => 'boolean',
        'min_notice_days' => 'integer',
        'max_consecutive_days' => 'integer',
        'is_paid' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // Helper methods
    public function getFormattedMaxDaysAttribute()
    {
        return $this->max_days_per_year . ' days per year';
    }

    public function canCarryForward()
    {
        return $this->carry_forward;
    }

    public function needsApproval()
    {
        return $this->requires_approval;
    }

    public function needsMedicalCertificate()
    {
        return $this->requires_medical_certificate;
    }
}
