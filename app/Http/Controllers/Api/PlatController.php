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

        return response()->json($plat);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' =>
            [
                'required',
                'max:250',
                'string',
                Rule::unique('plats')->where('user_id', $request->user()->id)
            ],
            'price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('plats', 'public');
            $fields['photo'] = $path;
        }

        $plat = $request->user()->plats()->create($fields);


        return response()->json([
            'message' => 'Plat created successfully',
            'data' => $plat
        ], 201);
    }

    public function update(Request $request, Plat $plat)
    {
        Gate::authorize('update', $plat);
        $fields = $request->validate([
            'name' =>
            [
                'required',
                'max:250',
                'string',
                Rule::unique('plats')->where('user_id', $request->user()->id)->ignore($plat)
            ],
            'price' => 'required|numeric|min:0'
        ]);

        $plat->update($fields);

        return response()->json([
            'message' => 'Update successfully',
            'data' => $plat
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
