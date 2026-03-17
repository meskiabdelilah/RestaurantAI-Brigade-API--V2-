<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plat;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = $request->user()->categories()->latest()->get();
        return response()->json($categories);
    }


    public function show(Request $request, Category $category)
    {
        Gate::authorize('view', $category);

        return response()->json($category);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => [
                'required',
                'max:250',
                'string',
                Rule::unique('categories')->where('user_id', $request->user()->id)
            ]
        ]);

        $category = $request->user()->categories()->create($fields);


        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        Gate::authorize('update', $category);

        $fields = $request->validate([
            'name' => [
                'required',
                'max:250',
                'string',
                Rule::unique('categories')->where('user_id', $request->user()->id)->ignore($category)
            ]
        ]);

        $category->update($fields);

        return response()->json([
            'message' => 'Update successfully',
            'data' => $category
        ], 200);
    }

    public function destroy(Request $request, Category $category)
    {

        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json([
            'message' => 'Delete successfully',
            'data' => $category
        ], 200);
    }

    public function associatePlats(Request $request, Category $category)
    {
        Gate::authorize('update', $category);

        $request->validate([
            'plat_ids' => 'required|array',
            'plat_ids.*' => 'exists:plats,id'
        ]);


        $plats = $request->user()->plats()->whereIn('id', $request->plat_ids)->get();

        $category->plats()->saveMany($plats);

        return response()->json([
            'message' => 'Plats associés avec succès',
        ], 200);
    }

    public function getPlatsByCategory(Category $category)
    {

        Gate::authorize('view', $category);

        $plats = $category->plats()->latest()->get();

        return response()->json($plats, 200);
    }
}
