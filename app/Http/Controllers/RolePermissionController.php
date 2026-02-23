<?php

namespace App\Http\Controllers;

use App\Http\Requests\RolePermissionRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()->orderBy('name')->get(['id', 'name']);

        return view('security.roles.index', compact('roles', 'permissions'));
    }

    public function update(RolePermissionRequest $request, Role $role)
    {
        $payload = $request->validated();
        $role->syncPermissions($payload['permissions'] ?? []);

        return redirect()->route('security.roles.index')->with('status', "Permisos actualizados para el rol {$role->name}.");
    }
}
