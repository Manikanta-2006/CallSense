<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Call;
use App\Models\Alert;
use App\Services\SentimentService;
use App\Services\SpeechToTextService;

class CallController extends Controller
{
    /**
     * Display call analysis page with all analyzed calls.
     */
    public function index()
    {
        $calls = Call::latest()->get();
        return view('call_analysis', compact('calls'));
    }

    /**
     * Store and analyze a new call — REAL sentiment analysis pipeline.
     */
    public function store(Request $request, SentimentService $sentimentService, SpeechToTextService $speechToTextService)
    {
        $validated = $request->validate([
            'audio_file'    => 'required|file|mimes:mp3,wav,ogg,m4a,webm,opus|max:20480',
            'transcript'    => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'agent_name'    => 'nullable|string|max:255',
        ]);

        // Store the uploaded audio file
        $audioPath = $request->file('audio_file')->store('call_recordings', 'public');

        $userId = auth()->id();

        $transcript = $validated['transcript'] ?? null;
        
        if (empty($transcript)) {
            // Auto-transcribe from the uploaded audio file
            $uploadedFile = $request->file('audio_file');
            $transcript = $speechToTextService->transcribe(
                $uploadedFile->getRealPath(),
                $uploadedFile->getClientOriginalName(),
                $userId
            );
        }

        // Run sentiment analysis on the transcript — REAL API call
        $apiAnalysis = $sentimentService->analyze($transcript, $userId);

        $score = 0.0;
        $magnitude = 0.0;
        $label = 'Neutral';
        $emotion = 'neutral';
        $sentenceAnalysis = [];
        $rawResponse = [];
        $keywords = [];

        if ($apiAnalysis !== null) {
            $score = $apiAnalysis['score'];
            $magnitude = $apiAnalysis['magnitude'] ?? 0;
            $sentenceAnalysis = $apiAnalysis['sentences'] ?? [];
            $rawResponse = $apiAnalysis['raw_response'] ?? [];
            $keywords = $apiAnalysis['keywords'] ?? [];

            // Use fixed thresholds for labeling sentiment
            // The user's negative_threshold is used for alert generation (line 108), not for labeling
            if ($score > 0.2) {
                $label = 'Positive';
            } elseif ($score < -0.25) {
                $label = 'Negative';
            } else {
                $label = 'Neutral';
            }

            // Emotion classification — mapped from sentiment
            $emotion = Call::classifyEmotion($score, $magnitude);
        }

        // Calculate risk score — REAL formula
        $riskScore = Call::calculateRiskScore($score, $keywords, $userId);

        // Save the full analysis to database
        $call = Call::create([
            'user_id'              => $userId,
            'audio_path'           => $audioPath,
            'customer_name'        => $validated['customer_name'] ?? null,
            'agent_name'           => $validated['agent_name'] ?? null,
            'transcript'           => $transcript,
            'sentiment_score'      => $score,
            'sentiment_magnitude'  => $magnitude,
            'sentiment_label'      => $label,
            'emotion'              => $emotion,
            'risk_score'           => $riskScore,
            'sentence_analysis'    => $sentenceAnalysis,
            'raw_nlp_response'     => $rawResponse,
            'keywords'             => $keywords,
            'status'               => 'analyzed',
        ]);

        // Auto-generate alert if sentiment is very negative
        if ($score < -0.5 || $riskScore >= 70) {
            $severity = 'high';
            if ($score < -0.7 || $riskScore >= 85) {
                $severity = 'critical';
            }

            Alert::create([
                'call_id'     => $call->id,
                'user_id'     => $userId,
                'type'        => 'negative_sentiment',
                'status'      => 'pending',
                'severity'    => $severity,
                'description' => "Auto-alert: Sentiment score {$score}, Risk score {$riskScore}. Emotion: {$emotion}.",
            ]);
        }

        // Detect duplicate complaints
        $duplicates = $sentimentService->detectDuplicateComplaints($keywords, $userId);
        if (count($duplicates) >= 2) {
            Alert::create([
                'call_id'     => $call->id,
                'user_id'     => $userId,
                'type'        => 'repeat_complaint',
                'status'      => 'pending',
                'severity'    => 'medium',
                'description' => 'Repeat complaint detected: ' . implode(', ', $keywords) . '. Matches ' . count($duplicates) . ' recent call(s).',
            ]);
        }

        return back()->with('success', "Call analyzed! Score: {$score} | Emotion: {$emotion} | Risk: {$riskScore}%");
    }

    /**
     * Show detailed analysis for a single call.
     */
    public function show(Call $call)
    {
        $call->load('alerts');
        return response()->json([
            'id'                => $call->id,
            'transcript'        => $call->transcript,
            'customer_name'     => $call->customer_name,
            'agent_name'        => $call->agent_name,
            'sentiment_score'   => $call->sentiment_score,
            'sentiment_label'   => $call->sentiment_label,
            'emotion'           => $call->emotion,
            'risk_score'        => $call->risk_score,
            'sentence_analysis' => $call->sentence_analysis,
            'keywords'          => $call->keywords,
            'alerts'            => $call->alerts,
            'created_at'        => $call->created_at->toDateTimeString(),
        ]);
    }
}
