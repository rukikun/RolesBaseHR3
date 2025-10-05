<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        // Load current settings from database or config
        $settings = $this->getCurrentSettings();
        
        return view('settings.settings', compact('settings'));
    }

    /**
     * Save system settings
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'system_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'standard_work_hours' => 'required|integer|min:1|max:24',
            'overtime_threshold' => 'required|integer|min:1|max:24',
            'late_grace_period' => 'required|integer|min:0|max:60',
            'break_duration' => 'required|integer|min:0|max:120',
            'overtime_rate' => 'required|numeric|min:1',
            'holiday_rate' => 'required|numeric|min:1',
            'night_differential' => 'required|integer|min:0|max:100',
            'password_min_length' => 'required|integer|min:6|max:20',
            'session_timeout' => 'required|integer|min:15|max:480',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle file upload
            if ($request->hasFile('company_logo')) {
                $logoPath = $request->file('company_logo')->store('logos', 'public');
                $request->merge(['company_logo_path' => $logoPath]);
            }

            // Save settings to database or config
            $this->saveSettings($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export settings as JSON
     */
    public function export()
    {
        try {
            $settings = $this->getCurrentSettings();
            
            $filename = 'hr3_settings_' . date('Y-m-d_H-i-s') . '.json';
            
            return response()->json($settings)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import settings from JSON file
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings_file' => 'required|file|mimes:json|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('settings_file');
            $content = file_get_contents($file->getRealPath());
            $settings = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON file');
            }

            // Validate and save imported settings
            $this->saveSettings($settings);

            return response()->json([
                'success' => true,
                'message' => 'Settings imported successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function reset()
    {
        try {
            $defaultSettings = $this->getDefaultSettings();
            $this->saveSettings($defaultSettings);

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resetting settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current settings from database/config
     */
    private function getCurrentSettings()
    {
        // This would typically load from a settings table or config files
        // For now, return default values
        return $this->getDefaultSettings();
    }

    /**
     * Save settings to database/config
     */
    private function saveSettings($settings)
    {
        // This would typically save to a settings table or update config files
        // For now, we'll simulate saving
        
        // You could implement this by:
        // 1. Creating a settings table and storing key-value pairs
        // 2. Updating .env file for environment-specific settings
        // 3. Creating a JSON config file
        
        // Example implementation with settings table:
        /*
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        */
        
        return true;
    }

    /**
     * Get default system settings
     */
    private function getDefaultSettings()
    {
        return [
            // General Settings
            'system_name' => 'HR3 System',
            'default_language' => 'en',
            'date_format' => 'Y-m-d',
            'time_format' => '24',
            'timezone' => 'Asia/Manila',
            'records_per_page' => '25',

            // Company Settings
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_website' => '',
            'company_tax_id' => '',
            'company_industry' => '',

            // Attendance Settings
            'standard_work_hours' => 8,
            'overtime_threshold' => 8,
            'late_grace_period' => 15,
            'break_duration' => 60,
            'require_location' => false,
            'allow_mobile_clockin' => true,
            'auto_clock_out' => false,
            'weekend_overtime' => false,

            // Payroll Settings
            'pay_period' => 'monthly',
            'currency' => 'PHP',
            'overtime_rate' => 1.5,
            'holiday_rate' => 2.0,
            'night_differential' => 10,
            'auto_payroll' => false,

            // Notification Settings
            'email_attendance' => true,
            'email_leave' => true,
            'email_payroll' => false,
            'system_alerts' => true,
            'maintenance_notices' => true,
            'notification_frequency' => 'immediate',

            // Security Settings
            'password_min_length' => 8,
            'session_timeout' => 120,
            'max_login_attempts' => 5,
            'require_2fa' => false,
            'password_complexity' => true,
            'audit_logging' => true,
        ];
    }
}
