<?php
namespace App\Services;
use App\Models\Admin;
use App\Notifications\AdminSystemNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;
class AdminBellService
{
    public function publish(string $key, string $permission, string $title, string $message, string $route, string $level = 'info', ?string $connection = null): void
    {
        try {
            $db = DB::connection($connection);
            $send = function () use ($db, $key, $permission, $title, $message, $route, $level): void {
                try {
                    if (!Schema::connection($db->getName())->hasTable('notifications')) return;
                    $url = app('router')->getRoutes()->getByName($route) ? route($route, [], false) : null;
                    $notification = new AdminSystemNotification($title, $message, $level, $url, 'Open menu');
                    Admin::on($db->getName())->where('status', 'active')->each(function (Admin $admin) use ($db, $key, $permission, $notification): void {
                        if (!$admin->hasAdminPermission($permission) || !$admin->hasAdminPermission('notifications.manage')) return;
                        // The primary key makes retries/concurrent publication idempotent per recipient.
                        $hash = hash('sha256', 'admin-bell:'.$key.':'.$admin->getKey());
                        $id = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-'.substr($hash, 12, 4).'-'.substr($hash, 16, 4).'-'.substr($hash, 20, 12);
                        $db->table('notifications')->insertOrIgnore([
                            'id' => $id, 'type' => AdminSystemNotification::class,
                            'notifiable_type' => $admin->getMorphClass(), 'notifiable_id' => $admin->getKey(),
                            'data' => json_encode($notification->toArray($admin), JSON_THROW_ON_ERROR),
                            'read_at' => null, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    });
                } catch (Throwable $exception) {
                    report($exception);
                }
            };
            if ($db->transactionLevel() > 0) $db->afterCommit($send);
            else $send();
        } catch (Throwable $exception) {
            // A bell failure must never reject a successful checkout or stock movement.
            report($exception);
        }
    }
}