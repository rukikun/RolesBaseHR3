<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePreference extends Model
{
    use HasFactory;

    protected $table = 'employee_preferences';

    protected $fillable = [
        'employee_id',
        'preference_key',
        'preference_value',
        'preference_type'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Helper methods for common preferences
    public static function setPreference($employeeId, $key, $value, $type = 'string')
    {
        return self::updateOrCreate(
            ['employee_id' => $employeeId, 'preference_key' => $key],
            ['preference_value' => $value, 'preference_type' => $type]
        );
    }

    public static function getPreference($employeeId, $key, $default = null)
    {
        $preference = self::where('employee_id', $employeeId)
                         ->where('preference_key', $key)
                         ->first();

        return $preference ? $preference->preference_value : $default;
    }

    public static function getEmployeePreferences($employeeId)
    {
        return self::where('employee_id', $employeeId)
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

    // Apply employee preferences to the application
    public static function applyPreferences($employeeId)
    {
        $preferences = self::getEmployeePreferences($employeeId);
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
