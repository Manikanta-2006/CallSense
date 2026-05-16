<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            $table->string('customer_name')->nullable()->after('audio_path');
            $table->string('agent_name')->nullable()->after('customer_name');
            $table->string('emotion')->nullable()->after('sentiment_label'); // angry, frustrated, happy, satisfied, neutral
            $table->float('risk_score')->nullable()->after('emotion');
            $table->float('sentiment_magnitude')->nullable()->after('sentiment_score');
            $table->json('sentence_analysis')->nullable()->after('risk_score');
            $table->json('raw_nlp_response')->nullable()->after('sentence_analysis');
            $table->json('keywords')->nullable()->after('raw_nlp_response');
            $table->string('status')->default('analyzed')->after('keywords'); // pending, processing, analyzed, failed
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'customer_name', 'agent_name', 'emotion',
                'risk_score', 'sentiment_magnitude', 'sentence_analysis',
                'raw_nlp_response', 'keywords', 'status'
            ]);
        });
    }
};
