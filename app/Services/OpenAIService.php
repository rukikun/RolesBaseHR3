<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    private $client;
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
        $this->baseUrl = env('OPENAI_BASE_URL', 'https://api.openai.com/v1');
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Test OpenAI API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->client->get('/models');
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'message' => 'OpenAI API connection successful'
            ];
        } catch (RequestException $e) {
            Log::error('OpenAI API connection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => $e->getCode(),
                'message' => 'OpenAI API connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analyze time entry description and categorize tasks
     */
    public function categorizeTimeEntry($description, $project = null)
    {
        $prompt = "Analyze this work task description and categorize it. Provide a structured response with category, subcategory, productivity score (1-10), and suggested improvements.\n\nTask: {$description}";
        
        if ($project) {
            $prompt .= "\nProject: {$project}";
        }

        return $this->generateCompletion($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.3
        ]);
    }

    /**
     * Generate time tracking insights and recommendations
     */
    public function generateTimeInsights($timeEntries)
    {
        $entriesText = '';
        foreach ($timeEntries as $entry) {
            $entriesText .= "Date: {$entry['date']}, Duration: {$entry['duration']}, Task: {$entry['description']}\n";
        }

        $prompt = "Analyze these time tracking entries and provide insights about productivity patterns, time allocation, and recommendations for improvement:\n\n{$entriesText}\n\nProvide a structured analysis with key insights, patterns, and actionable recommendations.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 400,
            'temperature' => 0.4
        ]);
    }

    /**
     * Validate and analyze expense claims
     */
    public function validateClaim($claimData)
    {
        $prompt = "Analyze this expense claim for validity, compliance, and potential issues:\n\n";
        $prompt .= "Type: {$claimData['type']}\n";
        $prompt .= "Amount: {$claimData['amount']}\n";
        $prompt .= "Description: {$claimData['description']}\n";
        $prompt .= "Date: {$claimData['date']}\n\n";
        $prompt .= "Provide analysis on: validity, compliance with typical business policies, red flags, and approval recommendation.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 300,
            'temperature' => 0.2
        ]);
    }

    /**
     * Generate automated timesheet summaries
     */
    public function generateTimesheetSummary($timesheetData)
    {
        $prompt = "Create a professional timesheet summary for the following entries:\n\n";
        
        foreach ($timesheetData as $entry) {
            $prompt .= "Date: {$entry['date']}, Hours: {$entry['hours']}, Task: {$entry['task']}\n";
        }
        
        $prompt .= "\nGenerate a concise professional summary highlighting key accomplishments, total hours, and main focus areas.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 250,
            'temperature' => 0.3
        ]);
    }

    /**
     * Suggest optimal work schedules based on productivity patterns
     */
    public function suggestOptimalSchedule($productivityData)
    {
        $prompt = "Based on this productivity data, suggest an optimal work schedule:\n\n";
        $prompt .= json_encode($productivityData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide schedule recommendations considering peak productivity hours, break times, and work-life balance.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 300,
            'temperature' => 0.4
        ]);
    }

    /**
     * Generate project time estimates using AI
     */
    public function estimateProjectTime($projectDescription, $requirements = [])
    {
        $prompt = "Estimate time requirements for this project:\n\n";
        $prompt .= "Project: {$projectDescription}\n";
        
        if (!empty($requirements)) {
            $prompt .= "Requirements:\n";
            foreach ($requirements as $req) {
                $prompt .= "- {$req}\n";
            }
        }
        
        $prompt .= "\nProvide detailed time estimates broken down by phases, with total hours and timeline recommendations.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 350,
            'temperature' => 0.3
        ]);
    }

    /**
     * Analyze team productivity and generate reports
     */
    public function analyzeTeamProductivity($teamData)
    {
        $prompt = "Analyze team productivity data and generate insights:\n\n";
        $prompt .= json_encode($teamData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide analysis on team performance, bottlenecks, strengths, and improvement recommendations.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 400,
            'temperature' => 0.3
        ]);
    }

    /**
     * Generate smart notifications and reminders
     */
    public function generateSmartReminder($context, $type = 'timesheet')
    {
        $prompts = [
            'timesheet' => "Generate a friendly reminder for timesheet submission based on: {$context}",
            'break' => "Generate a wellness reminder for taking breaks based on: {$context}",
            'deadline' => "Generate a project deadline reminder based on: {$context}",
            'meeting' => "Generate a meeting preparation reminder based on: {$context}"
        ];

        $prompt = $prompts[$type] ?? $prompts['timesheet'];

        return $this->generateCompletion($prompt, [
            'max_tokens' => 150,
            'temperature' => 0.5
        ]);
    }

    /**
     * Core method to generate AI completions
     */
    private function generateCompletion($prompt, $options = [])
    {
        try {
            $defaultOptions = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an AI assistant specialized in HR, time tracking, and productivity analysis. Provide structured, actionable insights.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 300,
                'temperature' => 0.3
            ];

            $requestData = array_merge($defaultOptions, $options);

            $response = $this->client->post('/chat/completions', [
                'json' => $requestData
            ]);

            $data = json_decode($response->getBody(), true);

            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'usage' => $data['usage'] ?? null
            ];

        } catch (RequestException $e) {
            Log::error('OpenAI API request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'AI analysis temporarily unavailable'
            ];
        }
    }

    /**
     * Extract key insights from text using AI
     */
    public function extractInsights($text, $context = 'general')
    {
        $prompt = "Extract key insights and actionable items from this {$context} text:\n\n{$text}\n\nProvide bullet points of main insights and recommendations.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.3
        ]);
    }

    /**
     * Generate automated reports with AI analysis
     */
    public function generateReport($data, $reportType = 'productivity')
    {
        $prompts = [
            'productivity' => "Generate a productivity report based on this data:",
            'timesheet' => "Generate a timesheet analysis report based on this data:",
            'claims' => "Generate an expense claims report based on this data:",
            'attendance' => "Generate an attendance report based on this data:"
        ];

        $prompt = $prompts[$reportType] . "\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide executive summary, key metrics, trends, and recommendations.";

        return $this->generateCompletion($prompt, [
            'max_tokens' => 500,
            'temperature' => 0.3
        ]);
    }
}
