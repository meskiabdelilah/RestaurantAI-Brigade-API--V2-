<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    // GET /api/ingredients
    public function index()
    {
        $ingredients = Ingredient::latest()->get();

        return response()->json($ingredients);
    }

    // POST /api/ingredients
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ingredients', 'name'),
            ],
            'tags' => 'nullable|array',
            'tags.*' => 'string'
        ]);

        $ingredient = Ingredient::create($fields);

        return response()->json([
            'message' => 'Ingredient created successfully',
            'data' => $ingredient
        ], 201);
    }

    // PUT /api/ingredients/{id}
    public function update(Request $request, Ingredient $ingredient)
    {
        $fields = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ingredients', 'name')->ignore($ingredient->id),
            ],
            'tags' => 'nullable|array',
            'tags.*' => 'string'

        ]);

        $ingredient->update($fields);

        return response()->json([
            'message' => 'Ingredient updated successfully',
            'data' => $ingredient
        ]);
    }

    // DELETE /api/ingredients/{id}
    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return response()->json([
            'message' => 'Ingredient deleted successfully'
        ]);
    }
}
