<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $table = 'user_preferences';

    protected $fillable = [
        'user_id',
        'preference_key',
        'preference_value',
        'preference_type'
    ];

    protected $casts = [
        'preference_value' => 'json'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods for common preferences
    public static function setPreference($userId, $key, $value, $type = 'string')
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'preference_key' => $key],
            ['preference_value' => $value, 'preference_type' => $type]
        );
    }

    public static function getPreference($userId, $key, $default = null)
    {
        $preference = self::where('user_id', $userId)
                         ->where('preference_key', $key)
                         ->first();

        return $preference ? $preference->preference_value : $default;
    }

    public static function getUserPreferences($userId)
    {
        return self::where('user_id', $userId)
                  ->pluck('preference_value', 'preference_key')
                  ->toArray();
    }

    // Common preference keys
    const THEME = 'theme';
    const LANGUAGE = 'language';
    const TIMEZONE = 'timezone';
    const DATE_FORMAT = 'date_format';
    const TIME_FORMAT = 'time_format';
    const NOTIFICATIONS_EMAIL = 'notifications_email';
    const NOTIFICATIONS_BROWSER = 'notifications_browser';
    const DASHBOARD_LAYOUT = 'dashboard_layout';
    const RECORDS_PER_PAGE = 'records_per_page';

    // Default preferences
    public static function getDefaultPreferences()
    {
        return [
            self::THEME => 'light',
            self::LANGUAGE => 'en',
            self::TIMEZONE => 'Asia/Manila',
            self::DATE_FORMAT => 'Y-m-d',
            self::TIME_FORMAT => '24',
            self::NOTIFICATIONS_EMAIL => true,
            self::NOTIFICATIONS_BROWSER => true,
            self::DASHBOARD_LAYOUT => 'default',
            self::RECORDS_PER_PAGE => 25
        ];
    }

    // Apply user preferences to the application
    public static function applyPreferences($userId)
    {
        $preferences = self::getUserPreferences($userId);
        $defaults = self::getDefaultPreferences();
        
        // Merge with defaults
        $preferences = array_merge($defaults, $preferences);
        
        // Apply timezone if set
        if (isset($preferences[self::TIMEZONE])) {
            config(['app.timezone' => $preferences[self::TIMEZONE]]);
        }
        
        return $preferences;
    }
}
