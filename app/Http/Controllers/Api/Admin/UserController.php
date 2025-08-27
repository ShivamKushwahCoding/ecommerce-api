<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('role')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|unique:users',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json($user->load('role'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('role'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|string|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:6',
            'role_id'   => 'sometimes|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load('role'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    // Assign/Change role of a user
    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->role_id = $validated['role_id'];
        $user->save();

        return response()->json([
            'message' => 'Role updated successfully',
            'user'    => $user->load('role'),
        ]);
    }

// Activate or deactivate user
    public function setActive(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user->is_active = $validated['is_active'];
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'User activated' : 'User deactivated',
            'user'    => $user->load('role'),
        ]);
    }

}
