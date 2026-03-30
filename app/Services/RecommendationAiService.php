<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationAiService
{
    public function analyze(string $dishName, array $ingredientTags, array $restrictions): array
    {
        $ingredients = implode(', ', $ingredientTags);
        $restrictionsText = implode(', ', $restrictions);

        $prompt = <<<PROMPT
Analyze the nutritional compatibility between this dish and the user's dietary restrictions.

DISH: {$dishName}
INGREDIENT TAGS: {$ingredients}
USER RESTRICTIONS: {$restrictionsText}

Tag mapping rules:
"vegan" restriction conflicts with: contains_meat, contains_lactose
"no_sugar" restriction conflicts with: contains_sugar
"no_cholesterol" restriction conflicts with: contains_cholesterol
"gluten_free" restriction conflicts with: contains_gluten
"no_lactose" restriction conflicts with: contains_lactose

Calculate score: start at 100, subtract 25 for each conflict found.

Respond ONLY with this JSON:
{"score": <0-100>, "warning_message": "<in French if score <= 50, else empty string>"}
PROMPT;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => env('OPENROUTER_MODEL', 'openrouter/free'),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ]
            ],
        ]);

        if ($response->failed()) {
            Log::error('AI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);


            return $this->fallbackAnalyze($ingredientTags, $restrictions);
        }

        $content = $response->json('choices.0.message.content', '{}');

        $parsed = $this->parseResponse($content);

        if (!isset($parsed['score'])) {
            return $this->fallbackAnalyze($ingredientTags, $restrictions);
        }

        return $parsed;
    }

    private function parseResponse(string $text): array
    {
        $text = preg_replace('/```json|```/', '', $text);
        $text = trim($text);

        preg_match('/{.*}/s', $text, $matches);
        $data = json_decode($matches[0] ?? '{}', true);

        if (!isset($data['score'])) {
            Log::warning('AI response parsing failed', ['text' => $text]);
            return [];
        }

        return [
            'score' => max(0, min(100, (int) $data['score'])),
            'warning_message' => $data['warning_message'] ?? null,
        ];
    }

    private function fallbackAnalyze(array $ingredientTags, array $restrictions): array
    {
        $rules = [
            'vegan' => ['contains_meat', 'contains_lactose'],
            'no_sugar' => ['contains_sugar'],
            'no_cholesterol' => ['contains_cholesterol'],
            'gluten_free' => ['contains_gluten'],
            'no_lactose' => ['contains_lactose'],
        ];

        $conflicts = [];

        foreach ($restrictions as $restriction) {
            if (!isset($rules[$restriction])) {
                continue;
            }

            foreach ($rules[$restriction] as $conflictTag) {
                if (in_array($conflictTag, $ingredientTags, true)) {
                    $conflicts[] = $conflictTag;
                }
            }
        }

        $conflicts = array_values(array_unique($conflicts));

        $score = 100 - (count($conflicts) * 25);
        $score = max(0, $score);

        $warningMessage = null;

       if ($score <= 50 && count($conflicts) > 0) {
            $translated = $this->translateConflictTags($conflicts);
            $warningMessage = 'Ce plat ne convient pas à votre régime car il contient : ' . implode(', ', $translated) . '.';
        }

        return [
            'score' => $score,
            'warning_message' => $warningMessage,
        ];
    }

    private function translateConflictTags(array $conflicts): array
    {
        $map = [
            'contains_meat' => 'de la viande',
            'contains_lactose' => 'du lactose',
            'contains_sugar' => 'du sucre',
            'contains_cholesterol' => 'du cholestérol',
            'contains_gluten' => 'du gluten',
        ];

        return array_map(function ($tag) use ($map) {
            return $map[$tag] ?? $tag;
        }, $conflicts);
    }
}