<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EmployeeActivity extends Model
{
    use HasFactory;

    protected $table = 'employee_activities';

    protected $fillable = [
        'employee_id',
        'activity_type',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'performed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Activity logging helper methods
    public static function log($activityType, $description, $metadata = [])
    {
        return self::create([
            'employee_id' => Auth::id(),
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
            'performed_at' => now()
        ]);
    }

    public static function logLogin()
    {
        return self::log('login', 'User logged in');
    }

    public static function logLogout()
    {
        return self::log('logout', 'User logged out');
    }

    public static function logProfileUpdate($changes = [])
    {
        return self::log('profile_update', 'Profile information updated', $changes);
    }

    public static function logPasswordChange()
    {
        return self::log('password_change', 'Password changed');
    }

    public static function logRoleChange($oldRole, $newRole)
    {
        return self::log('role_change', "Role changed from {$oldRole} to {$newRole}", [
            'old_role' => $oldRole,
            'new_role' => $newRole
        ]);
    }

    public static function logAdminCreated($adminData)
    {
        return self::log('admin_created', 'New admin user created', $adminData);
    }

    public static function logAdminDeleted($adminData)
    {
        return self::log('admin_deleted', 'Admin user deleted', $adminData);
    }

    // Scope methods for filtering activities
    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    // Get activity icon based on type
    public function getActivityIcon()
    {
        $icons = [
            'login' => 'fas fa-sign-in-alt text-success',
            'logout' => 'fas fa-sign-out-alt text-info',
            'profile_update' => 'fas fa-user-edit text-primary',
            'password_change' => 'fas fa-key text-warning',
            'role_change' => 'fas fa-user-shield text-danger',
            'admin_created' => 'fas fa-user-plus text-success',
            'admin_deleted' => 'fas fa-user-minus text-danger',
            'settings_update' => 'fas fa-cogs text-info',
            'data_export' => 'fas fa-download text-secondary',
            'data_import' => 'fas fa-upload text-secondary'
        ];

        return $icons[$this->activity_type] ?? 'fas fa-info-circle text-muted';
    }

    // Get formatted activity description
    public function getFormattedDescription()
    {
        return $this->description . ' at ' . $this->performed_at->format('M d, Y h:i A');
    }
}
