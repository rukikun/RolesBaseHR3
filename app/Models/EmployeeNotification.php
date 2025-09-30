<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeNotification extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'employee_notifications';

    protected $fillable = [
        'employee_id',
        'type',
        'title',
        'message',
        'sent_at',
        'read_at',
        'action_url',
        'priority'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Helper methods
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function getTypeIconClass()
    {
        return match($this->type) {
            'info' => 'fas fa-info-circle text-info',
            'success' => 'fas fa-check-circle text-success',
            'warning' => 'fas fa-exclamation-triangle text-warning',
            'error' => 'fas fa-times-circle text-danger',
            'reminder' => 'fas fa-bell text-primary',
            default => 'fas fa-envelope text-secondary'
        };
    }

    public function getPriorityBadgeClass()
    {
        return match($this->priority) {
            'high' => 'bg-danger',
            'medium' => 'bg-warning',
            'low' => 'bg-info',
            default => 'bg-secondary'
        };
    }
}
