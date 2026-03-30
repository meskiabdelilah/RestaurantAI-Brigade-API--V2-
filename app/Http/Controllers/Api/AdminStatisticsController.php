<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Plat;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    public function index()
    {
        $totalPlats = Plat::count();

        $totalCategories = Category::count();

        $totalIngredients = Ingredient::count();

        $totalRecommendations = Recommendation::count();

        $mostRecommendedPlate = Recommendation::join('plats', 'recommendations.plate_id', '=', 'plats.id')
            ->select(
                'plats.id',
                'plats.name',
                DB::raw('AVG(recommendations.score) as avg_score')
            )
            ->groupBy('plats.id', 'plats.name')
            ->orderByDesc('avg_score')
            ->first();

        $leastRecommendedPlate = Recommendation::join('plats', 'recommendations.plate_id', '=', 'plats.id')
            ->select(
                'plats.id',
                'plats.name',
                DB::raw('AVG(recommendations.score) as avg_score')
            )
            ->groupBy('plats.id', 'plats.name')
            ->orderBy('avg_score')
            ->first();

        $categoryWithMostPlates = Category::join('plats', 'plats.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(plats.id) as plats_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('plats_count')
            ->first();

        return response()->json([
            'total_plats' => $totalPlats,
            'total_categories' => $totalCategories,
            'total_ingredients' => $totalIngredients,
            'total_recommendations' => $totalRecommendations,
            'most_recommended_plate' => $mostRecommendedPlate ? [
                'id' => $mostRecommendedPlate->id,
                'name' => $mostRecommendedPlate->name,
                'avg_score' => round($mostRecommendedPlate->avg_score, 2),
            ] : null,
            'least_recommended_plate' => $leastRecommendedPlate ? [
                'id' => $leastRecommendedPlate->id,
                'name' => $leastRecommendedPlate->name,
                'avg_score' => round($leastRecommendedPlate->avg_score, 2),
            ] : null,
            'category_with_most_plates' => $categoryWithMostPlates ? [
                'id' => $categoryWithMostPlates->id,
                'name' => $categoryWithMostPlates->name,
                'plats_count' => $categoryWithMostPlates->plats_count,
            ] : null,
        ]);
    }
}
