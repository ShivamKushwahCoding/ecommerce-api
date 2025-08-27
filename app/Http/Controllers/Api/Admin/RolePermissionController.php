<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    // List permissions of a role
    public function index(Role $role)
    {
        return response()->json($role->permissions);
    }

    // Assign permissions to a role
    public function store(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->syncWithoutDetaching($validated['permissions']);

        return response()->json([
            'message'     => 'Permissions assigned',
            'permissions' => $role->permissions,
        ]);
    }

    // Remove a permission from role
    public function destroy(Role $role, Permission $permission)
    {
        $role->permissions()->detach($permission->id);

        return response()->json(['message' => 'Permission removed']);
    }
}
