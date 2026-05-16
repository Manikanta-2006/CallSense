<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('service'); // nlp_sentiment, speech_to_text
            $table->string('endpoint')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
