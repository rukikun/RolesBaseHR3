<?php

namespace App\Services;

class AIServiceFactory
{
    public static function create()
    {
        $service = env('AI_SERVICE', 'demo');
        
        switch ($service) {
            case 'huggingface':
                return new HuggingFaceService();
            case 'openai':
                return new OpenAIService();
            case 'demo':
                return new DemoAIService();
            case 'ollama':
            default:
                return new OllamaService();
        }
    }
}
