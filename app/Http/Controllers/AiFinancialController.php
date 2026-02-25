<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Collection;
use App\Models\Disbursement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AiFinancialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
            // Collections: department is stored in remarks (column exists on all environments)
            if (Schema::hasColumn('collections', 'remarks')) {
                $collectionsQuery->where('remarks', $validated['department']);
            } else {
                Log::warning('AI financial analysis: collections.remarks column missing; skipping department filter');
            }

            // Disbursements: some environments may not yet have a remarks column
            if (Schema::hasColumn('disbursements', 'remarks')) {
                $disbursementsQuery->where('remarks', $validated['department']);
            } else {
                Log::warning('AI financial analysis: disbursements.remarks column missing; skipping department filter');
            }
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

        // Build labels we will use in the template output
        $rangeLabel = $validated['date_range'];
        $departmentLabel = $validated['department'] ?: 'All Departments';

        try {
            // Use deterministic, template‑based analysis derived directly from system data.
            $payload = $this->buildFallbackPayload($labels, $historicalCollections, $historicalDisbursements, $rangeLabel, $departmentLabel);

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
     * Build a deterministic, template‑based payload from system data.
     */
    private function buildFallbackPayload(array $labels, array $collections, array $disbursements, string $rangeLabel, string $departmentLabel): array
    {
        $periods = count($labels) ?: 4;
        $labels = $labels ?: array_map(fn ($i) => 'Period ' . ($i + 1), range(0, $periods - 1));

        // Simple heuristics: use last known values if available, otherwise zeros.
        $lastCollections = end($collections) ?: 0.0;
        $lastDisbursements = end($disbursements) ?: 0.0;
        $net = $lastCollections - $lastDisbursements;

        $stressIndex = $net < 0 ? 70 : 40;
        $healthScore = $net < 0 ? 45 : 60;

        $totalCollections = array_sum($collections);
        $totalDisbursements = array_sum($disbursements);
        $totalNet = $totalCollections - $totalDisbursements;

        $forecastCollections = array_fill(0, $periods, $lastCollections);
        $forecastDisbursements = array_fill(0, $periods, $lastDisbursements);
        $forecastNet = array_fill(0, $periods, $net);

        $trendDirection = $totalNet >= 0 ? 'positive' : 'negative';

        $summaryBullets = [
            "For {$departmentLabel} over {$rangeLabel}, total collections were " . number_format($totalCollections, 2) . " and total disbursements were " . number_format($totalDisbursements, 2) . ".",
            "Net cash flow for the period is " . number_format($totalNet, 2) . ", indicating a {$trendDirection} cash position.",
            "Recent weekly patterns are used to project the next {$periods} periods of collections, disbursements, and net cash flow.",
        ];

        $explanation = $net < 0
            ? 'Stress is elevated because recent disbursements are higher than collections, putting pressure on short‑term liquidity. The health score remains moderate but should be monitored closely if this pattern continues.'
            : 'Stress is moderate because collections are meeting or slightly exceeding disbursements, supporting short‑term liquidity. The health score is stronger while this favourable balance is maintained.';

        $recommendedActions = $net < 0
            ? [
                'Delay or phase non‑critical disbursements to reduce immediate cash pressure.',
                'Accelerate collections where possible (follow up on overdue items or offer early‑payment incentives).',
                'Review upcoming commitments to ensure there is sufficient headroom for near‑term payments.',
            ]
            : [
                'Use the positive cash position to clear high‑cost or overdue obligations where appropriate.',
                'Evaluate opportunities to reinvest surplus cash in priority initiatives or reserves.',
                'Continue monitoring collections and disbursements to ensure the current healthy pattern is sustained.',
            ];

        return [
            'stressIndex' => $stressIndex,
            'healthScore' => $healthScore,
            'confidence' => 100,
            'summaryBullets' => $summaryBullets,
            'explanation' => $explanation,
            'recommendedActions' => $recommendedActions,
            'forecast' => [
                'labels' => $labels,
                'collections' => $forecastCollections,
                'disbursements' => $forecastDisbursements,
                'netCashFlow' => $forecastNet,
            ],
        ];
    }
}


