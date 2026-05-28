<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('courses');

        if ($request->has('aircraft_id')) {
            $query->where('aircraft_id', $request->aircraft_id);
        }

        $categories = $query->paginate(15);
        return response()->json($categories);
    }

    public function show($id)
    {
        $category = Category::with(['courses.aukstructures', 'aircraft'])->findOrFail($id);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50',
            'aircraft_id' => 'required|exists:aircrafts,id',
        ]);

        if (!isset($validated['code']) || !$validated['code']) {
            $validated['code'] = strtoupper(substr(md5($validated['title'] . time()), 0, 8));
        }

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'nullable|string|max:50',
            'aircraft_id' => 'nullable|exists:aircrafts,id',
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        
        return response()->json(null, 204);
    }
}
