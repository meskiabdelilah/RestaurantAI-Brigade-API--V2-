<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plat;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;


class PlatController extends Controller
{
    public function index(Request $request)
    {
        $plats = $request->user()->plats()->latest()->get();

        return response()->json($plats);
    }

    public function show(Request $request, Plat $plat)
    {
        Gate::authorize('view', $plat);

    return response()->json($plat->load('ingredients'));
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => [
                'required',
                'max:250',
                'string',
                Rule::unique('plats')->where('user_id', $request->user()->id)
            ],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
            'ingredient_ids' => 'nullable|array',
            'ingredient_ids.*' => 'exists:ingredients,id',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('plats', 'public');
            $fields['image'] = $path;
        }

        $ingredientIds = $fields['ingredient_ids'] ?? [];
        unset($fields['ingredient_ids']);

        $plat = $request->user()->plats()->create($fields);

        $plat->ingredients()->sync($ingredientIds);

        return response()->json([
            'message' => 'Plat created successfully',
            'data' => $plat->load('ingredients')
        ], 201);
    }

    public function update(Request $request, Plat $plat)
    {
        Gate::authorize('update', $plat);

        $fields = $request->validate([
            'name' => [
                'required',
                'max:250',
                'string',
                Rule::unique('plats')->where('user_id', $request->user()->id)->ignore($plat)
            ],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'is_available' => 'sometimes|boolean',
            'ingredient_ids' => 'nullable|array',
            'ingredient_ids.*' => 'exists:ingredients,id',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('plats', 'public');
            $fields['image'] = $path;
        }

        $ingredientIds = $fields['ingredient_ids'] ?? [];
        unset($fields['ingredient_ids']);

        $plat->update($fields);
        $plat->ingredients()->sync($ingredientIds);

        return response()->json([
            'message' => 'Update successfully',
            'data' => $plat->load('ingredients')
        ], 200);
    }

    public function destroy(Request $request, Plat $plat)
    {
        Gate::authorize('delete', $plat);

        $plat->delete();

        return response()->json([
            'message' => 'Delete successfully',
            'data' => $plat
        ], 200);
    }
}
