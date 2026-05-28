<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group2learning;

class Group2learningController extends Controller
{
    public function index(Request $request)
    {
        $query = Group2learning::query();

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $learnings = $query->get();

        return response()->json($learnings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'category_id' => 'nullable|exists:categories,id',
            'parent_id' => 'nullable|integer',
            'teacher' => 'nullable|string|max:255',
            'typeOfLesson' => 'nullable|string|max:255',
            'study_from' => 'nullable|date',
            'study_to' => 'nullable|date',
        ]);

        $group2learning = Group2learning::create($validated);

        return response()->json($group2learning, 201);
    }

    public function show($id)
    {
        $learning = Group2learning::findOrFail($id);
        return response()->json($learning);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'group_id' => 'sometimes|exists:groups,id',
            'category_id' => 'nullable|exists:categories,id',
            'parent_id' => 'nullable|integer',
            'teacher' => 'nullable|string|max:255',
            'typeOfLesson' => 'nullable|string|max:255',
            'study_from' => 'nullable|date',
            'study_to' => 'nullable|date',
        ]);

        $group2learn = Group2learning::findOrFail($id);
        $group2learn->update($validated);

        return response()->json([
            'data' => $group2learn,
            'message' => 'Ресурс успешно обновлен',
        ], 200);
    }

    public function destroy($id)
    {
        $group2learn = Group2learning::findOrFail($id);
        $group2learn->delete();

        return response()->json([
            'message' => 'Ресурс успешно удален',
        ], 200);
    }
}
