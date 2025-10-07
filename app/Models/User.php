<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'username',
        'role',
        'last_login',
        'is_active',
        'profile_picture',
        'job_title',
        'department',
        'manager_id',
        'work_location',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login' => 'datetime',
            'is_active' => 'boolean',
            'date_of_birth' => 'date'
        ];
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class)->orderBy('performed_at', 'desc');
    }

    public function recentActivities($limit = 10)
    {
        return $this->activities()->limit($limit);
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function getPreference($key, $default = null)
    {
        return UserPreference::getPreference($this->id, $key, $default);
    }

    public function setPreference($key, $value, $type = 'string')
    {
        return UserPreference::setPreference($this->id, $key, $value, $type);
    }

    public function getAllPreferences()
    {
        return UserPreference::getUserPreferences($this->id);
    }

    // Role-based helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isHRManager()
    {
        return $this->role === 'hr_manager';
    }

    public function isHRScheduler()
    {
        return $this->role === 'hr_scheduler';
    }

    public function isAttendanceAdmin()
    {
        return $this->role === 'attendance_admin';
    }

    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    public function canManageUsers()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canAccessModule($module)
    {
        $permissions = [
            'super_admin' => ['all'],
            'admin' => ['employees', 'timesheets', 'shifts', 'leave', 'claims', 'reports'],
            'hr_manager' => ['employees', 'leave', 'claims', 'reports'],
            'hr_scheduler' => ['shifts', 'timesheets', 'schedules'],
            'attendance_admin' => ['timesheets', 'attendance', 'leave']
        ];

        $userPermissions = $permissions[$this->role] ?? [];
        
        return in_array('all', $userPermissions) || in_array($module, $userPermissions);
    }

    // Get role display name
    public function getRoleDisplayName()
    {
        $roles = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'hr_manager' => 'HR Manager',
            'hr_scheduler' => 'HR Scheduler',
            'attendance_admin' => 'Attendance & Leave Admin'
        ];

        return $roles[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }

    // Get activity icon based on activity type
    public function getActivityIcon($activityType = null)
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
            'data_import' => 'fas fa-upload text-secondary',
            'profile_view' => 'fas fa-user text-primary',
            'dashboard_view' => 'fas fa-tachometer-alt text-info'
        ];

        // If no activity type provided, return default user icon
        if (!$activityType) {
            return 'fas fa-user text-primary';
        }

        return $icons[$activityType] ?? 'fas fa-info-circle text-muted';
    }
}
