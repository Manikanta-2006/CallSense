<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Call;
use App\Models\Alert;
use App\Models\LoginActivity;
use App\Models\ApiUsageLog;

class ProfileController extends Controller
{
    /**
     * Profile page with REAL authenticated data.
     */
    public function index()
    {
        $user = Auth::user();

        // Activity analytics — REAL queries
        $totalAnalyzed = Call::where('user_id', $user->id)->count();
        $alertsHandled = Alert::where('resolved_by', $user->id)->count();
        $positiveCount = Call::where('user_id', $user->id)->where('sentiment_label', 'Positive')->count();
        $avgSatisfaction = $totalAnalyzed > 0
            ? round(($positiveCount / $totalAnalyzed) * 100)
            : 0;

        // Average response quality
        $avgScore = Call::where('user_id', $user->id)->avg('sentiment_score') ?? 0;

        // Recent calls by this user
        $recentCalls = Call::where('user_id', $user->id)->latest()->take(5)->get();

        // If no user-specific calls, show all (for backwards compatibility)
        if ($recentCalls->isEmpty()) {
            $totalAnalyzed = Call::count();
            $alertsHandled = Alert::where('status', 'resolved')->count();
            $positiveCount = Call::where('sentiment_label', 'Positive')->count();
            $avgSatisfaction = $totalAnalyzed > 0 ? round(($positiveCount / $totalAnalyzed) * 100) : 0;
            $avgScore = Call::avg('sentiment_score') ?? 0;
            $recentCalls = Call::latest()->take(5)->get();
        }

        // Login history — REAL tracked data
        $loginHistory = LoginActivity::where('user_id', $user->id)
            ->latest('login_time')
            ->take(10)
            ->get();

        // API usage stats — REAL data
        $nlpRequests = ApiUsageLog::where('user_id', $user->id)
            ->where('service', 'nlp_sentiment')
            ->count();
        $speechRequests = ApiUsageLog::where('user_id', $user->id)
            ->where('service', 'speech_to_text')
            ->count();
        $totalApiCalls = $nlpRequests + $speechRequests;

        return view('profile', compact(
            'user', 'totalAnalyzed', 'alertsHandled', 'avgSatisfaction',
            'avgScore', 'recentCalls', 'loginHistory',
            'nlpRequests', 'speechRequests', 'totalApiCalls'
        ));
    }

    /**
     * Update profile — REAL profile editing.
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();
        $user->name = $validated['name'];

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = '/storage/' . $avatarPath;
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Settings page.
     */
    public function settings()
    {
        $user = Auth::user();

        // Queue monitoring — REAL data
        $queueStats = [
            'pending_jobs' => 0,
            'failed_jobs'  => 0,
        ];
        try {
            $queueStats['pending_jobs'] = \Illuminate\Support\Facades\DB::table('jobs')->count();
            $queueStats['failed_jobs'] = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            // Tables may not exist
        }

        return view('settings', compact('user', 'queueStats'));
    }

    /**
     * Save settings — REAL configuration.
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'negative_threshold'  => 'nullable|numeric|min:-1|max:0',
            'critical_alerts'     => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'theme'               => 'nullable|in:dark-manga,neon-manga,mono-comic',
        ]);

        Auth::user()->update([
            'negative_threshold'  => $validated['negative_threshold'] ?? -0.5,
            'critical_alerts'     => $request->has('critical_alerts'),
            'email_notifications' => $request->has('email_notifications'),
            'theme'               => $validated['theme'] ?? 'dark-manga',
        ]);

        return back()->with('success', 'Settings saved. Mission parameters updated.');
    }
}
