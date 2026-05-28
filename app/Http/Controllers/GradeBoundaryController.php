<?php

namespace App\Http\Controllers;

use App\Models\GradeBoundary;
use Illuminate\Http\Request;

class GradeBoundaryController extends Controller
{
    public function index()
    {
        $boundaries = GradeBoundary::orderBy('boundary')->get();
        return response()->json($boundaries);
    }

    public function store(Request $request)
    {
        if ($request->has('boundaries')) {
            $request->validate([
                'boundaries' => 'required|array',
                'boundaries.*.boundary' => 'required|integer|min:0|max:100',
                'boundaries.*.grade' => 'required|string|max:10',
            ]);

            foreach ($request->boundaries as $item) {
                GradeBoundary::updateOrCreate(
                    ['boundary' => $item['boundary']],
                    ['grade' => $item['grade']]
                );
            }

            return response()->json(['message' => 'saved'], 200);
        }

        $validated = $request->validate([
            'boundary' => 'required|integer|min:0|max:100',
            'grade' => 'required|string|max:10',
        ]);

        $boundary = GradeBoundary::updateOrCreate(
            ['boundary' => $request->boundary],
            ['grade' => $request->grade]
        );

        return response()->json($boundary, 200);
    }
}
