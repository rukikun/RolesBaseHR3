<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'metadata',
        'performed_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Static method to log activities
    public static function log($type, $description, $metadata = [], $userId = null)
    {
        try {
            $userId = $userId ?: auth()->id();
            
            if (!$userId) {
                return null;
            }

            return self::create([
                'user_id' => $userId,
                'activity_type' => $type,
                'description' => $description,
                'metadata' => $metadata,
                'performed_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $e) {
            // Silently fail if logging fails
            \Log::error('UserActivity logging failed: ' . $e->getMessage());
            return null;
        }
    }

    // Scope for recent activities
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('performed_at', 'desc')->limit($limit);
    }

    // Scope for specific user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Get activity icon based on activity type
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
            'data_import' => 'fas fa-upload text-secondary',
            'profile_view' => 'fas fa-user text-primary',
            'dashboard_view' => 'fas fa-tachometer-alt text-info'
        ];

        return $icons[$this->activity_type] ?? 'fas fa-info-circle text-muted';
    }

    // Get formatted activity description with timestamp
    public function getFormattedDescription()
    {
        return $this->description . ' at ' . $this->performed_at->format('M d, Y h:i A');
    }
}
