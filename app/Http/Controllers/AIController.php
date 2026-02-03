<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;
use App\Services\AISystemContext;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        $this->middleware('auth');
    }

    /**
     * Show the AI Chat interface
     */
    public function chat()
    {
        return view('ai.chat');
    }

    /**
     * Process AI request via API
     */
    public function processRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'prompt' => 'required|string|max:5000',
            ]);

            // Get system context
            $systemContext = AISystemContext::getContext();

            // Call Gemini API
            $response = $this->geminiService->generateContent(
                $validated['prompt'],
                $systemContext
            );

            if (!$response['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $response['error'] ?? 'Unknown error'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'response' => $response['response'],
                'model' => $response['model'] ?? 'gemini-2.5-flash'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation error: ' . implode(', ', array_merge(...array_values($e->errors())))
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI Controller Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI suggestions
     */
    public function getSuggestions()
    {
        $suggestions = [
            'budget' => [
                'icon' => 'fa-file-invoice-dollar',
                'title' => 'Budget Management',
                'prompts' => [
                    'What is the current status of all budget allocations?',
                    'Analyze budget utilization by department',
                    'Which departments have the most remaining budget?',
                    'What is the total spending versus allocated budget?'
                ]
            ],
            'collections' => [
                'icon' => 'fa-hand-holding-dollar',
                'title' => 'Collections & Revenue',
                'prompts' => [
                    'What is the total revenue collected this month?',
                    'Analyze collection trends over the last 3 months',
                    'Which collection categories are performing best?',
                    'What is the average collection amount?'
                ]
            ],
            'payables' => [
                'icon' => 'fa-credit-card',
                'title' => 'Payables & Expenses',
                'prompts' => [
                    'What is the total amount of unpaid payables?',
                    'Identify overdue payables that need attention',
                    'Analyze payable trends by vendor or category',
                    'What are the top pending payments?'
                ]
            ],
            'disbursement' => [
                'icon' => 'fa-money-bill-wave',
                'title' => 'Disbursements',
                'prompts' => [
                    'What is the total disbursed amount this period?',
                    'Analyze disbursement patterns and trends',
                    'Which departments have the highest disbursements?',
                    'Forecast future disbursement needs'
                ]
            ],
            'general' => [
                'icon' => 'fa-comments',
                'title' => 'Financial Insights',
                'prompts' => [
                    'Generate a comprehensive financial summary',
                    'What are the top 3 financial priorities this month?',
                    'Recommend actions to improve financial health',
                    'What KPIs should we focus on?'
                ]
            ]
        ];

        return response()->json($suggestions);
    }

    /**
     * Test API connection
     */
    public function test()
    {
        $isConnected = $this->geminiService->testConnection();

        return response()->json([
            'connected' => $isConnected,
            'message' => $isConnected ? 'Gemini API is working!' : 'Connection failed'
        ]);
    }
}
