<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Collection;
use App\Models\Disbursement;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiFinancialController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->middleware('auth');
        $this->gemini = $gemini;
    }

    /**
     * Show the AI Financial Intelligence dashboard (admin only).
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Only admins can access AI Financial Intelligence.');
        }

        // Simple defaults for the filter controls
        $defaultRange = 'last_30_days';

        return view('ai.financial_intelligence', [
            'defaultRange' => $defaultRange,
        ]);
    }

    /**
     * Run AI financial analysis using Gemini.
     *
     * Parameter controls:
     * - date_range: last_30_days | last_90_days | year_to_date | custom
     * - from, to: optional custom dates (Y-m-d)
     * - department: optional department/remarks filter
     */
    public function analyze(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'date_range' => 'required|string|in:last_30_days,last_90_days,year_to_date,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'department' => 'nullable|string|max:255',
        ]);

        // Resolve date range
        $now = Carbon::now();
        $from = null;
        $to = null;

        switch ($validated['date_range']) {
            case 'last_30_days':
                $from = $now->copy()->subDays(30)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'last_90_days':
                $from = $now->copy()->subDays(90)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'year_to_date':
                $from = $now->copy()->startOfYear();
                $to = $now->copy()->endOfDay();
                break;
            case 'custom':
                $from = $validated['from'] ? Carbon::parse($validated['from'])->startOfDay() : null;
                $to = $validated['to'] ? Carbon::parse($validated['to'])->endOfDay() : null;
                break;
        }

        // Aggregate collections and disbursements
        $collectionsQuery = Collection::query();
        $disbursementsQuery = Disbursement::query();

        if ($from) {
            $collectionsQuery->whereDate('payment_date', '>=', $from);
            $disbursementsQuery->whereDate('disbursement_date', '>=', $from);
        }
        if ($to) {
            $collectionsQuery->whereDate('payment_date', '<=', $to);
            $disbursementsQuery->whereDate('disbursement_date', '<=', $to);
        }

        if (! empty($validated['department'])) {
            // Collections department is stored in remarks
            $collectionsQuery->where('remarks', $validated['department']);
            // Disbursements department may also be stored in remarks for now
            $disbursementsQuery->where('remarks', $validated['department']);
        }

        // Group by week for the last 4 weeks worth of buckets
        $collections = $collectionsQuery
            ->selectRaw('YEARWEEK(payment_date, 1) as yearweek, SUM(amount_paid) as total')
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get();

        $disbursements = $disbursementsQuery
            ->selectRaw('YEARWEEK(disbursement_date, 1) as yearweek, SUM(amount) as total')
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get();

        // Prepare aligned series for up to 4 recent weeks
        $weeks = [];
        $collectionsByWeek = [];
        $disbursementsByWeek = [];

        foreach ($collections as $row) {
            $weeks[$row->yearweek] = true;
            $collectionsByWeek[$row->yearweek] = (float) $row->total;
        }
        foreach ($disbursements as $row) {
            $weeks[$row->yearweek] = true;
            $disbursementsByWeek[$row->yearweek] = (float) $row->total;
        }

        ksort($weeks);
        $weeks = array_slice(array_keys($weeks), -4); // last 4 weeks max

        $labels = [];
        $historicalCollections = [];
        $historicalDisbursements = [];

        foreach ($weeks as $yearweek) {
            // Convert YEARWEEK to a simple label like "Week N"
            $labels[] = 'Week ' . $yearweek;
            $historicalCollections[] = $collectionsByWeek[$yearweek] ?? 0.0;
            $historicalDisbursements[] = $disbursementsByWeek[$yearweek] ?? 0.0;
        }

        // Build prompt with explainability + strict JSON schema
        $rangeLabel = $validated['date_range'];
        $departmentLabel = $validated['department'] ?: 'All Departments';

        $prompt = "You are an AI financial analyst for a corporate finance team.\n"
            . "You receive recent weekly totals for collections (cash in) and disbursements (cash out).\n\n"
            . "Context:\n"
            . "- Date range: {$rangeLabel}\n"
            . "- Department filter: {$departmentLabel}\n\n"
            . "Historical data (up to 4 weeks):\n"
            . "Labels (weeks): " . json_encode($labels) . "\n"
            . "Collections: " . json_encode($historicalCollections) . "\n"
            . "Disbursements: " . json_encode($historicalDisbursements) . "\n\n"
            . "TASKS:\n"
            . "1. Compute a financial stress index from 0 to 100 (higher = more stress on cash flow).\n"
            . "2. Compute a financial health score from 0 to 100 (higher = healthier).\n"
            . "3. Provide a confidence score (0-100) representing how reliable you think this analysis is based on the data.\n"
            . "4. Forecast the next 4 weeks of collections, disbursements, and net cash flow.\n"
            . "5. Provide 3–5 short executive summary bullet points (plain text).\n"
            . "6. Provide a short explainability section that clearly states WHY the stress index and health score look the way they do, in 2–3 sentences.\n"
            . "7. Provide 2–3 concrete recommended actions that finance leaders can take.\n\n"
            . "Return ONLY valid JSON (no markdown, no backticks) with the following exact structure and key names:\n"
            . "{\n"
            . "  \"stressIndex\": number,\n"
            . "  \"healthScore\": number,\n"
            . "  \"confidence\": number,\n"
            . "  \"summaryBullets\": [\"string\", \"string\"],\n"
            . "  \"explanation\": \"short paragraph explaining the scores\",\n"
            . "  \"recommendedActions\": [\"string\", \"string\"],\n"
            . "  \"forecast\": {\n"
            . "    \"labels\": [\"string\", \"string\", \"string\", \"string\"],\n"
            . "    \"collections\": [number, number, number, number],\n"
            . "    \"disbursements\": [number, number, number, number],\n"
            . "    \"netCashFlow\": [number, number, number, number]\n"
            . "  }\n"
            . "}\n";

        try {
            // Ask Gemini for a response and parse JSON ourselves
            $result = $this->gemini->generateJson($prompt);

            if (! ($result['success'] ?? false)) {
                return response()->json([
                    'error' => $result['error'] ?? 'AI service error',
                ], 500);
            }

            $raw = $result['response'] ?? '';
            Log::error('AI financial analysis raw response', ['raw' => $raw]);
            $payload = $this->decodeGeminiJson($raw);

            if (! is_array($payload)) {
                Log::error('AI financial analysis JSON decode failed', [
                    'raw' => $raw,
                    'error' => json_last_error_msg(),
                ]);

                return response()->json([
                    'error' => 'AI response was malformed. Please try again.',
                ], 500);
            }

            // Audit trail: log that admin ran AI analysis
            AuditTrail::create([
                'actor_type' => 'admin',
                'actor_id' => $user->id,
                'actor_name' => $user->name,
                'actor_email' => $user->email,
                'action' => 'ai_financial_analysis_run',
                'description' => json_encode([
                    'date_range' => $validated['date_range'],
                    'from' => $from?->toDateString(),
                    'to' => $to?->toDateString(),
                    'department' => $validated['department'] ?? null,
                    'summary' => $payload['summaryBullets'] ?? [],
                    'stressIndex' => $payload['stressIndex'] ?? null,
                    'healthScore' => $payload['healthScore'] ?? null,
                ]),
                'target_type' => 'ai_financial_intelligence',
                'target_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json($payload);
        } catch (\Throwable $e) {
            Log::error('AI financial analysis exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Unexpected error while running AI analysis.',
            ], 500);
        }
    }

    /**
     * Best-effort JSON extraction from Gemini text responses.
     */
    private function decodeGeminiJson(string $raw): ?array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return null;
        }

        // Strip code fences if present
        if (str_starts_with($raw, '```')) {
            $raw = preg_replace('/^```[a-zA-Z]*\s*/', '', $raw);
            $raw = preg_replace('/```$/', '', $raw);
            $raw = trim($raw);
        }

        // Normalise common smart quotes and line endings that often break JSON
        $raw = str_replace(
            ['“', '”', '’', '‘', "\r\n", "\r"],
            ['"', '"', "'", "'", "\n", "\n"],
            $raw
        );

        // First attempt: decode whole string
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Second attempt: extract first JSON object between { and }
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');

        if ($start !== false && $end !== false && $end > $start) {
            $json = substr($raw, $start, $end - $start + 1);

            // Try raw slice first
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Last‑chance repair: remove trailing commas before ] or }
            $repaired = preg_replace('/,\s*([}\]])/', '$1', $json);
            $decoded = json_decode($repaired, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}


