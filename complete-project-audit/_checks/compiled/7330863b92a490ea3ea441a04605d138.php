<?php if($order->notes->isNotEmpty()): ?>

    <div class="admin-order-notes-list">

        <?php $__currentLoopData = $order->notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <article
                class="admin-order-note-card"
                data-note-id="<?php echo e($note->id); ?>"
            >

                <div class="admin-order-note-header">

                    <div class="admin-order-note-author">

                        <span class="admin-order-note-avatar">
                            <?php echo e(strtoupper(
                                substr(
                                    $note->author_name,
                                    0,
                                    1
                                )
                            )); ?>

                        </span>

                        <div>
                            <strong>
                                <?php echo e($note->author_name); ?>

                            </strong>

                            <small>
                                <?php echo e($note->created_at?->format(
                                    'M d, Y \a\t g:i A'
                                )); ?>

                            </small>
                        </div>

                    </div>

                    <div class="admin-order-note-actions">

                        <span
                            class="admin-order-note-visibility <?php echo e($note->is_customer_visible
                                    ? 'is-visible'
                                    : 'is-internal'); ?>"
                        >
                            <i class="fa-solid <?php echo e($note->is_customer_visible
                                    ? 'fa-eye'
                                    : 'fa-lock'); ?>"></i>

                            <?php echo e($note->is_customer_visible
                                ? 'Customer visible'
                                : 'Internal'); ?>

                        </span>

                        <button
                            type="button"
                            class="admin-order-note-delete"
                            data-delete-note-url="<?php echo e(route(
                                    'admin.orders.notes.destroy',
                                    [
                                        'order' => $order,
                                        'note' => $note,
                                    ]
                                )); ?>"
                            aria-label="Delete note"
                            title="Delete note"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>

                </div>

                <div class="admin-order-note-text">
                    <?php echo nl2br(e($note->note)); ?>

                </div>

            </article>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

<?php else: ?>

    <div class="admin-order-empty-section">

        <span>
            <i class="fa-regular fa-note-sticky"></i>
        </span>

        <strong>No notes yet</strong>

        <p>
            Add an internal note or a note visible to the customer.
        </p>

    </div>

<?php endif; ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\orders\partials\notes.blade.php ENDPATH**/ ?>