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
}
