<?php

namespace App\Jobs;

use App\Models\Recommendation;
use App\Services\RecommendationAiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class AnalyzeRecommendationJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Recommendation $recommendation,
        public string $dishName,
        public array $ingredientTags,
        public array $restrictions
    ) {}

    public function handle(RecommendationAiService $service): void
    {
        try {
            $result = $service->analyze(
                $this->dishName,
                $this->ingredientTags,
                $this->restrictions
            );

            $this->recommendation->update([
                'score' => $result['score'] ?? null,
                'warning_message' => $result['warning_message'] ?? null,
                'status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            $this->recommendation->update([
                'status' => 'failed',
            ]);
        }
    }
}