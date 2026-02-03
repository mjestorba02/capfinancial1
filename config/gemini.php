<?php

/**
 * Google Gemini API Configuration
 * 
 * Free API - No credit card required
 * Get free API keys at: https://aistudio.google.com/app/api-keys
 */

return [
    'api_key' => env('GEMINI_API_KEY', ''),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    'base_url' => 'https://generativelanguage.googleapis.com/v1',
    'config' => [
        'maxOutputTokens' => 2048,
        'temperature' => 0.7,
        'topP' => 1.0,
        'topK' => 40,
    ],
];
