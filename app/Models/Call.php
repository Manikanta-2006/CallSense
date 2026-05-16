<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    protected $fillable = [
        'user_id', 'audio_path', 'customer_name', 'agent_name',
        'transcript', 'sentiment_score', 'sentiment_magnitude',
        'sentiment_label', 'emotion', 'risk_score',
        'sentence_analysis', 'raw_nlp_response', 'keywords',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sentiment_score' => 'float',
            'sentiment_magnitude' => 'float',
            'risk_score' => 'float',
            'sentence_analysis' => 'array',
            'raw_nlp_response' => 'array',
            'keywords' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    /**
     * Classify emotion from sentiment score.
     */
    public static function classifyEmotion(float $score, float $magnitude): string
    {
        if ($score <= -0.7 && $magnitude >= 0.5) return 'angry';
        if ($score <= -0.3 && $magnitude >= 0.3) return 'frustrated';
        if ($score >= 0.7 && $magnitude >= 0.5) return 'happy';
        if ($score >= 0.3 && $magnitude >= 0.3) return 'satisfied';
        return 'neutral';
    }

    /**
     * Calculate risk score based on sentiment and complaint patterns.
     */
    public static function calculateRiskScore(float $sentimentScore, array $keywords = [], ?int $userId = null): float
    {
        // Base risk from negative sentiment (0-50 points)
        $baseRisk = max(0, (-$sentimentScore) * 50);

        // Repeat complaint bonus (0-30 points)
        $repeatBonus = 0;
        if (!empty($keywords) && $userId) {
            $recentKeywords = Call::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereNotNull('keywords')
                ->pluck('keywords')
                ->flatten()
                ->toArray();

            $matches = count(array_intersect($keywords, $recentKeywords));
            $repeatBonus = min(30, $matches * 10);
        }

        // High magnitude bonus (0-20 points)
        $magnitudeBonus = 0;

        return min(100, round($baseRisk + $repeatBonus + $magnitudeBonus, 1));
    }
}
