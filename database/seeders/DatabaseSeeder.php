<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@callsense.com'],
            ['name' => 'Commander Rithvik', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        // Generate 3 Default Calls
        $call1 = \App\Models\Call::create([
            'user_id' => $user->id,
            'audio_path' => 'demo/call_1.mp3',
            'customer_name' => 'John Doe',
            'agent_name' => 'Sarah Connor',
            'transcript' => 'I am very frustrated. The product is broken and support is ignoring me.',
            'sentiment_score' => -0.8,
            'sentiment_magnitude' => 0.9,
            'sentiment_label' => 'Negative',
            'emotion' => 'angry',
            'risk_score' => 85,
            'status' => 'analyzed',
            'keywords' => ['broken', 'ignoring', 'frustrated'],
            'created_at' => now()->subHours(2),
        ]);

        $call2 = \App\Models\Call::create([
            'user_id' => $user->id,
            'audio_path' => 'demo/call_2.mp3',
            'customer_name' => 'Jane Smith',
            'agent_name' => 'Kyle Reese',
            'transcript' => 'Thank you so much! Your help was amazing and my issue is completely resolved.',
            'sentiment_score' => 0.9,
            'sentiment_magnitude' => 0.8,
            'sentiment_label' => 'Positive',
            'emotion' => 'happy',
            'risk_score' => 5,
            'status' => 'analyzed',
            'keywords' => ['amazing', 'resolved', 'thank you'],
            'created_at' => now()->subHours(5),
        ]);

        $call3 = \App\Models\Call::create([
            'user_id' => $user->id,
            'audio_path' => 'demo/call_3.mp3',
            'customer_name' => 'Mark Johnson',
            'agent_name' => 'Sarah Connor',
            'transcript' => 'I want to know the status of my refund. It has been two weeks.',
            'sentiment_score' => -0.2,
            'sentiment_magnitude' => 0.3,
            'sentiment_label' => 'Neutral',
            'emotion' => 'neutral',
            'risk_score' => 45,
            'status' => 'analyzed',
            'keywords' => ['refund', 'status', 'two weeks'],
            'created_at' => now()->subDays(1),
        ]);

        // Generate Alert
        \App\Models\Alert::create([
            'call_id' => $call1->id,
            'user_id' => $user->id,
            'type' => 'negative_sentiment',
            'status' => 'pending',
            'severity' => 'critical',
            'description' => 'Auto-alert: Extreme negative sentiment detected. Customer threatened to leave.',
            'created_at' => now()->subHours(2),
        ]);

        // Generate Login Activity
        \App\Models\LoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.10',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'action' => 'login',
            'login_time' => now()->subHours(1),
            'created_at' => now()->subHours(1),
        ]);

        // Generate API Usage
        \App\Models\ApiUsageLog::create([
            'user_id' => $user->id,
            'service' => 'nlp_sentiment',
            'endpoint' => 'analyzeSentiment',
            'tokens_used' => 120,
            'success' => true,
            'response_time_ms' => 450,
            'created_at' => now()->subHours(2),
        ]);
        \App\Models\ApiUsageLog::create([
            'user_id' => $user->id,
            'service' => 'speech_to_text',
            'endpoint' => 'recognize',
            'tokens_used' => 45,
            'success' => true,
            'response_time_ms' => 850,
            'created_at' => now()->subHours(5),
        ]);
    }
}
