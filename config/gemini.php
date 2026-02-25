<?php

/**
 * Google Gemini API Configuration
 * 
 * Free API - No credit card required
 * Get free API keys at: https://aistudio.google.com/app/api-keys
 */

return [
    // If GEMINI_API_KEY is not set on the server's .env (eg. limited CyberPanel),
    // fall back to the key baked into this config so the feature still works after deploy.
    // NOTE: This will expose the key in your codebase; prefer setting GEMINI_API_KEY in .env
    // when you are able to.
    'api_key' => env('GEMINI_API_KEY', 'AIzaSyBRU8xfjwjKKalgr-OIo3pyIopWjZe9dKs'),

    // Default to a current, fast model; can still be overridden in .env
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
