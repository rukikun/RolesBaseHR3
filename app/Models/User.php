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
        'employee_id',
        'username',
        'role',
        'last_login',
        'is_active',
        'profile_picture'
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
            'is_active' => 'boolean'
        ];
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
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
}
