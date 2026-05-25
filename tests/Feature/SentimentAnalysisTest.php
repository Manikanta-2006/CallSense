<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Call;
use App\Models\Alert;
use App\Services\SentimentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SentimentAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private SentimentService $sentimentService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sentimentService = new SentimentService();
        $this->user = User::factory()->create();
    }

    /**
     * Test the negative/frustrated transcript analysis.
     */
    public function test_negative_frustrated_transcript_analysis(): void
    {
        $transcript = "I am extremely frustrated with your service.
Nobody solved my issue.
I already called three times.
I want to cancel my subscription.";

        // 1. Test Sentiment Service directly
        $result = $this->sentimentService->analyze($transcript, $this->user->id);

        $this->assertNotNull($result);
        $this->assertEquals(-1.0, $result['score']);
        $this->assertEquals(1.0, $result['magnitude']);
        
        // Assert keywords
        $this->assertContains('service', $result['keywords']);
        $this->assertContains('cancel', $result['keywords']);
        $this->assertContains('issue', $result['keywords']);
        $this->assertContains('frustrated', $result['keywords']);

        // Assert sentence level analysis
        $this->assertCount(4, $result['sentences']);
        
        $this->assertEquals("I am extremely frustrated with your service.", $result['sentences'][0]['text']);
        $this->assertEquals(-0.75, $result['sentences'][0]['score']);
        
        $this->assertEquals("Nobody solved my issue.", $result['sentences'][1]['text']);
        $this->assertEquals(-0.75, $result['sentences'][1]['score']);
        
        $this->assertEquals("I already called three times.", $result['sentences'][2]['text']);
        $this->assertEquals(0.0, $result['sentences'][2]['score']);
        
        $this->assertEquals("I want to cancel my subscription.", $result['sentences'][3]['text']);
        $this->assertEquals(-1.0, $result['sentences'][3]['score']);

        // 2. Test Call Model logic
        $emotion = Call::classifyEmotion($result['score'], $result['magnitude']);
        $this->assertEquals('angry', $emotion);

        $riskScore = Call::calculateRiskScore($result['score'], $result['keywords'], $this->user->id);
        $this->assertEquals(50.0, $riskScore);

        // 3. Test Controller/Database Flow
        $this->actingAs($this->user);

        // Prepare mock file
        $audioFile = \Illuminate\Http\UploadedFile::fake()->create('call.mp3', 500);

        $response = $this->post(route('calls.store'), [
            'audio_file' => $audioFile,
            'transcript' => $transcript,
            'customer_name' => 'Rithvik Teja',
            'agent_name' => 'Support Agent',
        ]);

        $response->assertStatus(302); // Redirects back

        // Assert call record in database
        $this->assertDatabaseHas('calls', [
            'user_id' => $this->user->id,
            'customer_name' => 'Rithvik Teja',
            'sentiment_score' => -1.0,
            'sentiment_label' => 'Negative',
            'emotion' => 'angry',
            'risk_score' => 50.0,
        ]);

        // Assert auto-generated critical alert
        $this->assertDatabaseHas('alerts', [
            'user_id' => $this->user->id,
            'type' => 'negative_sentiment',
            'severity' => 'critical',
            'status' => 'pending',
        ]);
    }

    /**
     * Test the positive/satisfied transcript analysis.
     */
    public function test_positive_satisfied_transcript_analysis(): void
    {
        $transcript = "Thank you for your support.
My issue was resolved quickly.
I am very satisfied with the service.";

        // 1. Test Sentiment Service directly
        $result = $this->sentimentService->analyze($transcript, $this->user->id);

        $this->assertNotNull($result);
        $this->assertEquals(1.0, $result['score']);
        $this->assertEquals(1.0, $result['magnitude']);

        // Assert keywords
        $this->assertContains('service', $result['keywords']);
        $this->assertContains('issue', $result['keywords']);
        $this->assertContains('support', $result['keywords']);

        // Assert sentence level analysis
        $this->assertCount(3, $result['sentences']);

        $this->assertEquals("Thank you for your support.", $result['sentences'][0]['text']);
        $this->assertEquals(0.6, $result['sentences'][0]['score']);

        $this->assertEquals("My issue was resolved quickly.", $result['sentences'][1]['text']);
        $this->assertEquals(-0.15, $result['sentences'][1]['score']);

        $this->assertEquals("I am very satisfied with the service.", $result['sentences'][2]['text']);
        $this->assertEquals(0.75, $result['sentences'][2]['score']);

        // 2. Test Call Model logic
        $emotion = Call::classifyEmotion($result['score'], $result['magnitude']);
        $this->assertEquals('happy', $emotion);

        $riskScore = Call::calculateRiskScore($result['score'], $result['keywords'], $this->user->id);
        $this->assertEquals(0.0, $riskScore);

        // 3. Test Controller/Database Flow
        $this->actingAs($this->user);

        // Prepare mock file
        $audioFile = \Illuminate\Http\UploadedFile::fake()->create('call.mp3', 500);

        $response = $this->post(route('calls.store'), [
            'audio_file' => $audioFile,
            'transcript' => $transcript,
            'customer_name' => 'Rithvik Teja',
            'agent_name' => 'Support Agent',
        ]);

        $response->assertStatus(302);

        // Assert call record in database
        $this->assertDatabaseHas('calls', [
            'user_id' => $this->user->id,
            'customer_name' => 'Rithvik Teja',
            'sentiment_score' => 1.0,
            'sentiment_label' => 'Positive',
            'emotion' => 'happy',
            'risk_score' => 0.0,
        ]);

        // Assert no alert created for positive sentiment
        $this->assertDatabaseMissing('alerts', [
            'user_id' => $this->user->id,
            'type' => 'negative_sentiment',
        ]);
    }

    /**
     * Test the auto-transcription fallback when no transcript is provided.
     */
    public function test_auto_transcription_fallback_for_uploaded_file(): void
    {
        $this->actingAs($this->user);

        // Prepare mock file with a name that triggers the 'frustrated' mock transcription
        $audioFile = \Illuminate\Http\UploadedFile::fake()->create('frustrated_customer_call.mp3', 500);

        // Make the request with an empty transcript
        $response = $this->post(route('calls.store'), [
            'audio_file' => $audioFile,
            'transcript' => '', // Empty transcript
            'customer_name' => 'Auto Transcribe User',
        ]);

        $response->assertStatus(302);

        // The mock transcription should yield a frustrated transcript
        // "I am extremely frustrated with your service. Nobody solved my issue. I already called three times. I want to cancel my subscription."
        // And the sentiment score should be negative.

        $this->assertDatabaseHas('calls', [
            'user_id' => $this->user->id,
            'customer_name' => 'Auto Transcribe User',
            'sentiment_label' => 'Negative',
        ]);
        
        $call = Call::where('user_id', $this->user->id)->where('customer_name', 'Auto Transcribe User')->first();
        $this->assertStringContainsString('extremely frustrated', $call->transcript);

        // Verify API usage log was created for speech-to-text
        $this->assertDatabaseHas('api_usage_logs', [
            'user_id' => $this->user->id,
            'service' => 'speech_to_text',
        ]);
    }
}
