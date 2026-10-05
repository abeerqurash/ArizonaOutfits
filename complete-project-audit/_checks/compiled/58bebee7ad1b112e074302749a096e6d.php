<?php $__env->startSection('title', 'Product Tags'); ?>
<?php $__env->startSection('page-heading', 'Product Tags'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $feedbackType = session('error') ? 'error' : (session('warning') ? 'warning' : (session('success') ? 'success' : null));
    $feedbackMessage = session('error') ?: session('warning') ?: session('success');
?>

<div class="admin-page-header tag-index-header">
    <div>
        <span class="admin-page-eyebrow">Catalog organization</span>
        <h2>Product Tags</h2>
        <p>Create and manage reusable product labels without losing visibility of where each tag is used.</p>
    </div>
    <div class="admin-page-actions">
        <a href="<?php echo e(route('admin.product-tags.create')); ?>" class="admin-button admin-button-primary">
            <i class="fa-solid fa-plus"></i>
            Create Tag
        </a>
    </div>
</div>

<div class="tag-stat-grid">
    <div class="admin-stat-card"><div class="admin-stat-icon"><i class="fa-solid fa-tags"></i></div><div><span>Total Tags</span><strong><?php echo e(number_format($totalTags)); ?></strong></div></div>
    <div class="admin-stat-card"><div class="admin-stat-icon"><i class="fa-solid fa-link"></i></div><div><span>Tags In Use</span><strong><?php echo e(number_format($usedTags)); ?></strong></div></div>
    <div class="admin-stat-card"><div class="admin-stat-icon"><i class="fa-solid fa-tag"></i></div><div><span>Unused Tags</span><strong><?php echo e(number_format($unusedTags)); ?></strong></div></div>
    <div class="admin-stat-card"><div class="admin-stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div><div><span>Assignments</span><strong><?php echo e(number_format($productAssignments)); ?></strong></div></div>
</div>

<section class="admin-panel tag-filter-panel">
    <form method="GET" action="<?php echo e(route('admin.product-tags.index')); ?>" class="tag-filter-form">
        <div class="tag-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search title or slug...">
        </div>
        <button type="submit" class="admin-button admin-button-primary"><i class="fa-solid fa-filter"></i> Filter</button>
        <?php if(request()->filled('search')): ?>
            <a href="<?php echo e(route('admin.product-tags.index')); ?>" class="admin-button admin-button-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
    </form>
</section>

<section class="admin-panel tag-list-panel">
    <div class="tag-panel-top">
        <div>
            <span class="admin-page-eyebrow">Tag directory</span>
            <h3>All Product Tags</h3>
        </div>
        <span class="tag-result-count"><?php echo e(number_format($tags->total())); ?> results</span>
    </div>

    <?php if($tags->count()): ?>
        <div class="tag-table-wrap">
            <table class="tag-table">
                <thead><tr><th>Tag</th><th>Slug</th><th>Products</th><th>Updated</th><th class="tag-actions-head">Actions</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><div class="tag-name-cell"><span class="tag-icon"><i class="fa-solid fa-tag"></i></span><div><strong><?php echo e($tag->title); ?></strong><small>#<?php echo e($tag->id); ?></small></div></div></td>
                        <td><code><?php echo e($tag->slug); ?></code></td>
                        <td><span class="tag-count-pill <?php echo e($tag->products_count > 0 ? 'is-used' : 'is-unused'); ?>"><?php echo e(number_format($tag->products_count)); ?> <?php echo e(Str::plural('product', $tag->products_count)); ?></span></td>
                        <td><?php echo e($tag->updated_at?->format('M d, Y') ?: '—'); ?></td>
                        <td>
                            <div class="tag-actions">
                                <a href="<?php echo e(route('admin.product-tags.edit', $tag)); ?>" class="tag-action-button" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <button
                                    type="button"
                                    class="tag-action-button tag-action-danger"
                                    title="<?php echo e($tag->products_count > 0 ? 'Remove this tag from products before deleting' : 'Delete'); ?>"
                                    data-delete-tag
                                    data-delete-url="<?php echo e(route('admin.product-tags.destroy', $tag)); ?>"
                                    data-tag-title="<?php echo e($tag->title); ?>"
                                    data-product-count="<?php echo e($tag->products_count); ?>"
                                ><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($tags->hasPages()): ?>
            <div class="tag-pagination"><?php echo e($tags->links()); ?></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="tag-empty"><span><i class="fa-solid fa-tags"></i></span><h3>No product tags found</h3><p><?php echo e(request()->filled('search') ? 'Try another search term.' : 'Create your first tag to start organizing products.'); ?></p></div>
    <?php endif; ?>
</section>

<div class="tag-popup" id="tagDeletePopup" hidden role="dialog" aria-modal="true">
    <div class="tag-popup-backdrop" data-delete-close></div>
    <div class="tag-popup-dialog">
        <button type="button" class="tag-popup-x" data-delete-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        <div class="tag-popup-icon tag-popup-icon-warning"><i class="fa-solid fa-trash"></i></div>
        <span class="admin-page-eyebrow">Delete product tag</span>
        <h3 id="tagDeleteTitle">Delete this tag?</h3>
        <p id="tagDeleteMessage">This action cannot be undone.</p>
        <div class="tag-popup-actions">
            <button type="button" class="admin-button admin-button-secondary" data-delete-close>Cancel</button>
            <form method="POST" id="tagDeleteForm"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit" class="admin-button tag-delete-confirm"><i class="fa-solid fa-trash"></i> Delete Tag</button></form>
        </div>
    </div>
</div>

<?php if($feedbackType): ?>
<div class="tag-popup" id="tagFeedbackPopup" role="dialog" aria-modal="true">
    <div class="tag-popup-backdrop" data-feedback-close></div>
    <div class="tag-popup-dialog">
        <button type="button" class="tag-popup-x" data-feedback-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        <div class="tag-popup-icon <?php echo e($feedbackType === 'success' ? 'tag-popup-icon-success' : 'tag-popup-icon-warning'); ?>"><i class="fa-solid <?php echo e($feedbackType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation'); ?>"></i></div>
        <span class="admin-page-eyebrow">Product tags</span>
        <h3><?php echo e($feedbackType === 'success' ? 'Completed successfully' : 'Attention required'); ?></h3>
        <p><?php echo e($feedbackMessage); ?></p>
        <div class="tag-popup-actions"><button type="button" class="admin-button admin-button-primary" data-feedback-close>OK</button></div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('page-styles'); ?>
<style>
.tag-index-header{margin-bottom:18px}.tag-index-header h2{margin:4px 0;color:#0f172a;font-size:24px;font-weight:800}.tag-index-header p{color:#7b8497;font-size:12px}
.tag-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
.tag-stat-grid .admin-stat-card{display:flex;align-items:center;gap:12px;min-height:100px;padding:16px;border:1px solid #e6eaf1;border-radius:11px;background:#fff;box-shadow:none}
.tag-stat-grid .admin-stat-icon{display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:9px;background:#eeedff;color:#635bff}
.tag-stat-grid span{display:block;color:#7b8497;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}.tag-stat-grid strong{display:block;margin-top:4px;color:#172033;font-size:20px}
.tag-filter-panel,.tag-list-panel{border:1px solid #e6eaf1!important;border-radius:11px!important;background:#fff!important;box-shadow:none!important}.tag-filter-panel{padding:14px;margin-bottom:16px}
.tag-filter-form{display:flex;align-items:center;gap:9px}.tag-search{position:relative;flex:1}.tag-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#98a2b3;font-size:10px}.tag-search input{width:100%;min-height:40px;padding:0 12px 0 33px;border:1px solid #e0e5ed;border-radius:7px;font-size:11px}.tag-search input:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10);outline:none}
.tag-list-panel{overflow:hidden}.tag-panel-top{display:flex;align-items:center;justify-content:space-between;padding:17px 18px;border-bottom:1px solid #edf0f4}.tag-panel-top h3{margin:3px 0 0;color:#172033;font-size:14px}.tag-result-count{padding:5px 8px;border-radius:999px;background:#f3f4f7;color:#667085;font-size:9px;font-weight:700}
.tag-table-wrap{overflow-x:auto}.tag-table{width:100%;border-collapse:collapse}.tag-table th{padding:10px 14px;border-bottom:1px solid #edf0f4;background:#fafbfc;color:#7b8497;font-size:8px;font-weight:800;letter-spacing:.07em;text-align:left;text-transform:uppercase}.tag-table td{padding:12px 14px;border-bottom:1px solid #f0f2f5;color:#667085;font-size:10px}.tag-table tr:last-child td{border-bottom:0}.tag-name-cell{display:flex;align-items:center;gap:10px}.tag-name-cell strong{display:block;color:#172033;font-size:11px}.tag-name-cell small{display:block;margin-top:2px;color:#98a2b3;font-size:8px}.tag-icon{display:flex;align-items:center;justify-content:center;width:31px;height:31px;border-radius:7px;background:#f3f2ff;color:#635bff}.tag-table code{padding:4px 6px;border-radius:5px;background:#f7f8fa;color:#596273;font-size:9px}
.tag-count-pill{display:inline-flex;padding:5px 8px;border-radius:999px;font-size:8px;font-weight:800}.tag-count-pill.is-used{background:#eaf8f1;color:#13875b}.tag-count-pill.is-unused{background:#f2f4f7;color:#667085}
.tag-actions-head{text-align:right!important}.tag-actions{display:flex;justify-content:flex-end;gap:6px}.tag-action-button{display:inline-flex;align-items:center;justify-content:center;width:31px;height:31px;border:1px solid #e0e5ed;border-radius:7px;background:#fff;color:#667085;cursor:pointer}.tag-action-button:hover{border-color:#c9c6ff;background:#f5f4ff;color:#635bff}.tag-action-danger:hover{border-color:#fecaca;background:#fff5f5;color:#d14343}
.tag-empty{padding:55px 20px;text-align:center}.tag-empty>span{display:flex;align-items:center;justify-content:center;width:50px;height:50px;margin:0 auto 12px;border-radius:12px;background:#f3f2ff;color:#635bff}.tag-empty h3{margin:0;color:#172033;font-size:14px}.tag-empty p{margin:6px 0 0;color:#98a2b3;font-size:10px}.tag-pagination{padding:14px 18px;border-top:1px solid #edf0f4}
.tag-popup[hidden]{display:none!important}.tag-popup{position:fixed;inset:0;z-index:13000;display:flex;align-items:center;justify-content:center;padding:20px}.tag-popup-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}.tag-popup-dialog{position:relative;z-index:2;width:100%;max-width:440px;padding:30px;border-radius:14px;background:#fff;box-shadow:0 24px 80px rgba(15,23,42,.24);text-align:center}.tag-popup-x{position:absolute;top:12px;right:12px;width:32px;height:32px;border:0;border-radius:7px;background:#f3f5f8;color:#687386;cursor:pointer}.tag-popup-icon{display:flex;align-items:center;justify-content:center;width:58px;height:58px;margin:0 auto 15px;border-radius:50%;font-size:20px}.tag-popup-icon-success{background:#eaf8f1;color:#13875b}.tag-popup-icon-warning{background:#fff4df;color:#d88716}.tag-popup-dialog h3{margin:5px 0 8px;color:#172033;font-size:18px}.tag-popup-dialog p{margin:0;color:#687386;font-size:11px;line-height:1.6}.tag-popup-actions{display:flex;justify-content:center;gap:8px;margin-top:20px}.tag-delete-confirm{border:1px solid #d14343;background:#d14343;color:#fff}
@media(max-width:850px){.tag-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.tag-stat-grid{grid-template-columns:1fr}.tag-filter-form{align-items:stretch;flex-direction:column}.tag-popup-actions{flex-direction:column}.tag-popup-actions>*{width:100%}}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('page-scripts'); ?>
<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const deletePopup = document.getElementById('tagDeletePopup');
    const deleteForm = document.getElementById('tagDeleteForm');
    const deleteTitle = document.getElementById('tagDeleteTitle');
    const deleteMessage = document.getElementById('tagDeleteMessage');

    document.querySelectorAll('[data-delete-tag]').forEach(function (button) {
        button.addEventListener('click', function () {
            const count = Number(button.dataset.productCount || 0);
            const title = button.dataset.tagTitle || 'this tag';

            deleteTitle.textContent = count > 0 ? 'Tag is currently in use' : 'Delete “' + title + '”?';
            deleteMessage.textContent = count > 0
                ? 'This tag is assigned to ' + count + ' product' + (count === 1 ? '' : 's') + '. Remove it from those products before deleting.'
                : 'This action permanently deletes the tag. It cannot be undone.';

            deleteForm.action = button.dataset.deleteUrl;
            deleteForm.style.display = count > 0 ? 'none' : '';
            deletePopup.hidden = false;
            document.body.style.overflow = 'hidden';
        });
    });

    function closeDelete() {
        if (!deletePopup) return;
        deletePopup.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-delete-close]').forEach(function (button) {
        button.addEventListener('click', closeDelete);
    });

    const feedback = document.getElementById('tagFeedbackPopup');

    if (feedback) {
        document.body.style.overflow = 'hidden';
        feedback.querySelectorAll('[data-feedback-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                feedback.remove();
                document.body.style.overflow = '';
            });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDelete();
        }
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\product-tags\index.blade.php ENDPATH**/ ?>