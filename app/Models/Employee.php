<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use App\Traits\DatabaseConnectionTrait;

class Employee extends Authenticatable implements AuthenticatableContract
{
    use HasFactory, DatabaseConnectionTrait;

    protected $connection = 'mysql';
    protected $table = 'employees';
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using the correct database
        try {
            $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
            \Illuminate\Support\Facades\Config::set('database.connections.mysql.database', $database);
            \Illuminate\Support\Facades\DB::purge('mysql');
        } catch (\Exception $e) {
            // Ignore connection setup errors during model instantiation
        }
    }

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'email',
        'username',
        'phone',
        'position',
        'department',
        'work_location',
        'manager_id',
        'hire_date',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'salary',
        'status',
        'role',
        'online_status',
        'last_activity',
        'password',
        'profile_picture',
        'profile_picture_url',
        'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'last_activity' => 'datetime',
        'hire_date' => 'date',
        'date_of_birth' => 'date',
        'password' => 'hashed',
    ];

    // Relationships
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'employee_shifts')
                    ->withPivot('shift_date', 'notes', 'status')
                    ->withTimestamps();
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function timesheetDetails()
    {
        return $this->hasMany(EmployeeTimesheetDetail::class);
    }


    // ESS-related relationships
    public function trainings()
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function trainingPrograms()
    {
        return $this->belongsToMany(TrainingProgram::class, 'employee_trainings', 'employee_id', 'training_id')
                    ->withPivot('status', 'start_date', 'end_date', 'completion_date', 'score')
                    ->withTimestamps();
    }

    public function competencyAssessments()
    {
        return $this->hasMany(CompetencyAssessment::class);
    }

    public function employeeRequests()
    {
        return $this->hasMany(EmployeeRequest::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function notifications()
    {
        return $this->hasMany(EmployeeNotification::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnline($query)
    {
        return $query->where('online_status', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('online_status', 'offline');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // Helper methods
    public function getTodayTimeEntry()
    {
        return $this->timeEntries()->whereDate('work_date', today())->first();
    }

    public function isClockedIn()
    {
        $todayEntry = $this->getTodayTimeEntry();
        return $todayEntry && $todayEntry->clock_in_time && !$todayEntry->clock_out_time;
    }

    public function getLeaveBalance($leaveTypeId)
    {
        return $this->leaveBalances()
                    ->where('leave_type_id', $leaveTypeId)
                    ->where('year', date('Y'))
                    ->first();
    }

    // Online status methods
    public function setOnline()
    {
        $this->update([
            'online_status' => 'online',
            'last_activity' => now()
        ]);
        return $this;
    }

    public function setOffline()
    {
        $this->update([
            'online_status' => 'offline',
            'last_activity' => now()
        ]);
        return $this;
    }

    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
        return $this;
    }

    public function isOnline()
    {
        return $this->online_status === 'online';
    }

    // Role-based methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isHRManager()
    {
        return $this->role === 'hr_manager';
    }

    public function isHRScheduler()
    {
        return $this->role === 'hr_scheduler';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasAnyRole($roles)
    {
        return in_array($this->role, $roles);
    }

    public function canAccessDashboard()
    {
        return in_array($this->role, ['employee', 'admin', 'super_admin', 'hr_manager', 'hr_scheduler']);
    }

    // Accessor for full name
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Get profile picture URL - use the stored URL directly
    public function getProfilePictureUrlAttribute($value)
    {
        // If we have a stored profile_picture_url, return it
        if ($value && file_exists(public_path($value))) {
            return $value;
        }
        
        // Fallback to old profile_picture field for backward compatibility
        if ($this->attributes['profile_picture'] ?? null) {
            $oldPath = 'storage/' . $this->attributes['profile_picture'];
            if (file_exists(public_path($oldPath))) {
                return $oldPath;
            }
        }
        
        return null;
    }

    // Get user initials for fallback
    public function getInitialsAttribute()
    {
        $firstName = $this->first_name ?? '';
        $lastName = $this->last_name ?? '';
        
        $firstInitial = $firstName ? strtoupper(substr($firstName, 0, 1)) : '';
        $lastInitial = $lastName ? strtoupper(substr($lastName, 0, 1)) : '';
        
        return $firstInitial . $lastInitial;
    }

    // Activity relationship
    public function activities()
    {
        return $this->hasMany(EmployeeActivity::class);
    }

    public function recentActivities($limit = 10)
    {
        return $this->activities()->orderBy('performed_at', 'desc')->limit($limit);
    }

    // Manager relationship
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        if (! empty($this->getRememberTokenName())) {
            return (string) $this->{$this->getRememberTokenName()};
        }
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        if (! empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return $this->rememberTokenName ?? 'remember_token';
    }
}
