<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'credential_id',
        'public_key',
        'authenticator_type',
        'authenticator_data',
        'device_name',
        'last_used_at',
        'is_active'
    ];

    protected $casts = [
        'authenticator_data' => 'array',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Get the employee that owns the biometric credential
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Check if employee has registered biometric credentials
     */
    public static function hasCredentials($employeeId): bool
    {
        return self::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get active credentials for employee
     */
    public static function getActiveCredentials($employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
