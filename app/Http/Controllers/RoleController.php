<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
        ]);
        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'description' => 'nullable|string',
        ]);
        $role->update($validated);
        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(null, 204);
    }

    public function syncPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $request->validate([
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);
        $role->permissions()->sync($request->permission_ids);
        return response()->json($role->load('permissions'));
    }
}
