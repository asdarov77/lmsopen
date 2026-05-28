<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aukstructure;

class AukstructureController extends Controller
{
    public function index(Request $request)
    {
        $query = Aukstructure::query();

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $aukstructures = $query->get();
        return response()->json($aukstructures);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
