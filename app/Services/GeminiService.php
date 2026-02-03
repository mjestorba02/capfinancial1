<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini AI Service
 * Handles all interactions with Google's Generative Language API
 */
class GeminiService
{
    private $apiKey;
    private $model;
    private $baseUrl;
    private $config;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model');
        $this->baseUrl = config('gemini.base_url');
        $this->config = config('gemini.config');

        if (!$this->apiKey) {
            throw new \Exception('Gemini API key not configured. Set GEMINI_API_KEY in .env');
        }
    }

    /**
     * Send a prompt to Gemini and get a response
     * 
     * @param string $prompt User prompt or question
     * @param array $systemContext Optional system context to prepend
     * @return array Response data
     */
    public function generateContent($prompt, $systemContext = null)
    {
        try {
            // Enhance prompt with system context if provided
            $enhancedPrompt = $prompt;
            if ($systemContext) {
                $enhancedPrompt = $systemContext . "\n### USER QUESTION\n" . $prompt;
            }

            // Use API key as query parameter per Google Generative API requirements
            $endpoint = $this->baseUrl . "/models/" . $this->model . ":generateContent?key=" . urlencode($this->apiKey);

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $enhancedPrompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => $this->config
            ];

            Log::debug('Gemini API Request', [
                'model' => $this->model,
                'prompt_length' => strlen($enhancedPrompt),
                'endpoint' => $endpoint
            ]);

            // Send JSON payload; Http::post will set the Content-Type header appropriately
            $response = Http::timeout(60)
                ->post($endpoint, $payload)
                ->json();

            if (isset($response['error'])) {
                Log::error('Gemini API Error', $response);
                return [
                    'success' => false,
                    'error' => $response['error']['message'] ?? 'Unknown error',
                ];
            }

            if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'response' => $response['candidates'][0]['content']['parts'][0]['text'],
                    'model' => $this->model
                ];
            }

            Log::error('Unexpected Gemini Response', $response);
            return [
                'success' => false,
                'error' => 'Unexpected response format',
            ];
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Service error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels()
    {
        return [
            'gemini-2.5-flash' => 'Gemini 2.5 Flash (Fastest)',
            'gemini-2.0-pro' => 'Gemini 2.0 Pro (Most Capable)',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
        ];
    }

    /**
     * Test the API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->generateContent("Say 'Connection successful!' in exactly 5 words.");
            return $response['success'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
