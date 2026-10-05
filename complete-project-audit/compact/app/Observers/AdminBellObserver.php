<?php
namespace App\Observers;
use App\Models\{AdminAuditLog, AdminBackup, InventoryAlert, InventoryHistory, Order, Review, User};
use App\Services\AdminBellService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class AdminBellObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof AdminAuditLog) { $this->audit($model); return; }
        if ($model instanceof Order) {
            $this->send($model, 'created', 'orders.manage', 'New order', 'Order '.$model->order_number.' received ('.$model->payment_method.').', 'admin.orders.index');
        } elseif ($model instanceof Review) {
            $this->send($model, 'created', 'reviews.manage', 'New product review', 'Review #'.$model->id.' is '.$model->status.'.', 'admin.reviews.index');
        } elseif ($model instanceof User && !$model->is_admin && !$model->is_super_admin) {
            $this->send($model, 'created', 'customers.manage', 'New customer account', 'Customer account #'.$model->id.' was created.', 'admin.customers.index');
        } elseif ($model instanceof InventoryAlert && $model->status === 'active') {
            $this->stockAlert($model, 'created');
        } elseif ($model instanceof InventoryHistory) {
            $this->send($model, 'created', 'inventory.manage', 'Inventory movement recorded', 'Movement #'.$model->id.' ('.$model->movement_type.') recorded. Open inventory history for details.', 'admin.inventory-history.index');
        } elseif ($model instanceof AdminBackup) {
            $this->backup($model, 'created');
        }
    }
    public function updated(Model $model): void
    {
        $event = 'updated:'.Str::uuid();
        if ($model instanceof Order && $model->wasChanged(['payment_status', 'order_status', 'tracking_number'])) {
            $payment = $model->wasChanged('payment_status');
            $this->send($model, $event, 'orders.manage', $payment ? 'Order payment updated' : 'Order status updated',
                'Order '.$model->order_number.': payment '.$model->payment_status.', status '.$model->order_status.'.',
                $payment ? 'admin.payment-verifications.index' : 'admin.orders.index',
                $model->payment_status === 'failed' ? 'danger' : ($model->payment_status === 'paid' ? 'success' : 'info'));
        } elseif ($model instanceof Review && $model->wasChanged('status')) {
            $this->send($model, $event, 'reviews.manage', 'Review moderation updated', 'Review #'.$model->id.' is now '.$model->status.'.', 'admin.reviews.index');
        } elseif ($model instanceof User && !$model->is_admin && !$model->is_super_admin && $model->wasChanged(['status', 'name', 'email', 'phone'])) {
            $this->send($model, $event, 'customers.manage', 'Customer account updated', 'Customer #'.$model->id.' details changed; status '.$model->status.'.', 'admin.customers.index');
        } elseif ($model instanceof InventoryAlert && $model->wasChanged(['status', 'alert_type'])) {
            if ($model->status === 'active') $this->stockAlert($model, $event);
            else $this->send($model, $event, 'inventory.manage', 'Inventory alert resolved', 'Inventory alert #'.$model->id.' is '.$model->status.'.', 'admin.inventory-alerts.index', 'success');
        } elseif ($model instanceof AdminBackup && $model->wasChanged('status')) {
            $this->backup($model, $event);
        }
    }
    public function deleted(Model $model): void
    {
        if ($model instanceof Order) $this->send($model, 'archived:'.Str::uuid(), 'orders.manage', 'Order archived', 'Order '.$model->order_number.' was archived.', 'admin.orders.archived');
        elseif ($model instanceof Review) $this->send($model, 'deleted', 'reviews.manage', 'Review deleted', 'Review #'.$model->id.' was deleted.', 'admin.reviews.index');
        elseif ($model instanceof User && !$model->is_admin && !$model->is_super_admin) $this->send($model, 'deleted', 'customers.manage', 'Customer account deleted', 'Customer #'.$model->id.' was deleted.', 'admin.customers.index');
    }
    public function restored(Model $model): void
    {
        if ($model instanceof Order) $this->send($model, 'restored:'.Str::uuid(), 'orders.manage', 'Order restored', 'Order '.$model->order_number.' was restored.', 'admin.orders.index');
    }
    private function stockAlert(InventoryAlert $alert, string $event): void
    {
        $this->send($alert, $event, 'inventory.manage', $alert->alert_type === 'out_of_stock' ? 'Product out of stock' : 'Low stock warning',
            'Product #'.$alert->product_id.($alert->product_variant_id ? ', variant #'.$alert->product_variant_id : '').': '.$alert->stock_level.' units; threshold '.$alert->threshold.'.', 'admin.inventory-alerts.index', $alert->stock_level <= 0 ? 'danger' : 'warning');
    }
    private function backup(AdminBackup $backup, string $event): void
    {
        if (!in_array($backup->status, ['completed', 'failed'], true)) return;
        $this->send($backup, $event, 'backups.manage', 'Backup '.$backup->status,
            'Backup #'.$backup->id.' '.$backup->status.'. Open Backups for details.', 'admin.backups.index', $backup->status === 'failed' ? 'danger' : 'success');
    }
    private function send(Model $model, string $event, string $permission, string $title, string $message, string $route, string $level = 'info'): void
    {
        app(AdminBellService::class)->publish($model->getTable().':'.$model->getKey().':'.$event, $permission, $title, $message, $route, $level, $model->getConnectionName());
    }
    private function audit(AdminAuditLog $log): void
    {
        if ($log->outcome !== 'success' || !$log->admin_id || !str_starts_with((string) $log->route_name, 'admin.')) return;
        $action = substr($log->route_name, 6);
        $module = explode('.', $action)[0];
        // These modules already emit a specific model event, or manage notifications themselves.
        if (in_array($module, ['orders','payment-verifications','customers','reviews','backups','notifications','audit-logs'], true)) return;
        if ($module === 'products' && str_contains($action, '.inventory.')) return;
        $menus = [
            'products' => ['products.manage', 'Products', 'admin.products.index'],
            'product-categories' => ['products.manage', 'Product categories', 'admin.product-categories.index'],
            'product-tags' => ['products.manage', 'Product tags', 'admin.product-tags.index'],
            'coupons' => ['coupons.manage', 'Coupons', 'admin.coupons.index'],
            'posts' => ['content.manage', 'Blog posts', 'admin.posts.index'],
            'categories' => ['content.manage', 'Blog categories', 'admin.categories.index'],
            'settings' => ['settings.manage', 'Store Settings', 'admin.settings.edit'],
            'pages' => ['content.manage', 'CMS pages', 'admin.pages.index'],
            'navigation-menus' => ['content.manage', 'Menu Builder', 'admin.navigation-menus.index'],
            'admin-users' => ['admin-users.manage', 'Admin Team', 'admin.admin-users.index'],
            'admin-roles' => ['admin-users.manage', 'Roles & Permissions', 'admin.admin-roles.index'],
            'email-templates' => ['settings.manage', 'Email templates', 'admin.email-templates.index'],
            'purchase-orders' => ['purchase-orders.manage', 'Purchase orders', 'admin.purchase-orders.index'],
            'suppliers' => ['suppliers.manage', 'Suppliers', 'admin.suppliers.index'],
        ];
        if (!isset($menus[$module])) return;
        [$permission, $label, $route] = $menus[$module];
        $operation = trim(str_replace(['.', '_', '-'], ' ', substr($action, strlen($module))));
        $this->send($log, 'created', $permission, $label.' updated', $label.': '.($operation ?: 'change').' completed.', $route, 'success');
    }
}