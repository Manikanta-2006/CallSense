<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * Update alert status — REAL resolution tracking.
     */
    public function updateStatus(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'status'           => 'required|in:pending,investigating,resolved',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'resolved') {
            $updateData['resolved_by'] = Auth::id();
            $updateData['resolved_at'] = now();
            $updateData['resolution_notes'] = $validated['resolution_notes'] ?? 'Resolved by agent.';
        }

        if ($validated['status'] === 'investigating') {
            $updateData['assigned_to'] = Auth::id();
        }

        $alert->update($updateData);

        return back()->with('success', "Alert #{$alert->id} status updated to: {$validated['status']}");
    }

    /**
     * Escalate an alert to supervisor queue.
     */
    public function escalate(Alert $alert)
    {
        $alert->update([
            'severity' => 'critical',
            'status'   => 'investigating',
            'assigned_to' => Auth::id(),
            'description' => $alert->description . ' [ESCALATED by ' . Auth::user()->name . ' at ' . now()->toDateTimeString() . ']',
        ]);

        return back()->with('success', "Alert #{$alert->id} escalated to supervisor queue.");
    }

    /**
     * Create manual alert from a call.
     */
    public function createFromCall(Request $request, Call $call)
    {
        $validated = $request->validate([
            'type'        => 'nullable|string|in:negative_sentiment,repeat_complaint,escalation',
            'severity'    => 'nullable|string|in:low,medium,high,critical',
            'description' => 'nullable|string|max:1000',
        ]);

        Alert::create([
            'call_id'     => $call->id,
            'user_id'     => Auth::id(),
            'type'        => $validated['type'] ?? 'escalation',
            'status'      => 'pending',
            'severity'    => $validated['severity'] ?? 'high',
            'description' => $validated['description'] ?? 'Manual escalation by agent.',
        ]);

        return back()->with('success', "Alert created for call #{$call->id}.");
    }
}
