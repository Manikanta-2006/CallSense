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
            'poor', 'slow', 'broken', 'fail', 'failure', 'annoyed',
            'annoying', 'ridiculous', 'incompetent', 'trash',
            'cancel', 'refund', 'complaint', 'overcharged', 'scam',
            'defective', 'wrong', 'delay', 'waiting', 'waited',
            'unresolved', 'ignored', 'misleading', 'fraud', 'sue',
            'lawsuit', 'regret', 'unhappy', 'dissatisfied',
        ];

        // Complaint-intent phrases — these indicate strong negative intent
        // even without emotional words
        $negativePhrases = [
            'want to cancel', 'cancel my', 'close my account',
            'stop my subscription', 'end my subscription',
            'want a refund', 'give me my money', 'money back',
            'not working', 'doesn\'t work', 'does not work',
            'file a complaint', 'speak to manager', 'speak to supervisor',
            'never again', 'switch to', 'moving to', 'leaving',
            'wasted my time', 'waste of time', 'waste of money',
            'taking too long', 'no response', 'no reply', 'no help',
        ];

        // Intensity amplifiers boost the score magnitude
        $amplifiers = [
            'very', 'extremely', 'really', 'absolutely', 'totally',
            'completely', 'utterly', 'so', 'incredibly', 'highly',
            'terribly', 'seriously', 'insanely', 'super',
        ];

        $pos = 0;
        $neg = 0;

        foreach ($positiveWords as $word) {
            if (str_contains($lower, $word)) $pos++;
        }
        foreach ($negativeWords as $word) {
            if (str_contains($lower, $word)) $neg++;
        }

        // Check for complaint-intent phrases (each counts as 1.5 negative hits)
        $phraseHits = 0;
        foreach ($negativePhrases as $phrase) {
            if (str_contains($lower, $phrase)) $phraseHits++;
        }

        // Check for intensity amplifiers to boost the score
        $amplifierCount = 0;
        foreach ($amplifiers as $amp) {
            if (str_contains($lower, $amp)) $amplifierCount++;
        }

        // Base score: word hits + phrase hits (phrases count as 1.5 neg each)
        $effectiveNeg = $neg + ($phraseHits * 1.5);
        $rawDiff = $pos - $effectiveNeg;
        $baseScore = $rawDiff * 0.6;

        // Amplifiers boost the score in the dominant direction
        if ($amplifierCount > 0 && $rawDiff !== 0) {
            $boost = $amplifierCount * 0.15;
            $baseScore = $rawDiff > 0
                ? $baseScore + $boost
                : $baseScore - $boost;
        }

        $score = max(-1.0, min(1.0, $baseScore));

        // Magnitude reflects emotional intensity (not direction)
        $totalHits = $pos + $neg + $phraseHits;
        $magnitude = min(1.0, ($totalHits * 0.4) + ($amplifierCount * 0.2));
        if ($totalHits === 0) $magnitude = 0.1;

        // Sentence-level mock analysis
        $rawSentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        // If no sentence-ending punctuation, treat entire text as one sentence
        if (empty($rawSentences)) {
            $rawSentences = [$text];
        }
        $sentences = [];
        foreach ($rawSentences as $sentence) {
            $sLower = strtolower($sentence);
            $sPos = 0;
            $sNeg = 0;
            $sAmp = 0;
            foreach ($positiveWords as $w) { if (str_contains($sLower, $w)) $sPos++; }
            foreach ($negativeWords as $w) { if (str_contains($sLower, $w)) $sNeg++; }
            foreach ($amplifiers as $a) { if (str_contains($sLower, $a)) $sAmp++; }

            $sPhraseHits = 0;
            foreach ($negativePhrases as $p) { if (str_contains($sLower, $p)) $sPhraseHits++; }

            $sEffectiveNeg = $sNeg + ($sPhraseHits * 1.5);
            $sDiff = $sPos - $sEffectiveNeg;
            $sScore = $sDiff * 0.6;
            if ($sAmp > 0 && $sDiff !== 0) {
                $sScore = $sDiff > 0 ? $sScore + ($sAmp * 0.15) : $sScore - ($sAmp * 0.15);
            }
            $sScore = max(-1.0, min(1.0, $sScore));
            $sMag = min(1.0, (($sPos + $sNeg + $sPhraseHits) * 0.4) + ($sAmp * 0.2));
            if (($sPos + $sNeg + $sPhraseHits) === 0) $sMag = 0.1;

            $sentences[] = [
                'text'      => trim($sentence),
                'score'     => round($sScore, 2),
                'magnitude' => round($sMag, 2),
            ];
        }

        $keywords = $this->extractKeywords($text);

        Log::info('Mock sentiment analysis', [
            'text' => $text,
            'pos_hits' => $pos,
            'neg_hits' => $neg,
            'amplifiers' => $amplifierCount,
            'score' => round($score, 2),
            'magnitude' => round($magnitude, 2),
        ]);

        return [
            'score'        => round($score, 2),
            'magnitude'    => round($magnitude, 2),
            'sentences'    => $sentences,
            'keywords'     => $keywords,
            'raw_response' => [
                'source' => 'mock_fallback',
                'documentSentiment' => [
                    'score' => round($score, 2),
                    'magnitude' => round($magnitude, 2),
                ],
            ],
        ];
    }
}
