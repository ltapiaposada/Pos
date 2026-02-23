<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserManagementRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['name', 'email', 'branch', 'role'];
        $sort = (string) $request->get('sort', 'name');
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }
        $dir = strtolower((string) $request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = User::query()
            ->with(['branch', 'roles'])
            ->select('users.*');

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($role = $request->get('role')) {
            $query->whereHas('roles', fn ($builder) => $builder->where('name', $role));
        }

        if ($sort === 'name') {
            $query->orderBy('users.name', $dir);
        } elseif ($sort === 'email') {
            $query->orderBy('users.email', $dir);
        } elseif ($sort === 'branch') {
            $query->leftJoin('branches', 'branches.id', '=', 'users.branch_id')
                ->orderBy(DB::raw('COALESCE(branches.name, \'\')'), $dir)
                ->orderBy('users.name');
        } else {
            $roleNameSubquery = DB::table('roles')
                ->select('roles.name')
                ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->orderBy('roles.name')
                ->limit(1);

            $query->orderBy($roleNameSubquery, $dir)->orderBy('users.name');
        }

        $users = $query->paginate(20)->withQueryString();
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        return view('security.users.index', compact('users', 'branches', 'roles', 'sort', 'dir'));
    }

    public function create()
    {
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $permissions = Permission::query()->orderBy('name')->get(['id', 'name']);

        return view('security.users.create', compact('branches', 'roles', 'permissions'));
    }

    public function store(UserManagementRequest $request)
    {
        $payload = $request->validated();

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'branch_id' => $payload['branch_id'] ?? null,
            'password' => $payload['password'],
        ]);

        $user->syncRoles($payload['roles'] ?? []);
        $user->syncPermissions($payload['permissions'] ?? []);

        return redirect()->route('security.users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'permissions']);
        $branches = Branch::query()->orderBy('name')->get(['id', 'name']);
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $permissions = Permission::query()->orderBy('name')->get(['id', 'name']);

        return view('security.users.edit', compact('user', 'branches', 'roles', 'permissions'));
    }

    public function update(UserManagementRequest $request, User $user)
    {
        $payload = $request->validated();

        $data = [
            'name' => $payload['name'],
            'email' => $payload['email'],
            'branch_id' => $payload['branch_id'] ?? null,
        ];

        if (! empty($payload['password'])) {
            $data['password'] = $payload['password'];
        }

        $user->update($data);
        $user->syncRoles($payload['roles'] ?? []);
        $user->syncPermissions($payload['permissions'] ?? []);

        return redirect()->route('security.users.index')->with('status', 'Usuario actualizado.');
    }
}
