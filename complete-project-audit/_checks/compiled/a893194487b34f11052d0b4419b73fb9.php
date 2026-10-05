<?php if($order->activities->isNotEmpty()): ?>

    <div class="admin-order-timeline">

        <?php $__currentLoopData = $order->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php
                $activityIcon = match ($activity->type) {
                    'order_status_changed' =>
                        'fa-solid fa-box',

                    'payment_status_changed' =>
                        'fa-solid fa-credit-card',

                    'tracking_updated' =>
                        'fa-solid fa-truck',

                    'note_added' =>
                        'fa-solid fa-note-sticky',

                    'email_sent' =>
                        'fa-solid fa-envelope',

                    'invoice_generated' =>
                        'fa-solid fa-file-invoice',

                    default =>
                        'fa-solid fa-pen',
                };
            ?>

            <article class="admin-order-timeline-item">

                <div class="admin-order-timeline-marker">
                    <i class="<?php echo e($activityIcon); ?>"></i>
                </div>

                <div class="admin-order-timeline-content">

                    <div class="admin-order-timeline-heading">

                        <div>
                            <strong>
                                <?php echo e($activity->title); ?>

                            </strong>

                            <small>
                                by <?php echo e($activity->actor_name); ?>

                            </small>
                        </div>

                        <time
                            datetime="<?php echo e($activity->created_at?->toIso8601String()); ?>"
                        >
                            <?php echo e($activity->created_at?->format(
                                'M d, Y g:i A'
                            )); ?>

                        </time>

                    </div>

                    <?php if($activity->formatted_change): ?>

                        <div class="admin-order-activity-change">

                            <span>
                                <?php echo e($activity->old_value
                                    ?: 'Not set'); ?>

                            </span>

                            <i class="fa-solid fa-arrow-right"></i>

                            <strong>
                                <?php echo e($activity->new_value
                                    ?: 'Not set'); ?>

                            </strong>

                        </div>

                    <?php endif; ?>

                    <?php if($activity->description): ?>

                        <p>
                            <?php echo nl2br(
                                e($activity->description)
                            ); ?>

                        </p>

                    <?php endif; ?>

                </div>

            </article>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>

<?php else: ?>

    <div class="admin-order-empty-section">

        <span>
            <i class="fa-solid fa-clock-rotate-left"></i>
        </span>

        <strong>No activity recorded yet</strong>

        <p>
            Status updates, payment changes, tracking changes and notes will
            appear here.
        </p>

    </div>

<?php endif; ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\orders\partials\activities.blade.php ENDPATH**/ ?>