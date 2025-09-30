<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HuggingFaceService
{
    private $client;
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('HUGGINGFACE_API_KEY');
        $this->baseUrl = 'https://api-inference.huggingface.co/models';
        
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Test Hugging Face connection
     */
    public function testConnection()
    {
        try {
            $response = $this->client->post($this->baseUrl . '/microsoft/DialoGPT-medium', [
                'json' => ['inputs' => 'Hello']
            ]);
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'message' => 'Hugging Face API connection successful'
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'status' => $e->getCode(),
                'message' => 'Hugging Face API connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate text using Hugging Face models
     */
    public function generateText($prompt, $model = 'microsoft/DialoGPT-medium')
    {
        try {
            $response = $this->client->post($this->baseUrl . '/' . $model, [
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_length' => 200,
                        'temperature' => 0.7,
                        'do_sample' => true
                    ]
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            return [
                'success' => true,
                'content' => $data[0]['generated_text'] ?? $prompt,
                'model' => $model
            ];

        } catch (RequestException $e) {
            Log::error('Hugging Face API request failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'AI analysis temporarily unavailable'
            ];
        }
    }

    // Implement the same methods as OpenAI service but using Hugging Face
    public function categorizeTimeEntry($description, $project = null)
    {
        $prompt = "Analyze work task: {$description}. Provide category and productivity score 1-10.";
        return $this->generateText($prompt, 'facebook/bart-large-cnn');
    }

    public function generateTimeInsights($timeEntries)
    {
        $entriesText = '';
        foreach ($timeEntries as $entry) {
            $entriesText .= "{$entry['date']}: {$entry['duration']} - {$entry['description']}. ";
        }
        
        $prompt = "Analyze productivity patterns: {$entriesText}";
        return $this->generateText($prompt, 'facebook/bart-large-cnn');
    }

    public function validateClaim($claimData)
    {
        $prompt = "Validate expense: {$claimData['type']} {$claimData['amount']} - {$claimData['description']}";
        return $this->generateText($prompt, 'facebook/bart-large-cnn');
    }
}
