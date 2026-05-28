<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Group2learning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'Администратор') {
            $groups = Group::orderBy('id')->get();
        } else {
            $groups = Group::orderBy('id')
                ->where('id', Auth::user()->group_id)
                ->get();
        }

        foreach ($groups as $group) {
            $group->group2learnings;
        }

        return response()->json($groups);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'groupname' => 'required|string|max:255',
            'groupdescription' => 'nullable|string',
        ]);

        $group = Group::create($validated);

        return response()->json($group, 201);
    }

    public function show($id)
    {
        $group = Group::findOrFail($id);
        return response()->json($group);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'groupname' => 'required|string|max:255',
            'groupdescription' => 'nullable|string',
        ]);

        $group = Group::findOrFail($id);
        $group->update($validated);

        return response()->json($group);
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();

        return response()->json(null, 204);
    }
}
