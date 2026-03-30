<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeRecommendationJob;
use App\Models\Plat;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function analyze(Request $request, Plat $plat)
    {
        $user = $request->user();

        $restrictions = $user->dietary_tags ?? [];

        $ingredientTags = $plat->ingredients
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $recommendation = Recommendation::updateOrCreate(
            [
                'user_id' => $user->id,
                'plate_id' => $plat->id,
            ],
            [
                'score' => null,
                'warning_message' => null,
                'status' => 'processing'
            ]
        );

        AnalyzeRecommendationJob::dispatch(
            $recommendation,
            $plat->name,
            $ingredientTags,
            $restrictions
        );

        return response()->json([
            'message' => 'Recommendation analysis started',
            'data' => [
                'recommendation' => $recommendation,
                'ingredient_tags' => $ingredientTags,
                'restrictions' => $restrictions,
                'dish_name' => $plat->name,
            ]
        ], 202);
    }

    public function index(Request $request)
    {
        $recommendations = Recommendation::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($recommendations);
    }

    public function show(Request $request, Plat $plat)
    {
        $recommendation = Recommendation::where('user_id', $request->user()->id)
            ->where('plate_id', $plat->id)
            ->first();

        if (!$recommendation) {
            return response()->json([
                'message' => 'Recommendation not found'
            ], 404);
        }

        
        if ($recommendation->status === 'processing') {
            $label = 'Processing';
        } elseif ($recommendation->status === 'failed') {
            $label = 'Failed';
        } else {
            $label = match (true) {
                $recommendation->score >= 80 => 'Highly Recommended',
                $recommendation->score >= 50 => 'Recommended with notes',
                default => 'Not Recommended',
            };
        }


        return response()->json([
            'score' => $recommendation->score,
            'label' => $label,
            'warning_message' => $recommendation->warning_message,
            'status' => $recommendation->status,
        ]);
    }
}
