@extends('layouts.app')

@section('title', 'AI Financial Intelligence')

@section('styles')
<style>
    .ai-summary-card {
        min-height: 140px;
    }
    .ai-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
    }
    .ai-metric-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .ai-explanation {
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">AI Financial Intelligence</h4>
            <p class="text-muted mb-0">Real-time diagnostic analysis and cash flow forecasting for admins.</p>
        </div>
        <button id="runAnalysisBtn" class="btn btn-primary">
            <span class="fe fe-zap me-1"></span> Run Analysis
        </button>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <div class="card shadow-sm ai-summary-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="ai-chip">
                        <i class="fe fe-cpu me-2"></i> Executive Intelligence Summary
                    </span>
                    <small class="text-muted" id="lastRunLabel">No analysis run yet</small>
                </div>
                <ul id="aiSummary" class="mb-0 text-muted">
                    <li>Use the controls on the right and click <strong>Run Analysis</strong> to generate AI insights.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Analysis Parameters</h6>
                <form id="aiParametersForm">
                    <div class="mb-2">
                        <label class="form-label mb-1 ai-metric-label">Date Range</label>
                        <select name="date_range" id="date_range" class="form-select form-select-sm">
                            <option value="last_30_days" {{ $defaultRange === 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="last_90_days">Last 90 days</option>
                            <option value="year_to_date">Year to date</option>
                            <option value="custom">Custom range</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-2" id="customRangeGroup" style="display:none;">
                        <div class="col-6">
                            <label class="form-label mb-1">From</label>
                            <input type="date" name="from" id="from" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1">To</label>
                            <input type="date" name="to" id="to" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label mb-1">Department</label>
                        <select name="department" id="department" class="form-select form-select-sm">
                            <option value="">All Departments</option>
                            <option value="IT / Technical Department">IT / Technical Department</option>
                            <option value="Marketing Department">Marketing Department</option>
                            <option value="Logistics / Operations Department">Logistics / Operations Department</option>
                            <option value="Sales Department">Sales Department</option>
                        </select>
                    </div>
                    <div class="small text-muted">
                        Every analysis run is logged in the audit trail with parameters and key outputs.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="ai-metric-label mb-1 text-muted">Financial Stress Index</p>
                <div class="d-flex align-items-center mb-1">
                    <div class="flex-grow-1 me-2">
                        <div class="progress" style="height: 6px;">
                            <div id="stressBar" class="progress-bar bg-danger" style="width: 0%;"></div>
                        </div>
                    </div>
                    <span id="stressValue" class="fw-semibold">0%</span>
                </div>
                <small class="text-muted">Higher values indicate more pressure on cash flow.</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="ai-metric-label mb-1 text-muted">Health Score</p>
                <h3 id="healthScore" class="mb-1">–</h3>
                <small class="text-muted">Overall financial health based on recent trends.</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="ai-metric-label mb-1 text-muted">AI Confidence</p>
                <h3 id="confidence" class="mb-1">–</h3>
                <small class="text-muted">Model confidence in these insights and forecasts.</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold mb-0">AI Trend Forecasting</h6>
                    <small class="text-muted" id="forecastMeta">Forecast will appear after analysis.</small>
                </div>
                <canvas id="forecastChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Why these scores?</h6>
                <p id="aiExplanation" class="ai-explanation text-muted mb-0">
                    Once you run an analysis, the AI will explain why the stress index and health score look the way they do.
                </p>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Recommended Actions</h6>
                <ul id="aiActions" class="mb-0 text-muted">
                    <li>AI-driven recommendations will appear here after analysis.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- AI Thinking Modal -->
<div class="modal fade" id="aiThinkingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <h5 class="fw-semibold mb-1">AI is thinking…</h5>
            <p class="text-muted mb-0">Analyzing collections and disbursements to forecast your cash flow.</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let forecastChart = null;
    let thinkingModalInstance = null;
    let thinkingModalShownAt = null;
    const THINKING_MIN_DURATION_MS = 10000; // keep the modal for ~10 seconds

    function showThinkingModal() {
        const modalElement = document.getElementById('aiThinkingModal');
        thinkingModalInstance = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        thinkingModalShownAt = Date.now();
        thinkingModalInstance.show();
    }

    function hideThinkingModal() {
        if (!thinkingModalInstance) {
            return;
        }

        const now = Date.now();
        const elapsed = thinkingModalShownAt ? now - thinkingModalShownAt : THINKING_MIN_DURATION_MS;
        const remaining = THINKING_MIN_DURATION_MS - elapsed;

        if (remaining > 0) {
            setTimeout(() => {
                if (thinkingModalInstance) {
                    thinkingModalInstance.hide();
                }
            }, remaining);
        } else {
            thinkingModalInstance.hide();
        }
    }

    function updateSummary(bullets) {
        const summaryEl = document.getElementById('aiSummary');
        summaryEl.innerHTML = '';
        if (!bullets || !bullets.length) {
            const li = document.createElement('li');
            li.textContent = 'AI did not return any summary bullets.';
            summaryEl.appendChild(li);
            return;
        }
        bullets.forEach(text => {
            const li = document.createElement('li');
            li.textContent = text;
            summaryEl.appendChild(li);
        });
    }

    function updateMetrics(data) {
        const stressIndex = data.stressIndex ?? 0;
        const healthScore = data.healthScore ?? '–';
        const confidence = data.confidence ?? 0;

        document.getElementById('stressValue').textContent = stressIndex + '%';
        document.getElementById('stressBar').style.width = stressIndex + '%';

        document.getElementById('healthScore').textContent = healthScore;
        document.getElementById('confidence').textContent = confidence + '%';
    }

    function updateExplanation(explanation) {
        document.getElementById('aiExplanation').textContent = explanation || 'AI did not provide an explanation.';
    }

    function updateActions(actions) {
        const list = document.getElementById('aiActions');
        list.innerHTML = '';
        if (!actions || !actions.length) {
            const li = document.createElement('li');
            li.textContent = 'No specific actions were suggested.';
            list.appendChild(li);
            return;
        }
        actions.forEach(text => {
            const li = document.createElement('li');
            li.textContent = text;
            list.appendChild(li);
        });
    }

    function renderForecastChart(forecast) {
        const labels = forecast.labels || [];
        const collections = forecast.collections || [];
        const disbursements = forecast.disbursements || [];
        const netCashFlow = forecast.netCashFlow || [];

        const ctx = document.getElementById('forecastChart').getContext('2d');

        if (forecastChart) {
            forecastChart.destroy();
        }

        forecastChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Collections',
                        data: collections,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Disbursements',
                        data: disbursements,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.1)',
                        tension: 0.3
                    },
                    {
                        label: 'Net Cash Flow',
                        data: netCashFlow,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,0.1)',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const meta = document.getElementById('forecastMeta');
        meta.textContent = 'Forecast for the next ' + labels.length + ' periods based on recent collections and disbursements.';
    }

    function updateLastRunLabel() {
        const label = document.getElementById('lastRunLabel');
        const now = new Date();
        label.textContent = 'Last run: ' + now.toLocaleString();
    }

    document.getElementById('date_range').addEventListener('change', function () {
        const isCustom = this.value === 'custom';
        document.getElementById('customRangeGroup').style.display = isCustom ? 'flex' : 'none';
    });

    document.getElementById('runAnalysisBtn').addEventListener('click', function () {
        const form = document.getElementById('aiParametersForm');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        if (payload.date_range === 'custom' && (!payload.from || !payload.to)) {
            alert('Please select both From and To dates for the custom range.');
            return;
        }

        showThinkingModal();

        fetch("{{ route('ai.financial_intelligence.analyze') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            hideThinkingModal();

            if (data.error) {
                alert(data.error);
                return;
            }

            updateSummary(data.summaryBullets || []);
            updateMetrics(data);
            updateExplanation(data.explanation || '');
            updateActions(data.recommendedActions || []);
            if (data.forecast) {
                renderForecastChart(data.forecast);
            }
            updateLastRunLabel();
        })
        .catch(() => {
            hideThinkingModal();
            alert('Something went wrong while running AI analysis.');
        });
    });
</script>
@endsection

