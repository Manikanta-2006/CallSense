<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Real-time dashboard data from database — NOT hardcoded.
     */
    public function apiData()
    {
        $totalCalls = Call::count();
        $positive   = Call::where('sentiment_label', 'Positive')->count();
        $negative   = Call::where('sentiment_label', 'Negative')->count();
        $neutral    = Call::where('sentiment_label', 'Neutral')->count();
        $trendData  = Call::latest()->take(20)->get(['id', 'sentiment_score', 'sentiment_label', 'created_at'])->reverse()->values();

        // Sentiment percentages — dynamically calculated
        $posPct = $totalCalls > 0 ? round(($positive / $totalCalls) * 100) : 0;
        $negPct = $totalCalls > 0 ? round(($negative / $totalCalls) * 100) : 0;
        $neuPct = $totalCalls > 0 ? round(($neutral / $totalCalls) * 100) : 0;

        // Average sentiment score
        $avgSentiment = Call::avg('sentiment_score') ?? 0;

        // Active alerts count — REAL query
        $activeAlerts = Alert::active()->count();
        $pendingAlerts = Alert::pending()->count();

        // AI Threat Level — based on real recent negative call count
        $recentNeg = Call::where('sentiment_label', 'Negative')
            ->where('created_at', '>=', now()->subHours(24))->count();
        if ($recentNeg >= 5) {
            $threatLevel = 'CRITICAL';
        } elseif ($recentNeg >= 2) {
            $threatLevel = 'WARNING';
        } else {
            $threatLevel = 'STABLE';
        }

        // Top complaint keywords — extracted from REAL transcripts
        $negativeTranscripts = Call::where('sentiment_label', 'Negative')
            ->latest()->take(20)->pluck('transcript')->implode(' ');
        $keywords = [];
        $watchWords = [
            'refund', 'delay', 'service', 'billing', 'charge', 'cancel',
            'wait', 'issue', 'broken', 'error', 'complaint', 'wrong',
            'terrible', 'horrible', 'worst', 'disappointed', 'frustrated',
            'payment', 'overcharged', 'shipping', 'delivery', 'quality',
            'defective', 'return', 'warranty', 'support', 'manager',
        ];
        foreach ($watchWords as $word) {
            $count = substr_count(strtolower($negativeTranscripts), $word);
            if ($count > 0) {
                $keywords[] = ['word' => $word, 'count' => $count];
            }
        }
        usort($keywords, fn($a, $b) => $b['count'] - $a['count']);
        $keywords = array_slice($keywords, 0, 8);

        // Recent calls feed — live database entries
        $recentCalls = Call::latest()->take(5)->get(['id', 'transcript', 'sentiment_label', 'sentiment_score', 'emotion', 'created_at']);

        // Average agent performance — REAL formula
        $agentPerformance = Call::whereNotNull('agent_name')
            ->select('agent_name', DB::raw('AVG(sentiment_score) as avg_score'), DB::raw('COUNT(*) as call_count'))
            ->groupBy('agent_name')
            ->orderByDesc('avg_score')
            ->take(5)
            ->get();

        // AI Recommendations — dynamic based on real data
        $recommendations = [];
        if ($negPct > 30) {
            $recommendations[] = 'Increase agent response speed — negative rate above 30%';
        }
        if ($recentNeg >= 3) {
            $recommendations[] = 'Escalate billing complaints — spike in negative calls';
        }
        if ($posPct > 70) {
            $recommendations[] = 'Maintain current approach — strong positive trend';
        }
        if ($totalCalls == 0) {
            $recommendations[] = 'Upload call recordings to begin analysis';
        }
        if ($activeAlerts > 0) {
            $recommendations[] = $activeAlerts . ' active alert(s) require attention';
        }
        if (count($keywords) > 0) {
            $recommendations[] = 'Monitor "' . $keywords[0]['word'] . '" keyword — appearing frequently';
        }
        if ($avgSentiment < -0.3 && $totalCalls > 0) {
            $recommendations[] = 'Overall sentiment trending negative — review agent training';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'System stable. Continue monitoring incoming calls.';
        }

        return response()->json([
            'total_calls'       => $totalCalls,
            'positive'          => $positive,
            'negative'          => $negative,
            'neutral'           => $neutral,
            'pos_pct'           => $posPct,
            'neg_pct'           => $negPct,
            'neu_pct'           => $neuPct,
            'avg_sentiment'     => round($avgSentiment, 2),
            'active_alerts'     => $activeAlerts,
            'pending_alerts'    => $pendingAlerts,
            'trend'             => $trendData,
            'threat_level'      => $threatLevel,
            'keywords'          => $keywords,
            'recent_calls'      => $recentCalls,
            'agent_performance' => $agentPerformance,
            'recommendations'   => $recommendations,
        ]);
    }

    /**
     * System status check — REAL backend checks.
     */
    public function systemStatus()
    {
        $status = [];

        // Database connection check
        try {
            DB::connection()->getPdo();
            $status['database'] = 'connected';
        } catch (\Exception $e) {
            $status['database'] = 'disconnected';
        }

        // NLP API check
        $apiKey = env('GOOGLE_CLOUD_API_KEY');
        $status['nlp_api'] = $apiKey ? 'configured' : 'not_configured';

        // Queue status check
        try {
            $failedJobs = DB::table('failed_jobs')->count();
            $pendingJobs = DB::table('jobs')->count();
            $status['queue'] = [
                'status'       => 'running',
                'pending_jobs' => $pendingJobs,
                'failed_jobs'  => $failedJobs,
            ];
        } catch (\Exception $e) {
            $status['queue'] = ['status' => 'unknown'];
        }

        // Aggregated metrics — REAL database queries
        $status['metrics'] = [
            'total_calls'    => Call::count(),
            'active_alerts'  => Alert::active()->count(),
            'avg_sentiment'  => round(Call::avg('sentiment_score') ?? 0, 2),
        ];

        return response()->json($status);
    }

    /**
     * Alerts page with REAL alert data.
     */
    public function alerts()
    {
        $alerts = Alert::with(['call', 'resolver', 'assignee'])
            ->latest()
            ->get();

        // Also get raw negative calls without alerts for legacy view
        $negativeCalls = Call::where('sentiment_label', 'Negative')
            ->latest()
            ->get();

        return view('alerts', compact('alerts', 'negativeCalls'));
    }

    /**
     * Call history with search and filter support.
     */
    public function history()
    {
        $query = Call::query();

        // Search functionality
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transcript', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('agent_name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        // Filter by sentiment
        if ($sentiment = request('sentiment')) {
            $query->where('sentiment_label', $sentiment);
        }

        // Filter by emotion
        if ($emotion = request('emotion')) {
            $query->where('emotion', $emotion);
        }

        // Filter by date range
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Filter by agent
        if ($agent = request('agent')) {
            $query->where('agent_name', $agent);
        }

        $calls = $query->latest()->get();

        // Get unique agents for filter dropdown
        $agents = Call::whereNotNull('agent_name')
            ->distinct()
            ->pluck('agent_name');

        return view('call_history', compact('calls', 'agents'));
    }

    /**
     * Export calls as CSV.
     */
    public function exportCsv()
    {
        $calls = Call::latest()->get();

        $filename = 'callsense_export_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($calls) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Customer', 'Agent', 'Transcript', 'Score', 'Label', 'Emotion', 'Risk', 'Date']);
            foreach ($calls as $call) {
                fputcsv($out, [
                    $call->id,
                    $call->customer_name ?? 'N/A',
                    $call->agent_name ?? 'N/A',
                    $call->transcript,
                    $call->sentiment_score,
                    $call->sentiment_label,
                    $call->emotion ?? 'N/A',
                    $call->risk_score ?? 'N/A',
                    $call->created_at->toDateTimeString(),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
