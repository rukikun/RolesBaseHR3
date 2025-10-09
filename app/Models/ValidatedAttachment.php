<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class ValidatedAttachment extends Model
{
    use HasFactory;

    // Explicitly specify the database connection
    protected $connection = 'mysql';
    protected $table = 'validated_attachments';
    
    protected $fillable = [
        'claim_id',
        'employee_id',
        'employee_name',
        'claim_type',
        'amount',
        'claim_date',
        'description',
        'attachment_path',
        'status',
        'validated_at',
        'sent_to_payroll_at',
        'validated_by'
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        // Ensure we're using the correct database from config
        $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'claim_date' => 'date',
        'validated_at' => 'datetime',
        'sent_to_payroll_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    // Scopes
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopeSentToPayroll($query)
    {
        return $query->where('status', 'sent_to_payroll');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'validated' => 'success',
            'sent_to_payroll' => 'info',
            'processed' => 'primary',
            default => 'secondary'
        };
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    // Helper methods
    public function canBeSentToPayroll()
    {
        return $this->status === 'validated';
    }

    public function canBeProcessed()
    {
        return $this->status === 'sent_to_payroll';
    }

    public function isValidated()
    {
        return $this->status === 'validated';
    }

    public function isSentToPayroll()
    {
        return $this->status === 'sent_to_payroll';
    }

    public function isProcessed()
    {
        return $this->status === 'processed';
    }
}
