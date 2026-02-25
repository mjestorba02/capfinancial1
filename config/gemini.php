<?php

/**
 * Google Gemini API Configuration
 * 
 * Free API - No credit card required
 * Get free API keys at: https://aistudio.google.com/app/api-keys
 */

return [
    'api_key' => env('GEMINI_API_KEY', ''),
    // Default to a current, fast model; can be overridden in .env
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    // Use v1beta for best compatibility with latest Gemini models
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    'config' => [
        'maxOutputTokens' => 2048,
        'temperature' => 0.4,
        'topP' => 1.0,
        'topK' => 40,
    ],
];
