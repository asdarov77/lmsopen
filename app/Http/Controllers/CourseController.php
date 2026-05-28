<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\Aukstructure;
use App\Http\Filters\CourseFilter;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $filter = new CourseFilter($request->query());
        
        $courses = Course::filter($filter)
            ->with(['aukstructures.links', 'categories', 'aircraft'])
            ->paginate(15);

        return response()->json($courses);
    }

    public function show($id)
    {
        $course = Course::with([
            'aukstructures' => function($query) {
                $query->orderBy('type')->orderBy('id');
            },
            'aukstructures.links',
            'categories',
            'aircraft'
        ])->findOrFail($id);

        return response()->json([$course]);
    }

    public function getLink($aukstructureId)
    {
        $aukstructure = Aukstructure::with('links')->findOrFail($aukstructureId);
        
        if ($aukstructure->links->isEmpty()) {
            return response()->json(['message' => 'No links found'], 404);
        }

        $firstLink = $aukstructure->links->first();
        
        return response()->json([
            'link' => $firstLink->link,
            'aukstructure' => $aukstructure,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'aircraft_id' => 'required|exists:aircrafts,id',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
        ]);

        $course = Course::create($validated);

        if ($request->has('category_ids')) {
            $course->categories()->sync($request->category_ids);
        }

        return response()->json($course, 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        $course->update($request->only([
            'title', 'path', 'aircraft_id', 'short_description', 'long_description'
        ]));

        if ($request->has('category_ids')) {
            $course->categories()->sync($request->category_ids);
        }

        return response()->json($course);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        
        return response()->json(null, 204);
    }

    public function getFirstAuk($auk_id)
    {
        $cur_course_id = Aukstructure::find($auk_id)->course_id;
        $firstAukId = Aukstructure::where([
            ['course_id', '=', $cur_course_id],
            ['type', '=', 3],
            ['id', '>=', $auk_id]
        ])->orderBy('id')->first();

        return $firstAukId;
    }

    public function showManifest($id)
    {
        $course = Course::find($id);
        $course->categories;
        $course->aircraft;

        return response()->json($course);
    }
}
