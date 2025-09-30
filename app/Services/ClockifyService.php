<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\OpenAIService;

class ClockifyService
{
    private $apiKey;
    private $baseUrl;
    private $workspaceId;
    private $openAIService;

    public function __construct()
    {
        $this->apiKey = config('services.clockify.api_key');
        $this->baseUrl = config('services.clockify.base_url', 'https://api.clockify.me/api/v1');
        $this->workspaceId = config('services.clockify.workspace_id');
        $this->openAIService = new OpenAIService();
    }

    /**
     * Get HTTP client with authentication headers
     */
    private function getHttpClient()
    {
        return Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Get user's workspaces
     */
    public function getWorkspaces()
    {
        try {
            $response = $this->getHttpClient()->get("{$this->baseUrl}/workspaces");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Clockify API Error: Failed to get workspaces', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current user info
     */
    public function getCurrentUser()
    {
        try {
            $response = $this->getHttpClient()->get("{$this->baseUrl}/user");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Start time entry
     */
    public function startTimeEntry($userId, $description = null, $projectId = null, $taskId = null)
    {
        try {
            $data = [
                'start' => Carbon::now()->toISOString(),
                'description' => $description ?? 'Work session',
            ];

            if ($projectId) {
                $data['projectId'] = $projectId;
            }

            if ($taskId) {
                $data['taskId'] = $taskId;
            }

            $response = $this->getHttpClient()->post(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/time-entries",
                $data
            );

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Clockify API Error: Failed to start time entry', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Stop current time entry
     */
    public function stopTimeEntry($userId)
    {
        try {
            $data = [
                'end' => Carbon::now()->toISOString()
            ];

            $response = $this->getHttpClient()->patch(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/user/{$userId}/time-entries",
                $data
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get time entries for a user
     */
    public function getTimeEntries($userId, $startDate = null, $endDate = null, $page = 1, $pageSize = 50)
    {
        try {
            $params = [
                'page' => $page,
                'page-size' => $pageSize,
            ];

            if ($startDate) {
                $params['start'] = Carbon::parse($startDate)->toISOString();
            }

            if ($endDate) {
                $params['end'] = Carbon::parse($endDate)->toISOString();
            }

            $response = $this->getHttpClient()->get(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/user/{$userId}/time-entries",
                $params
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get projects in workspace
     */
    public function getProjects()
    {
        try {
            $response = $this->getHttpClient()->get(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/projects"
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new project
     */
    public function createProject($name, $clientId = null, $color = '#FF5722')
    {
        try {
            $data = [
                'name' => $name,
                'color' => $color,
                'isPublic' => false,
            ];

            if ($clientId) {
                $data['clientId'] = $clientId;
            }

            $response = $this->getHttpClient()->post(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/projects",
                $data
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get workspace users
     */
    public function getWorkspaceUsers()
    {
        try {
            $response = $this->getHttpClient()->get(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/users"
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed time entries report
     */
    public function getDetailedReport($startDate, $endDate, $userIds = null)
    {
        try {
            $data = [
                'dateRangeStart' => Carbon::parse($startDate)->toISOString(),
                'dateRangeEnd' => Carbon::parse($endDate)->toISOString(),
                'detailedFilter' => [
                    'page' => 1,
                    'pageSize' => 1000,
                ],
            ];

            if ($userIds) {
                $data['users'] = ['ids' => $userIds];
            }

            $response = $this->getHttpClient()->post(
                "{$this->baseUrl}/workspaces/{$this->workspaceId}/reports/detailed",
                $data
            );

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Clockify Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if API connection is working
     */
    public function testConnection()
    {
        try {
            $user = $this->getCurrentUser();
            $workspaces = $this->getWorkspaces();
            
            return [
                'success' => $user !== null && $workspaces !== null,
                'user' => $user,
                'workspaces' => $workspaces,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Start AI-enhanced time entry with task categorization
     */
    public function startAITimeEntry($userId, $description = null, $projectId = null, $taskId = null)
    {
        try {
            // Use AI to categorize and enhance the task description
            if ($description) {
                $aiAnalysis = $this->openAIService->categorizeTimeEntry($description);
                if ($aiAnalysis['success']) {
                    Log::info('AI Task Categorization', ['analysis' => $aiAnalysis['content']]);
                }
            }

            return $this->startTimeEntry($userId, $description, $projectId, $taskId);
        } catch (\Exception $e) {
            Log::error('AI-Enhanced Clockify Service Error: ' . $e->getMessage());
            return $this->startTimeEntry($userId, $description, $projectId, $taskId);
        }
    }

    /**
     * Get AI-enhanced time entries with productivity insights
     */
    public function getAITimeEntries($userId, $startDate = null, $endDate = null, $page = 1, $pageSize = 50)
    {
        try {
            $entries = $this->getTimeEntries($userId, $startDate, $endDate, $page, $pageSize);
            
            if ($entries && !empty($entries)) {
                // Prepare data for AI analysis
                $timeData = [];
                foreach ($entries as $entry) {
                    $timeData[] = [
                        'date' => $entry['timeInterval']['start'] ?? '',
                        'duration' => $entry['timeInterval']['duration'] ?? '',
                        'description' => $entry['description'] ?? ''
                    ];
                }

                // Get AI insights
                $insights = $this->openAIService->generateTimeInsights($timeData);
                
                return [
                    'entries' => $entries,
                    'ai_insights' => $insights
                ];
            }

            return ['entries' => $entries, 'ai_insights' => null];
        } catch (\Exception $e) {
            Log::error('AI-Enhanced Time Entries Error: ' . $e->getMessage());
            return ['entries' => $this->getTimeEntries($userId, $startDate, $endDate, $page, $pageSize), 'ai_insights' => null];
        }
    }

    /**
     * Generate AI-powered productivity report
     */
    public function generateProductivityReport($userId, $startDate, $endDate)
    {
        try {
            $report = $this->getDetailedReport($startDate, $endDate, [$userId]);
            
            if ($report && isset($report['timeentries'])) {
                // Prepare data for AI analysis
                $productivityData = [];
                foreach ($report['timeentries'] as $entry) {
                    $productivityData[] = [
                        'date' => $entry['timeInterval']['start'] ?? '',
                        'duration' => $entry['timeInterval']['duration'] ?? '',
                        'description' => $entry['description'] ?? '',
                        'project' => $entry['project']['name'] ?? 'No Project'
                    ];
                }

                // Get AI analysis
                $aiReport = $this->openAIService->generateReport($productivityData, 'productivity');
                
                return [
                    'clockify_data' => $report,
                    'ai_analysis' => $aiReport,
                    'generated_at' => Carbon::now()->toISOString()
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('AI Productivity Report Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get AI-powered schedule suggestions
     */
    public function getScheduleSuggestions($userId, $days = 7)
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subDays($days);
            
            $entries = $this->getTimeEntries($userId, $startDate, $endDate, 1, 100);
            
            if ($entries) {
                // Analyze productivity patterns
                $productivityData = [];
                foreach ($entries as $entry) {
                    $start = Carbon::parse($entry['timeInterval']['start']);
                    $productivityData[] = [
                        'hour' => $start->hour,
                        'day_of_week' => $start->dayOfWeek,
                        'duration' => $entry['timeInterval']['duration'] ?? '',
                        'description' => $entry['description'] ?? ''
                    ];
                }

                return $this->openAIService->suggestOptimalSchedule($productivityData);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('AI Schedule Suggestions Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate time entry with AI
     */
    public function validateTimeEntry($description, $duration, $project = null)
    {
        try {
            $context = "Task: {$description}, Duration: {$duration}";
            if ($project) {
                $context .= ", Project: {$project}";
            }

            return $this->openAIService->extractInsights($context, 'time_entry_validation');
        } catch (\Exception $e) {
            Log::error('AI Time Entry Validation Error: ' . $e->getMessage());
            return ['success' => false, 'content' => 'Validation temporarily unavailable'];
        }
    }

    /**
     * Get AI-powered project time estimates
     */
    public function estimateProjectTime($projectName, $description, $requirements = [])
    {
        try {
            return $this->openAIService->estimateProjectTime($description, $requirements);
        } catch (\Exception $e) {
            Log::error('AI Project Time Estimation Error: ' . $e->getMessage());
            return ['success' => false, 'content' => 'Time estimation temporarily unavailable'];
        }
    }
}
