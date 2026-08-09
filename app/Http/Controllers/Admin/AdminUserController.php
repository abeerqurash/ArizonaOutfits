<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends AdminController
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->value());

        $query = User::query()
            ->where('is_admin', true)
            ->with('adminRoles:id,name,slug')
            ->withCount('adminRoles');

        if ($search !== '') {
            $query->where(
                fn ($searchQuery) => $searchQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
            );
        }

        $admins = $query
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = AdminRole::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $availableUsers = User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email', 'status']);

        $stats = [
            'total' => User::query()->where('is_admin', true)->count(),
            'active' => User::query()
                ->where('is_admin', true)
                ->where('status', 'active')
                ->count(),
            'super_admins' => User::query()
                ->where('is_admin', true)
                ->where('is_super_admin', true)
                ->count(),
            'roles' => $roles->count(),
        ];

        return view(
            'admin.admin-users.index',
            compact(
                'admins',
                'roles',
                'availableUsers',
                'stats',
                'search'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['create', 'promote'])],
            'existing_user_id' => [
                'nullable',
                'required_if:mode,promote',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'name' => [
                'nullable',
                'required_if:mode,create',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'required_if:mode,create',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => [
                'nullable',
                'required_if:mode,create',
                'string',
                'min:8',
                'confirmed',
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_super_admin' => ['nullable', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('admin_roles', 'id'),
            ],
        ]);

        $isSuperAdmin = $request->boolean('is_super_admin');
        $roleIds = collect($validated['role_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (!$isSuperAdmin && $roleIds === []) {
            throw ValidationException::withMessages([
                'role_ids' =>
                    'Choose at least one role or enable Super Administrator.',
            ]);
        }

        $admin = DB::transaction(
            function () use (
                $validated,
                $isSuperAdmin,
                $roleIds
            ): User {
                if ($validated['mode'] === 'promote') {
                    $user = User::query()
                        ->lockForUpdate()
                        ->findOrFail((int) $validated['existing_user_id']);

                    if ($user->is_admin) {
                        throw ValidationException::withMessages([
                            'existing_user_id' =>
                                'This user is already an administrator.',
                        ]);
                    }
                } else {
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'] ?? null,
                        'password' => $validated['password'],
                        'status' => $validated['status'],
                        'is_admin' => true,
                        'is_super_admin' => $isSuperAdmin,
                    ]);
                }

                $user->forceFill([
                    'status' => $validated['status'],
                    'is_admin' => true,
                    'is_super_admin' => $isSuperAdmin,
                ])->save();

                $user->adminRoles()->sync($roleIds);

                return $user;
            }
        );

        return redirect()
            ->route('admin.admin-users.index')
            ->with(
                'success',
                $admin->name . ' now has administrator access.'
            );
    }

    public function edit(User $adminUser): View|RedirectResponse
    {
        if (!$adminUser->is_admin) {
            return redirect()
                ->route('admin.admin-users.index')
                ->with('error', 'That user is not an administrator.');
        }

        $adminUser->load('adminRoles:id,name,slug,description');
        $roles = AdminRole::query()->orderBy('name')->get();

        return view(
            'admin.admin-users.edit',
            compact('adminUser', 'roles')
        );
    }

    public function update(
        Request $request,
        User $adminUser
    ): RedirectResponse {
        if (!$adminUser->is_admin) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($adminUser->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_super_admin' => ['nullable', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('admin_roles', 'id'),
            ],
        ]);

        $isSuperAdmin = $request->boolean('is_super_admin');
        $roleIds = collect($validated['role_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (!$isSuperAdmin && $roleIds === []) {
            throw ValidationException::withMessages([
                'role_ids' =>
                    'Choose at least one role or enable Super Administrator.',
            ]);
        }

        if (
            (int) $adminUser->id === (int) Auth::id()
            && (
                $validated['status'] !== 'active'
                || !$isSuperAdmin
            )
        ) {
            throw ValidationException::withMessages([
                'is_super_admin' =>
                    'You cannot disable or remove Super Administrator access from your own signed-in account.',
            ]);
        }

        $this->protectLastSuperAdministrator(
            $adminUser,
            $isSuperAdmin,
            $validated['status']
        );

        DB::transaction(
            function () use (
                $validated,
                $adminUser,
                $isSuperAdmin,
                $roleIds
            ): void {
                $data = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'status' => $validated['status'],
                    'is_admin' => true,
                    'is_super_admin' => $isSuperAdmin,
                ];

                if (filled($validated['password'] ?? null)) {
                    $data['password'] = $validated['password'];
                }

                $adminUser->update($data);
                $adminUser->adminRoles()->sync($roleIds);
            }
        );

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', $adminUser->name . ' was updated.');
    }

    public function destroy(User $adminUser): RedirectResponse
    {
        if (!$adminUser->is_admin) {
            abort(404);
        }

        if ((int) $adminUser->id === (int) Auth::id()) {
            return redirect()
                ->route('admin.admin-users.index')
                ->with(
                    'error',
                    'You cannot revoke your own administrator access.'
                );
        }

        $this->protectLastSuperAdministrator(
            $adminUser,
            false,
            $adminUser->status
        );

        DB::transaction(function () use ($adminUser): void {
            $adminUser->adminRoles()->detach();
            $adminUser->forceFill([
                'is_admin' => false,
                'is_super_admin' => false,
            ])->save();
        });

        return redirect()
            ->route('admin.admin-users.index')
            ->with(
                'success',
                'Administrator access was revoked from '
                    . $adminUser->name . '.'
            );
    }

    private function protectLastSuperAdministrator(
        User $adminUser,
        bool $willBeSuperAdmin,
        string $newStatus
    ): void {
        if (
            !$adminUser->is_super_admin
            || ($willBeSuperAdmin && $newStatus === 'active')
        ) {
            return;
        }

        $otherActiveSuperAdmins = User::query()
            ->where('is_admin', true)
            ->where('is_super_admin', true)
            ->where('status', 'active')
            ->where('id', '!=', $adminUser->id)
            ->count();

        if ($otherActiveSuperAdmins === 0) {
            throw ValidationException::withMessages([
                'is_super_admin' =>
                    'At least one active Super Administrator must remain.',
            ]);
        }
    }
}
