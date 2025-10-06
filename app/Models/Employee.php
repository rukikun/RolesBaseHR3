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
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'hire_date',
        'salary',
        'status',
        'role',
        'online_status',
        'last_activity',
        'password',
        'profile_picture',
        'address',
        'date_of_birth',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_account_number',
        'tax_id'
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

    public function user()
    {
        return $this->hasOne(User::class);
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

    public function isHR()
    {
        return $this->role === 'hr';
    }

    public function isManager()
    {
        return $this->role === 'manager';
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
