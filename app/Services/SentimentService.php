<?php

namespace App\Services;

use App\Models\ApiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SentimentService
{
    /**
     * Analyze full document sentiment + sentence-level analysis.
     */
    public function analyze(string $text, ?int $userId = null): ?array
    {
        $apiKey = env('GOOGLE_CLOUD_API_KEY');

        if (!$apiKey) {
            Log::warning('Google Cloud API Key missing. Using mock sentiment fallback.');
            return $this->mockAnalyze($text);
        }

        $endpoint = 'https://language.googleapis.com/v1/documents:analyzeSentiment?key=' . $apiKey;

        $startTime = microtime(true);

        try {
            $response = Http::post($endpoint, [
                'document'     => ['type' => 'PLAIN_TEXT', 'content' => $text],
                'encodingType' => 'UTF8',
            ]);

            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            // Log API usage
            ApiUsageLog::create([
                'user_id'          => $userId,
                'service'          => 'nlp_sentiment',
                'endpoint'         => 'analyzeSentiment',
                'tokens_used'      => str_word_count($text),
                'success'          => $response->successful(),
                'error_message'    => $response->successful() ? null : $response->body(),
                'response_time_ms' => $responseTimeMs,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Extract sentence-level analysis
                $sentences = [];
                foreach ($data['sentences'] ?? [] as $sentence) {
                    $sentences[] = [
                        'text'      => $sentence['text']['content'] ?? '',
                        'score'     => $sentence['sentiment']['score'] ?? 0,
                        'magnitude' => $sentence['sentiment']['magnitude'] ?? 0,
                    ];
                }

                // Extract keywords from sentences with high magnitude
                $keywords = $this->extractKeywords($text);

                return [
                    'score'             => $data['documentSentiment']['score'] ?? 0,
                    'magnitude'         => $data['documentSentiment']['magnitude'] ?? 0,
                    'sentences'         => $sentences,
                    'keywords'          => $keywords,
                    'raw_response'      => $data,
                ];
            }

            Log::error('Google NLP API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Google NLP Exception: ' . $e->getMessage());

            // Log failed API usage
            ApiUsageLog::create([
                'user_id'       => $userId,
                'service'       => 'nlp_sentiment',
                'endpoint'      => 'analyzeSentiment',
                'tokens_used'   => str_word_count($text),
                'success'       => false,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $this->mockAnalyze($text);
    }

    /**
     * Extract keywords / complaint topics from text.
     */
    public function extractKeywords(string $text): array
    {
        $lower = strtolower($text);
        $found = [];

        $watchWords = [
            'refund', 'delay', 'service', 'billing', 'charge', 'cancel',
            'wait', 'issue', 'broken', 'error', 'complaint', 'wrong',
            'terrible', 'horrible', 'worst', 'disappointed', 'frustrated',
            'payment', 'overcharged', 'scam', 'rude', 'unprofessional',
            'shipping', 'delivery', 'quality', 'defective', 'return',
            'warranty', 'support', 'manager', 'supervisor', 'escalate',
        ];

        foreach ($watchWords as $word) {
            $count = substr_count($lower, $word);
            if ($count > 0) {
                $found[] = $word;
            }
        }

        return $found;
    }

    /**
     * Detect duplicate/repeat complaint themes.
     */
    public function detectDuplicateComplaints(array $keywords, ?int $userId = null): array
    {
        if (empty($keywords)) return [];

        $recentCalls = \App\Models\Call::where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('keywords')
            ->latest()
            ->take(50)
            ->get(['id', 'keywords', 'created_at']);

        $duplicates = [];
        foreach ($recentCalls as $call) {
            $callKeywords = is_array($call->keywords) ? $call->keywords : [];
            $overlap = array_intersect($keywords, $callKeywords);
            if (count($overlap) >= 2) {
                $duplicates[] = [
                    'call_id'  => $call->id,
                    'date'     => $call->created_at->toDateTimeString(),
                    'matching' => array_values($overlap),
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Mock analysis with sentence-level support.
     */
    private function mockAnalyze(string $text): array
    {
        $lower = strtolower($text);

        $positiveWords = [
            'great', 'awesome', 'good', 'excellent', 'love', 'happy',
            'thanks', 'thank you', 'helpful', 'pleased', 'satisfied',
            'resolved', 'perfect', 'wonderful', 'appreciated', 'amazing',
            'brilliant', 'fantastic', 'outstanding', 'superb',
        ];

        $negativeWords = [
            'bad', 'terrible', 'awful', 'hate', 'angry', 'upset',
            'worst', 'unhelpful', 'stupid', 'frustrated', 'useless',
            'disappointed', 'horrible', 'unacceptable', 'rude', 'never',
            'pathetic', 'disgusting', 'furious', 'waste', 'problem',
        ];

        $pos = 0;
        $neg = 0;

        foreach ($positiveWords as $word) {
            if (str_contains($lower, $word)) $pos++;
        }
        foreach ($negativeWords as $word) {
            if (str_contains($lower, $word)) $neg++;
        }

        // Net score: positive → +1, negative → -1, neutral → 0
        $score = ($pos - $neg) * 0.4;
        $score = max(-1.0, min(1.0, $score));
        $magnitude = abs($score) > 0 ? 0.8 : 0.1;

        // Sentence-level mock analysis
        $rawSentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = [];
        foreach ($rawSentences as $sentence) {
            $sLower = strtolower($sentence);
            $sPos = 0;
            $sNeg = 0;
            foreach ($positiveWords as $w) { if (str_contains($sLower, $w)) $sPos++; }
            foreach ($negativeWords as $w) { if (str_contains($sLower, $w)) $sNeg++; }
            $sScore = ($sPos - $sNeg) * 0.5;
            $sScore = max(-1.0, min(1.0, $sScore));
            $sentences[] = [
                'text'      => trim($sentence),
                'score'     => $sScore,
                'magnitude' => abs($sScore) > 0 ? 0.7 : 0.1,
            ];
        }

        $keywords = $this->extractKeywords($text);

        return [
            'score'        => $score,
            'magnitude'    => $magnitude,
            'sentences'    => $sentences,
            'keywords'     => $keywords,
            'raw_response' => [
                'source' => 'mock_fallback',
                'documentSentiment' => [
                    'score' => $score,
                    'magnitude' => $magnitude,
                ],
            ],
        ];
    }
}
