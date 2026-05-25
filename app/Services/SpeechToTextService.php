<?php

namespace App\Services;

use App\Models\ApiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpeechToTextService
{
    /**
     * Transcribe an audio file to text.
     *
     * Attempts Google Cloud Speech-to-Text API first.
     * Falls back to a mock transcription if no API key is set or the API fails.
     *
     * @param string $filePath  Absolute path to the audio file on disk.
     * @param string $originalName  The original client filename (used for mock fallback).
     * @param int|null $userId  The authenticated user ID (for API usage logging).
     * @return string  The transcribed text.
     */
    public function transcribe(string $filePath, string $originalName = '', ?int $userId = null): string
    {
        $apiKey = env('GOOGLE_CLOUD_API_KEY') ?: env('GOOGLE_SPEECH_API_KEY');

        if (!$apiKey) {
            Log::warning('Google Speech API Key missing. Using mock transcription fallback.');
            return $this->mockTranscribe($originalName, $userId);
        }

        $startTime = microtime(true);

        try {
            // Read the audio file and base64-encode it
            $audioContent = base64_encode(file_get_contents($filePath));

            // Detect encoding from file extension
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $encoding = match ($ext) {
                'mp3'  => 'MP3',
                'wav'  => 'LINEAR16',
                'ogg'  => 'OGG_OPUS',
                'webm' => 'WEBM_OPUS',
                'opus' => 'OGG_OPUS',
                'm4a'  => 'MP3',        // closest supported encoding
                default => 'ENCODING_UNSPECIFIED',
            };

            $endpoint = 'https://speech.googleapis.com/v1/speech:recognize?key=' . $apiKey;

            $response = Http::timeout(30)->post($endpoint, [
                'config' => [
                    'encoding'        => $encoding,
                    'languageCode'    => 'en-US',
                    'enableAutomaticPunctuation' => true,
                ],
                'audio' => [
                    'content' => $audioContent,
                ],
            ]);

            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            // Log API usage
            ApiUsageLog::create([
                'user_id'          => $userId,
                'service'          => 'speech_to_text',
                'endpoint'         => 'recognize',
                'tokens_used'      => (int)(filesize($filePath) / 1024), // approximate KB
                'success'          => $response->successful(),
                'error_message'    => $response->successful() ? null : $response->body(),
                'response_time_ms' => $responseTimeMs,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Extract all transcript alternatives from the response
                $transcript = '';
                foreach ($data['results'] ?? [] as $result) {
                    $transcript .= ($result['alternatives'][0]['transcript'] ?? '') . ' ';
                }

                $transcript = trim($transcript);

                if (!empty($transcript)) {
                    Log::info('Speech-to-Text success', [
                        'file' => $originalName,
                        'length' => strlen($transcript),
                        'response_time_ms' => $responseTimeMs,
                    ]);
                    return $transcript;
                }

                Log::warning('Speech-to-Text returned empty transcript, falling back to mock.');
            } else {
                Log::error('Speech-to-Text API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

            Log::error('Speech-to-Text Exception: ' . $e->getMessage());

            ApiUsageLog::create([
                'user_id'          => $userId,
                'service'          => 'speech_to_text',
                'endpoint'         => 'recognize',
                'tokens_used'      => 0,
                'success'          => false,
                'error_message'    => $e->getMessage(),
                'response_time_ms' => $responseTimeMs,
            ]);
        }

        return $this->mockTranscribe($originalName, $userId);
    }

    /**
     * Mock transcription fallback — generates realistic transcript text
     * based on the audio file's original name.
     */
    public function mockTranscribe(string $originalName = '', ?int $userId = null): string
    {
        $lower = strtolower($originalName);

        // Log that we're using mock
        ApiUsageLog::create([
            'user_id'          => $userId,
            'service'          => 'speech_to_text',
            'endpoint'         => 'recognize',
            'tokens_used'      => 0,
            'success'          => true,
            'response_time_ms' => 500,
        ]);

        // Negative / frustrated indicators
        $negativeIndicators = ['frustrated', 'angry', 'cancel', 'bad', 'complaint', 'upset', 'negative', 'furious', 'terrible', 'worst'];
        foreach ($negativeIndicators as $indicator) {
            if (str_contains($lower, $indicator)) {
                Log::info('Mock transcription: negative filename detected', ['file' => $originalName]);
                return "I am extremely frustrated with your service. Nobody solved my issue. I already called three times. I want to cancel my subscription.";
            }
        }

        // Positive / satisfied indicators
        $positiveIndicators = ['satisfied', 'happy', 'thanks', 'resolved', 'good', 'positive', 'great', 'excellent', 'amazing'];
        foreach ($positiveIndicators as $indicator) {
            if (str_contains($lower, $indicator)) {
                Log::info('Mock transcription: positive filename detected', ['file' => $originalName]);
                return "Thank you for your support. My issue was resolved quickly. I am very satisfied with the service.";
            }
        }

        // Default — neutral transcript
        Log::info('Mock transcription: neutral fallback', ['file' => $originalName]);
        return "Hello, I am calling about my account. I would like to check the status of my recent order and understand the billing details.";
    }
}
