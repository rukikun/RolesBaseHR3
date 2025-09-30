<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    private $client;
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('OLLAMA_BASE_URL', 'http://localhost:11434');
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 60,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Test Ollama connection
     */
    public function testConnection()
    {
        try {
            $response = $this->client->get('/api/tags');
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'message' => 'Ollama connection successful',
                'models' => json_decode($response->getBody(), true)
            ];
        } catch (RequestException $e) {
            Log::error('Ollama connection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => $e->getCode(),
                'message' => 'Ollama connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate completion using Ollama
     */
    public function generateCompletion($prompt, $model = 'llama2', $options = [])
    {
        try {
            $defaultOptions = [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.3,
                    'top_p' => 0.9,
                    'max_tokens' => 500
                ]
            ];

            $requestData = array_merge($defaultOptions, $options);

            $response = $this->client->post('/api/generate', [
                'json' => $requestData
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'success' => true,
                'content' => $data['response'] ?? '',
                'model' => $data['model'] ?? $model
            ];

        } catch (RequestException $e) {
            Log::error('Ollama API request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'AI analysis temporarily unavailable'
            ];
        }
    }

    /**
     * Analyze time entry description and categorize tasks
     */
    public function categorizeTimeEntry($description, $project = null)
    {
        $prompt = "Analyze this work task and provide a brief categorization with productivity score (1-10):\n\nTask: {$description}";
        
        if ($project) {
            $prompt .= "\nProject: {$project}";
        }

        $prompt .= "\n\nProvide response in this format:\nCategory: [category]\nProductivity Score: [1-10]\nSuggestion: [brief improvement tip]";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Generate time tracking insights
     */
    public function generateTimeInsights($timeEntries)
    {
        $entriesText = '';
        foreach ($timeEntries as $entry) {
            $entriesText .= "Date: {$entry['date']}, Duration: {$entry['duration']}, Task: {$entry['description']}\n";
        }

        $prompt = "Analyze these time entries and provide 3 key insights about productivity patterns:\n\n{$entriesText}\n\nProvide brief, actionable insights.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Validate expense claims
     */
    public function validateClaim($claimData)
    {
        $prompt = "Review this expense claim for validity:\n\n";
        $prompt .= "Type: {$claimData['type']}\n";
        $prompt .= "Amount: {$claimData['amount']}\n";
        $prompt .= "Description: {$claimData['description']}\n";
        $prompt .= "Date: {$claimData['date']}\n\n";
        $prompt .= "Provide: Valid/Invalid, Risk Level (Low/Medium/High), Brief reason.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Generate timesheet summary
     */
    public function generateTimesheetSummary($timesheetData)
    {
        $prompt = "Create a brief professional summary of this timesheet:\n\n";
        
        foreach ($timesheetData as $entry) {
            $prompt .= "Date: {$entry['date']}, Hours: {$entry['hours']}, Task: {$entry['task']}\n";
        }
        
        $prompt .= "\nProvide a concise summary highlighting key accomplishments and total hours.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Suggest optimal work schedules
     */
    public function suggestOptimalSchedule($productivityData)
    {
        $prompt = "Based on this productivity data, suggest an optimal work schedule:\n\n";
        $prompt .= json_encode($productivityData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide brief schedule recommendations for peak productivity.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Generate project time estimates
     */
    public function estimateProjectTime($projectDescription, $requirements = [])
    {
        $prompt = "Estimate time needed for this project:\n\n";
        $prompt .= "Project: {$projectDescription}\n";
        
        if (!empty($requirements)) {
            $prompt .= "Requirements:\n";
            foreach ($requirements as $req) {
                $prompt .= "- {$req}\n";
            }
        }
        
        $prompt .= "\nProvide time estimate with breakdown by phases.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Analyze team productivity
     */
    public function analyzeTeamProductivity($teamData)
    {
        $prompt = "Analyze this team productivity data:\n\n";
        $prompt .= json_encode($teamData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide insights on team performance and improvement suggestions.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Generate smart reminders
     */
    public function generateSmartReminder($context, $type = 'timesheet')
    {
        $prompts = [
            'timesheet' => "Generate a friendly timesheet reminder: {$context}",
            'break' => "Generate a wellness break reminder: {$context}",
            'deadline' => "Generate a project deadline reminder: {$context}",
            'meeting' => "Generate a meeting preparation reminder: {$context}"
        ];

        $prompt = $prompts[$type] ?? $prompts['timesheet'];

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Extract insights from text
     */
    public function extractInsights($text, $context = 'general')
    {
        $prompt = "Extract key insights from this {$context} text:\n\n{$text}\n\nProvide 3 main insights as bullet points.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Generate reports
     */
    public function generateReport($data, $reportType = 'productivity')
    {
        $prompts = [
            'productivity' => "Generate a productivity report from this data:",
            'timesheet' => "Generate a timesheet analysis from this data:",
            'claims' => "Generate an expense claims report from this data:",
            'attendance' => "Generate an attendance report from this data:"
        ];

        $prompt = $prompts[$reportType] . "\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide executive summary with key metrics and recommendations.";

        return $this->generateCompletion($prompt, 'llama2');
    }

    /**
     * Get available models
     */
    public function getAvailableModels()
    {
        try {
            $response = $this->client->get('/api/tags');
            $data = json_decode($response->getBody(), true);
            return $data['models'] ?? [];
        } catch (RequestException $e) {
            Log::error('Failed to get Ollama models: ' . $e->getMessage());
            return [];
        }
    }
}
