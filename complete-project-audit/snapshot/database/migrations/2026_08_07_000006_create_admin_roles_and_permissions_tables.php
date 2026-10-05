<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'is_super_admin')) {
                $table->boolean('is_super_admin')
                    ->default(false)
                    ->after('is_admin');
            }
        });

        if (!Schema::hasTable('admin_roles')) {
            Schema::create('admin_roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 120)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('admin_permissions')) {
            Schema::create(
                'admin_permissions',
                function (Blueprint $table): void {
                    $table->id();
                    $table->string('name', 120);
                    $table->string('slug', 150)->unique();
                    $table->string('group_name', 100);
                    $table->text('description')->nullable();
                    $table->timestamps();

                    $table->index(['group_name', 'name']);
                }
            );
        }

        if (!Schema::hasTable('admin_permission_role')) {
            Schema::create(
                'admin_permission_role',
                function (Blueprint $table): void {
                    $table->foreignId('admin_role_id')
                        ->constrained('admin_roles')
                        ->cascadeOnDelete();
                    $table->foreignId('admin_permission_id')
                        ->constrained('admin_permissions')
                        ->cascadeOnDelete();
                    $table->primary([
                        'admin_role_id',
                        'admin_permission_id',
                    ]);
                }
            );
        }

        if (!Schema::hasTable('admin_role_user')) {
            Schema::create(
                'admin_role_user',
                function (Blueprint $table): void {
                    $table->foreignId('admin_role_id')
                        ->constrained('admin_roles')
                        ->cascadeOnDelete();
                    $table->foreignId('user_id')
                        ->constrained('users')
                        ->cascadeOnDelete();
                    $table->timestamps();
                    $table->primary(['admin_role_id', 'user_id']);
                }
            );
        }

        $now = now();

        $permissions = [
            ['Dashboard', 'dashboard.view', 'Dashboard'],
            ['Manage Orders', 'orders.manage', 'Orders'],
            ['Manage Products', 'products.manage', 'Catalog'],
            ['Manage Coupons', 'coupons.manage', 'Catalog'],
            ['Manage Inventory', 'inventory.manage', 'Inventory'],
            ['Manage Purchase Orders', 'purchase-orders.manage', 'Purchasing'],
            ['Manage Suppliers', 'suppliers.manage', 'Purchasing'],
            ['Manage Customers', 'customers.manage', 'Customers'],
            ['Manage Reviews', 'reviews.manage', 'Customers'],
            ['Manage Content', 'content.manage', 'Content'],
            ['Manage Store Settings', 'settings.manage', 'Configuration'],
            ['Manage Admin Team', 'admin-users.manage', 'Administration'],
            ['View Audit Logs', 'audit-logs.view', 'Administration'],
            ['Manage Notifications', 'notifications.manage', 'Administration'],
            ['Manage Backups', 'backups.manage', 'Administration'],
        ];

        foreach ($permissions as [$name, $slug, $group]) {
            DB::table('admin_permissions')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'group_name' => $group,
                    'description' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $roles = [
            'store-manager' => [
                'name' => 'Store Manager',
                'description' => 'Manages daily store operations without administrator access.',
                'permissions' => [
                    'dashboard.view',
                    'orders.manage',
                    'products.manage',
                    'coupons.manage',
                    'inventory.manage',
                    'purchase-orders.manage',
                    'suppliers.manage',
                    'customers.manage',
                    'reviews.manage',
                    'content.manage',
                    'settings.manage',
                ],
            ],
            'inventory-manager' => [
                'name' => 'Inventory Manager',
                'description' => 'Manages inventory, purchase orders, and suppliers.',
                'permissions' => [
                    'dashboard.view',
                    'products.manage',
                    'inventory.manage',
                    'purchase-orders.manage',
                    'suppliers.manage',
                ],
            ],
            'content-editor' => [
                'name' => 'Content Editor',
                'description' => 'Manages products, reviews, and website content.',
                'permissions' => [
                    'dashboard.view',
                    'products.manage',
                    'reviews.manage',
                    'content.manage',
                ],
            ],
            'customer-support' => [
                'name' => 'Customer Support',
                'description' => 'Manages orders, customers, and reviews.',
                'permissions' => [
                    'dashboard.view',
                    'orders.manage',
                    'customers.manage',
                    'reviews.manage',
                ],
            ],
        ];

        foreach ($roles as $slug => $roleData) {
            DB::table('admin_roles')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $roleId = DB::table('admin_roles')
                ->where('slug', $slug)
                ->value('id');

            $permissionIds = DB::table('admin_permissions')
                ->whereIn('slug', $roleData['permissions'])
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('admin_permission_role')->updateOrInsert([
                    'admin_role_id' => $roleId,
                    'admin_permission_id' => $permissionId,
                ]);
            }
        }

        /* Preserve unrestricted access for every existing administrator. */
        DB::table('users')
            ->where('is_admin', true)
            ->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_user');
        Schema::dropIfExists('admin_permission_role');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
        });
    }
};
