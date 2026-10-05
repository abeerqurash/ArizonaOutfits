<div class="admin-panel">

    <div class="admin-panel-header">

        <div>
            <span class="admin-panel-eyebrow">
                Customer feedback
            </span>

            <h3>
                Latest Reviews
            </h3>
        </div>

        <a href="<?php echo e(route('admin.reviews.index')); ?>">
            View all reviews
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="admin-table-wrapper">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>

                <?php $__empty_1 = true; $__currentLoopData = $latestReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <?php
                $reviewStatus = strtolower(
                $review->status ?? 'pending'
                );

                $reviewerName =
                $review->name
                ?: $review->user?->name
                ?: 'Guest';

                $reviewProduct =
                $review->product?->title
                ?: 'Product unavailable';
                ?>

                <tr>

                    <td>
                        <strong>
                            <?php echo e($reviewerName); ?>

                        </strong>

                        <?php if($review->email): ?>
                        <small>
                            <?php echo e($review->email); ?>

                        </small>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php echo e($reviewProduct); ?>

                    </td>

                    <td>
                        <strong>
                            <?php echo e(number_format(
                                                (int) $review->rating
                                            )); ?>/5
                        </strong>

                        <small>
                            <?php for($star = 1; $star <= 5; $star++): ?>
                                <?php if($star <=(int) $review->rating): ?>
                                ★
                                <?php else: ?>
                                ☆
                                <?php endif; ?>
                                <?php endfor; ?>
                        </small>
                    </td>

                    <td>
                        <span
                            class="admin-badge admin-badge-<?php echo e($reviewStatus); ?>">
                            <?php echo e(ucfirst($reviewStatus)); ?>

                        </span>
                    </td>

                    <td>
                        <span class="admin-table-date">
                            <?php echo e($review->created_at?->format(
                                                    'M d, Y'
                                                )); ?>

                        </span>
                    </td>

                </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <tr>
                    <td colspan="5">

                        <div class="admin-empty-state">

                            <span>
                                <i class="fa-solid fa-star"></i>
                            </span>

                            <h4>
                                No reviews yet
                            </h4>

                            <p>
                                Customer reviews will appear here.
                            </p>

                        </div>

                    </td>
                </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\dashboard\latest-reviews.blade.php ENDPATH**/ ?>