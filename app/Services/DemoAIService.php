<?php

namespace App\Services;

class DemoAIService
{
    public function analyzeTask($description)
    {
        // Demo responses based on common HR tasks
        $responses = [
            'Meeting' => 'Administrative - Team coordination and planning',
            'Email' => 'Communication - Client and internal correspondence', 
            'Report' => 'Analysis - Data compilation and documentation',
            'Training' => 'Development - Skill building and education',
            'Review' => 'Evaluation - Performance assessment and feedback'
        ];
        
        foreach ($responses as $keyword => $category) {
            if (stripos($description, strtolower($keyword)) !== false) {
                return $category;
            }
        }
        
        return 'General - Productive work activity';
    }
    
    public function getTimeInsights($timeEntries)
    {
        $totalHours = count($timeEntries) * 1.5; // Demo calculation
        
        return [
            'total_hours' => $totalHours,
            'productivity_score' => rand(75, 95),
            'peak_hours' => '10:00 AM - 12:00 PM',
            'suggestions' => [
                'Consider taking breaks every 2 hours for optimal productivity',
                'Your most productive time appears to be mid-morning',
                'Try batching similar tasks together for better efficiency'
            ]
        ];
    }
    
    public function validateClaim($claimData)
    {
        $amount = $claimData['amount'] ?? 0;
        
        if ($amount > 1000) {
            return [
                'status' => 'requires_review',
                'confidence' => 85,
                'notes' => 'High-value claim requires manager approval'
            ];
        }
        
        return [
            'status' => 'approved',
            'confidence' => 92,
            'notes' => 'Standard expense within policy limits'
        ];
    }
    
    public function generateTimesheetSummary($entries)
    {
        $totalHours = count($entries) * 8;
        $categories = ['Development', 'Meetings', 'Administration', 'Training'];
        
        return [
            'total_hours' => $totalHours,
            'breakdown' => [
                'Development' => $totalHours * 0.6,
                'Meetings' => $totalHours * 0.2,
                'Administration' => $totalHours * 0.15,
                'Training' => $totalHours * 0.05
            ],
            'summary' => "Productive week with focus on core development tasks. Good balance of collaborative and individual work.",
            'recommendations' => [
                'Maintain current development focus',
                'Consider scheduling fewer meetings to maximize deep work time'
            ]
        ];
    }
    
    public function optimizeSchedule($currentSchedule)
    {
        return [
            'optimized_schedule' => [
                '09:00-11:00' => 'Deep Work (Development)',
                '11:00-12:00' => 'Meetings & Collaboration', 
                '13:00-15:00' => 'Project Work',
                '15:00-16:00' => 'Administrative Tasks',
                '16:00-17:00' => 'Planning & Review'
            ],
            'efficiency_gain' => '15%',
            'reasoning' => 'Scheduled demanding tasks during peak energy hours'
        ];
    }
    
    public function estimateProjectTime($projectDescription)
    {
        // Simple estimation based on keywords
        $complexity = 1;
        if (stripos($projectDescription, 'complex') !== false) $complexity = 2;
        if (stripos($projectDescription, 'simple') !== false) $complexity = 0.5;
        
        $baseHours = 40;
        $estimatedHours = $baseHours * $complexity;
        
        return [
            'estimated_hours' => $estimatedHours,
            'confidence' => 80,
            'breakdown' => [
                'Planning' => $estimatedHours * 0.2,
                'Development' => $estimatedHours * 0.6,
                'Testing' => $estimatedHours * 0.15,
                'Documentation' => $estimatedHours * 0.05
            ]
        ];
    }
    
    public function analyzeTeamProductivity($teamData)
    {
        return [
            'overall_score' => rand(78, 88),
            'trends' => [
                'productivity' => 'increasing',
                'collaboration' => 'stable',
                'efficiency' => 'improving'
            ],
            'insights' => [
                'Team is showing consistent improvement in delivery times',
                'Communication patterns indicate good collaboration',
                'Workload distribution appears balanced'
            ],
            'recommendations' => [
                'Continue current practices',
                'Consider implementing pair programming for knowledge sharing',
                'Schedule regular team retrospectives'
            ]
        ];
    }
    
    public function generateSmartNotifications($userData)
    {
        $notifications = [
            [
                'type' => 'productivity_tip',
                'message' => 'Your productivity peaks at 10 AM. Schedule important tasks during this time.',
                'priority' => 'medium'
            ],
            [
                'type' => 'break_reminder', 
                'message' => 'You\'ve been working for 2 hours. Consider taking a 10-minute break.',
                'priority' => 'low'
            ],
            [
                'type' => 'deadline_alert',
                'message' => 'Project milestone due in 2 days. Current progress: 75% complete.',
                'priority' => 'high'
            ]
        ];
        
        return array_slice($notifications, 0, rand(1, 3));
    }
    
    public function testConnection()
    {
        return [
            'status' => 'connected',
            'service' => 'Demo AI Service',
            'message' => 'Demo mode active - all AI features working with sample data'
        ];
    }
}
