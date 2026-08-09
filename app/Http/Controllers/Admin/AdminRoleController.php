<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminRoleController extends AdminController
{
    public function index(): View
    {
        $roles = AdminRole::query()
            ->with('permissions:id,name,slug,group_name')
            ->withCount('users')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $permissions = AdminPermission::query()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view(
            'admin.admin-roles.index',
            compact('roles', 'permissions')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $role = DB::transaction(
            function () use ($validated): AdminRole {
                $role = AdminRole::create([
                    'name' => $validated['name'],
                    'slug' => $this->uniqueSlug($validated['name']),
                    'description' => $validated['description'] ?? null,
                    'is_system' => false,
                ]);

                $role->permissions()->sync($validated['permission_ids']);

                return $role;
            }
        );

        return redirect()
            ->route('admin.admin-roles.index')
            ->with('success', $role->name . ' was created.');
    }

    public function update(
        Request $request,
        AdminRole $adminRole
    ): RedirectResponse {
        $validated = $this->validatedData($request, $adminRole);

        DB::transaction(
            function () use ($validated, $adminRole): void {
                $adminRole->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                ]);

                $adminRole->permissions()->sync(
                    $validated['permission_ids']
                );
            }
        );

        return redirect()
            ->route('admin.admin-roles.index')
            ->with('success', $adminRole->name . ' was updated.');
    }

    public function destroy(AdminRole $adminRole): RedirectResponse
    {
        if ($adminRole->is_system) {
            return redirect()
                ->route('admin.admin-roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        if ($adminRole->users()->exists()) {
            return redirect()
                ->route('admin.admin-roles.index')
                ->with(
                    'error',
                    'Remove all administrators from this role before deleting it.'
                );
        }

        $name = $adminRole->name;
        $adminRole->delete();

        return redirect()
            ->route('admin.admin-roles.index')
            ->with('success', $name . ' was deleted.');
    }

    private function validatedData(
        Request $request,
        ?AdminRole $role = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('admin_roles', 'name')->ignore($role?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('admin_permissions', 'id'),
            ],
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'admin-role';
        $slug = $base;
        $counter = 2;

        while (AdminRole::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter += 1;
        }

        return $slug;
    }
}
