<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
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

        $query = Admin::query()
            ->with('adminRoles:id,name,slug')
            ->withCount('adminRoles');

        if ($search !== '') {
            $query->where(
                fn ($q) => $q
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
            ->withCount('admins')
            ->orderBy('name')
            ->get();

        // Existing Blade currently reads users_count.
        $roles->each(function (AdminRole $role): void {
            $role->setAttribute('users_count', $role->admins_count);
        });

        /*
         * Compatibility with the current "promote existing user" UI.
         * Promotion now COPIES identity into admins and never modifies the
         * customer record. Only customers with an email + password can be
         * promoted through the existing form.
         */
        $availableUsers = User::query()
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->whereNotIn(
                DB::raw('LOWER(email)'),
                Admin::query()
                    ->selectRaw('LOWER(email)')
                    ->whereNotNull('email')
            )
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email', 'status']);

        $stats = [
            'total' => Admin::query()->count(),
            'active' => Admin::query()->where('status', 'active')->count(),
            'super_admins' => Admin::query()
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
                Rule::unique('admins', 'email'),
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
        $roleIds = $this->roleIds($validated, $isSuperAdmin);

        $admin = DB::transaction(function () use (
            $validated,
            $isSuperAdmin,
            $roleIds
        ): Admin {
            if ($validated['mode'] === 'promote') {
                $user = User::query()
                    ->lockForUpdate()
                    ->findOrFail((int) $validated['existing_user_id']);

                if (blank($user->email) || blank($user->password)) {
                    throw ValidationException::withMessages([
                        'existing_user_id' =>
                            'This customer does not have both an email and password and cannot be copied into administrator authentication.',
                    ]);
                }

                if (
                    Admin::query()
                        ->whereRaw('LOWER(email) = ?', [mb_strtolower($user->email)])
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'existing_user_id' =>
                            'An administrator with this email already exists.',
                    ]);
                }

                $admin = new Admin();
                $admin->forceFill([
                    'name' => $user->name,
                    'email' => mb_strtolower($user->email),
                    'phone' => $user->phone,
                    'email_verified_at' => $user->email_verified_at,
                    // Preserve the existing hash exactly. Do not re-hash it.
                    'password' => $user->getRawOriginal('password'),
                    'status' => $validated['status'],
                    'is_super_admin' => $isSuperAdmin,
                    'legacy_user_id' => $user->id,
                ]);
                $admin->saveQuietly();
            } else {
                $admin = Admin::create([
                    'name' => $validated['name'],
                    'email' => mb_strtolower($validated['email']),
                    'phone' => $validated['phone'] ?? null,
                    'password' => $validated['password'],
                    'status' => $validated['status'],
                    'is_super_admin' => $isSuperAdmin,
                    'legacy_user_id' => null,
                ]);
            }

            $admin->adminRoles()->sync($roleIds);

            return $admin;
        });

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', $admin->name . ' now has administrator access.');
    }

    public function edit(Admin $adminUser): View
    {
        $adminUser->load('adminRoles:id,name,slug,description');
        $roles = AdminRole::query()->orderBy('name')->get();

        return view(
            'admin.admin-users.edit',
            compact('adminUser', 'roles')
        );
    }

    public function update(
        Request $request,
        Admin $adminUser
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($adminUser->id),
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
        $roleIds = $this->roleIds($validated, $isSuperAdmin);
        $signedInAdminId = Auth::guard('admin')->id();

        if (
            (int) $adminUser->id === (int) $signedInAdminId
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

        DB::transaction(function () use (
            $validated,
            $adminUser,
            $isSuperAdmin,
            $roleIds
        ): void {
            $data = [
                'name' => $validated['name'],
                'email' => mb_strtolower($validated['email']),
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'is_super_admin' => $isSuperAdmin,
            ];

            if (filled($validated['password'] ?? null)) {
                $data['password'] = $validated['password'];
            }

            $adminUser->update($data);
            $adminUser->adminRoles()->sync($roleIds);
            $adminUser->flushAdminPermissionCache();
        });

        return redirect()
            ->route('admin.admin-users.index')
            ->with('success', $adminUser->name . ' was updated.');
    }

    public function destroy(Admin $adminUser): RedirectResponse
    {
        if ((int) $adminUser->id === (int) Auth::guard('admin')->id()) {
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

        $name = $adminUser->name;

        DB::transaction(function () use ($adminUser): void {
            $adminUser->adminRoles()->detach();

            /*
             * Administrator authentication is now independent from customers.
             * Deleting this Admin record does not delete or modify a User row.
             */
            $adminUser->delete();
        });

        return redirect()
            ->route('admin.admin-users.index')
            ->with(
                'success',
                'Administrator access was revoked from ' . $name . '.'
            );
    }

    private function roleIds(array $validated, bool $isSuperAdmin): array
    {
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

        return $roleIds;
    }

    private function protectLastSuperAdministrator(
        Admin $adminUser,
        bool $willBeSuperAdmin,
        string $newStatus
    ): void {
        if (
            !$adminUser->is_super_admin
            || ($willBeSuperAdmin && $newStatus === 'active')
        ) {
            return;
        }

        $otherActiveSuperAdmins = Admin::query()
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
