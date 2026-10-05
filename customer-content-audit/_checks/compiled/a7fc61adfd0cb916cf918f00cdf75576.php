

<?php $__env->startSection('title', 'Customer Details'); ?>
<?php $__env->startSection('page-heading', 'Customer Details'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $status = $customer->status ?: 'active';

    $customIdentity = $customer->emailIdentities
        ->firstWhere('source', \App\Models\CustomerEmailIdentity::SOURCE_CUSTOM);

    $googleIdentity = $customer->emailIdentities
        ->firstWhere('source', \App\Models\CustomerEmailIdentity::SOURCE_GOOGLE);

    $facebookIdentity = $customer->emailIdentities
        ->firstWhere('source', \App\Models\CustomerEmailIdentity::SOURCE_FACEBOOK);

    $phoneVerified = (bool) $customer->phone_verified_at;
    $passwordAvailable = filled($customer->password);

    $customEmailConnected = $customIdentity?->isConnected() ?? false;
    $customEmailVerified = $customIdentity?->isEmailVerified() ?? false;
    $customEmailLogin = $customEmailConnected && (bool) $customIdentity?->is_login_enabled;

    $googleConnected = $googleIdentity?->isConnected() ?? false;
    $facebookConnected = $facebookIdentity?->isConnected() ?? false;
?>

<div class="admin-customer-page">
    <div class="admin-page-header">
        <div>
            <span class="admin-page-eyebrow">Customer account</span>
            <h2><?php echo e($customer->name ?: 'Unnamed customer'); ?></h2>
            <p><?php echo e($customer->email ?: 'No primary email on profile'); ?></p>
        </div>

        <div class="admin-page-actions">
            <a href="<?php echo e(route('admin.customers.index')); ?>" class="admin-button admin-button-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Customers
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="admin-alert admin-alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="admin-alert admin-alert-error"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="admin-alert admin-alert-error">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <div class="admin-customer-stats">
        <div class="admin-customer-stat">
            <span class="admin-customer-stat-icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </span>
            <div>
                <small>Total Orders</small>
                <strong><?php echo e(number_format($customer->orders->count())); ?></strong>
            </div>
        </div>

        <div class="admin-customer-stat">
            <span class="admin-customer-stat-icon">
                <i class="fa-solid fa-dollar-sign"></i>
            </span>
            <div>
                <small>Total Spent</small>
                <strong><?php echo e(\App\Services\CustomerSpendService::format($totalSpent)); ?></strong>
            </div>
        </div>

        <div class="admin-customer-stat">
            <span class="admin-customer-stat-icon">
                <i class="fa-solid fa-star"></i>
            </span>
            <div>
                <small>Reviews</small>
                <strong><?php echo e(number_format($customer->reviews->count())); ?></strong>
            </div>
        </div>

        <div class="admin-customer-stat">
            <span class="admin-customer-stat-icon">
                <i class="fa-solid fa-user-shield"></i>
            </span>
            <div>
                <small>Account Status</small>
                <strong><?php echo e(ucfirst($status)); ?></strong>
            </div>
        </div>
    </div>

    <div class="admin-customer-grid">
        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <span class="admin-panel-eyebrow">Profile</span>
                    <h3>Customer Information</h3>
                    <p>Name and account status can be managed here.</p>
                </div>
            </div>

            <form
                action="<?php echo e(route('admin.customers.update', $customer)); ?>"
                method="POST"
                class="admin-customer-form"
            >
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="admin-customer-field">
                    <label for="name">Customer Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="<?php echo e(old('name', $customer->name)); ?>"
                        required
                    >
                </div>

                <div class="admin-customer-field">
                    <label>Profile Email</label>
                    <div class="admin-customer-readonly">
                        <i class="fa-solid fa-envelope"></i>
                        <?php echo e($customer->email ?: 'Not available'); ?>

                    </div>
                    <small>Login email changes are handled through the customer's security flow.</small>
                </div>

                <div class="admin-customer-field">
                    <label>Phone</label>
                    <div class="admin-customer-readonly">
                        <i class="fa-solid fa-phone"></i>
                        <?php echo e($customer->phone ?: 'Not available'); ?>

                    </div>
                    <small>Phone verification and replacement are handled through account security.</small>
                </div>

                <div class="admin-customer-field">
                    <label for="status">Account Status</label>
                    <select name="status" id="status" required>
                        <?php $__currentLoopData = ['active', 'inactive', 'blocked']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($option); ?>" <?php if(old('status', $status) === $option): echo 'selected'; endif; ?>>
                                <?php echo e(ucfirst($option)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <button type="submit" class="admin-button admin-button-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>
            </form>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <span class="admin-panel-eyebrow">Authentication</span>
                    <h3>Login & Security</h3>
                    <p>Read-only overview of the customer's configured login methods.</p>
                </div>
            </div>

            <div class="admin-customer-security-list">
                <div class="admin-customer-security-row">
                    <span class="admin-customer-security-icon"><i class="fa-solid fa-mobile-screen"></i></span>
                    <div>
                        <strong>Phone</strong>
                        <small><?php echo e($customer->phone ?: 'No phone connected'); ?></small>
                    </div>
                    <span class="admin-security-pill <?php echo e($phoneVerified ? 'is-good' : 'is-muted'); ?>">
                        <?php echo e($phoneVerified ? 'Verified' : 'Not verified'); ?>

                    </span>
                </div>

                <div class="admin-customer-security-row">
                    <span class="admin-customer-security-icon"><i class="fa-solid fa-key"></i></span>
                    <div>
                        <strong>Password</strong>
                        <small>ArizonaOutfits password authentication</small>
                    </div>
                    <span class="admin-security-pill <?php echo e($passwordAvailable ? 'is-good' : 'is-muted'); ?>">
                        <?php echo e($passwordAvailable ? 'Available' : 'Not set'); ?>

                    </span>
                </div>

                <div class="admin-customer-security-row">
                    <span class="admin-customer-security-icon"><i class="fa-solid fa-envelope-circle-check"></i></span>
                    <div>
                        <strong>Custom Email</strong>
                        <small><?php echo e($customIdentity?->email ?: 'No custom email identity'); ?></small>
                    </div>
                    <span class="admin-security-pill <?php echo e($customEmailLogin && $customEmailVerified ? 'is-good' : 'is-muted'); ?>">
                        <?php if($customEmailLogin && $customEmailVerified): ?>
                            Login enabled
                        <?php elseif($customEmailConnected): ?>
                            Connected
                        <?php else: ?>
                            Not connected
                        <?php endif; ?>
                    </span>
                </div>

                <div class="admin-customer-security-row">
                    <span class="admin-customer-security-icon"><i class="fa-brands fa-google"></i></span>
                    <div>
                        <strong>Google</strong>
                        <small><?php echo e($googleIdentity?->email ?: 'No Google identity'); ?></small>
                    </div>
                    <span class="admin-security-pill <?php echo e($googleConnected ? 'is-good' : 'is-muted'); ?>">
                        <?php echo e($googleConnected ? 'Connected' : 'Not connected'); ?>

                    </span>
                </div>

                <div class="admin-customer-security-row">
                    <span class="admin-customer-security-icon"><i class="fa-brands fa-facebook"></i></span>
                    <div>
                        <strong>Facebook</strong>
                        <small><?php echo e($facebookIdentity?->email ?: 'No Facebook identity'); ?></small>
                    </div>
                    <span class="admin-security-pill <?php echo e($facebookConnected ? 'is-good' : 'is-muted'); ?>">
                        <?php echo e($facebookConnected ? 'Connected' : 'Not connected'); ?>

                    </span>
                </div>
            </div>
        </section>
    </div>

    <section class="admin-panel admin-customer-orders-panel">
        <div class="admin-panel-header">
            <div>
                <span class="admin-panel-eyebrow">Purchase history</span>
                <h3>Customer Orders</h3>
                <p><?php echo e(number_format($customer->orders->count())); ?> order(s) associated with this account.</p>
            </div>
        </div>

        <div class="admin-customer-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $customer->orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><strong><?php echo e($order->order_number ?: '#' . $order->id); ?><?php echo e($order->trashed() ? ' (Archived)' : ''); ?></strong></td>
                            <td class="admin-customer-money"><?php echo e($order->currency ?: config('shipping.currency', 'USD')); ?> <?php echo e(number_format((float) $order->total, 2)); ?></td>
                            <td><?php echo e(ucfirst($order->payment_status ?: 'pending')); ?></td>
                            <td><?php echo e(ucfirst($order->order_status ?: 'pending')); ?></td>
                            <td><?php echo e($order->created_at?->format('M d, Y') ?: '—'); ?></td>
                            <td>
                                <a
                                    href="<?php echo e(route('admin.orders.show', $order)); ?>"
                                    class="admin-customer-view-button"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="admin-customer-empty">
                                This customer has no orders.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="admin-panel admin-customer-danger-zone">
        <div class="admin-panel-header">
            <div>
                <span class="admin-panel-eyebrow">Danger zone</span>
                <h3>Delete Customer</h3>
                <p>
                    Customers with existing orders cannot be deleted. Block the account instead when order history must be retained.
                </p>
            </div>
        </div>

        <div class="admin-customer-danger-body">
            <button
                type="button"
                id="customerDeleteButton"
                class="admin-customer-danger-button"
                <?php if($customer->orders->isNotEmpty()): echo 'disabled'; endif; ?>
            >
                <i class="fa-solid fa-trash"></i>
                Delete Customer
            </button>
        </div>
    </section>
</div>

<div id="customerDeleteModal" class="admin-customer-modal" hidden>
    <div class="admin-customer-modal-backdrop" data-close-customer-modal></div>

    <div class="admin-customer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="customerDeleteTitle">
        <span class="admin-customer-modal-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </span>

        <h3 id="customerDeleteTitle">Delete customer?</h3>
        <p>
            This permanently deletes <strong><?php echo e($customer->name ?: 'this customer'); ?></strong>.
            This action cannot be undone.
        </p>

        <div class="admin-customer-modal-actions">
            <button type="button" class="admin-button admin-button-secondary" data-close-customer-modal>
                Cancel
            </button>

            <form action="<?php echo e(route('admin.customers.destroy', $customer)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>

                <button type="submit" class="admin-customer-confirm-delete">
                    <i class="fa-solid fa-trash"></i>
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.admin-customer-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
.admin-customer-stat { display: flex; align-items: center; gap: 14px; padding: 18px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
.admin-customer-stat-icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; flex: 0 0 42px; border-radius: 10px; background: #eef2ff; color: #635bff; }
.admin-customer-stat small, .admin-customer-stat strong { display: block; }
.admin-customer-stat small { margin-bottom: 4px; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.admin-customer-stat strong { color: #0f172a; font-size: 20px; }
.admin-customer-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 20px; margin-bottom: 20px; }
.admin-customer-form { display: flex; flex-direction: column; gap: 17px; padding: 22px 24px 24px; border-top: 1px solid #e2e8f0; }
.admin-customer-field { display: flex; flex-direction: column; gap: 7px; }
.admin-customer-field label { color: #334155; font-size: 13px; font-weight: 700; }
.admin-customer-field input, .admin-customer-field select { width: 100%; min-height: 44px; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; color: #0f172a; font: inherit; }
.admin-customer-field small { color: #64748b; font-size: 12px; line-height: 1.5; }
.admin-customer-readonly { display: flex; align-items: center; gap: 9px; min-height: 44px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 9px; background: #f8fafc; color: #475569; }
.admin-customer-readonly i { color: #94a3b8; }
.admin-customer-security-list { border-top: 1px solid #e2e8f0; }
.admin-customer-security-row { display: grid; grid-template-columns: 38px minmax(0, 1fr) auto; align-items: center; gap: 12px; padding: 15px 20px; border-bottom: 1px solid #f1f5f9; }
.admin-customer-security-row:last-child { border-bottom: 0; }
.admin-customer-security-icon { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 9px; background: #f1f5f9; color: #475569; }
.admin-customer-security-row strong, .admin-customer-security-row small { display: block; }
.admin-customer-security-row small { margin-top: 3px; color: #64748b; overflow-wrap: anywhere; }
.admin-security-pill { display: inline-flex; padding: 5px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap; }
.admin-security-pill.is-good { background: #dcfce7; color: #166534; }
.admin-security-pill.is-muted { background: #f1f5f9; color: #64748b; }
.admin-customer-orders-panel { margin-bottom: 20px; }
.admin-customer-table-wrapper { overflow-x: auto; border-top: 1px solid #e2e8f0; }
.admin-customer-money { white-space: nowrap; font-weight: 600; }
.admin-customer-view-button { display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; }
.admin-customer-empty { padding: 36px 20px !important; color: #64748b; text-align: center; }
.admin-customer-danger-zone { border-color: #fecaca; }
.admin-customer-danger-body { padding: 20px 24px 24px; border-top: 1px solid #fee2e2; }
.admin-customer-danger-button, .admin-customer-confirm-delete { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px; padding: 9px 14px; border: 1px solid #dc2626; border-radius: 9px; background: #dc2626; color: #fff; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; }
.admin-customer-danger-button:disabled { border-color: #cbd5e1; background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
.admin-customer-modal[hidden] { display: none; }
.admin-customer-modal { position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.admin-customer-modal-backdrop { position: absolute; inset: 0; background: rgba(15, 23, 42, .62); }
.admin-customer-modal-dialog { position: relative; z-index: 1; width: min(440px, 100%); padding: 28px; border-radius: 14px; background: #fff; box-shadow: 0 24px 70px rgba(15, 23, 42, .3); text-align: center; }
.admin-customer-modal-icon { display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; margin-bottom: 14px; border-radius: 50%; background: #fee2e2; color: #dc2626; font-size: 21px; }
.admin-customer-modal-dialog h3 { margin: 0 0 8px; color: #0f172a; }
.admin-customer-modal-dialog p { margin: 0; color: #64748b; line-height: 1.6; }
.admin-customer-modal-actions { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 22px; }
@media (max-width: 1050px) { .admin-customer-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .admin-customer-grid { grid-template-columns: 1fr; } }
@media (max-width: 600px) { .admin-customer-stats { grid-template-columns: 1fr; } .admin-customer-security-row { grid-template-columns: 38px minmax(0, 1fr); } .admin-security-pill { grid-column: 2; justify-self: start; } .admin-customer-modal-actions { align-items: stretch; flex-direction: column; } .admin-customer-modal-actions form, .admin-customer-modal-actions button { width: 100%; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('customerDeleteModal');
    const deleteButton = document.getElementById('customerDeleteButton');

    function openModal() {
        if (!modal || !deleteButton || deleteButton.disabled) {
            return;
        }

        modal.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.style.overflow = '';
    }

    deleteButton?.addEventListener('click', openModal);

    modal?.querySelectorAll('[data-close-customer-modal]').forEach(function (element) {
        element.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/customers/show.blade.php ENDPATH**/ ?>