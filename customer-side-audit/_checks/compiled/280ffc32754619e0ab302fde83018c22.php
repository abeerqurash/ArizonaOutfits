<?php $__env->startSection('title', 'Manage Posts'); ?>
<?php $__env->startSection('page-heading', 'Posts'); ?>

<?php $__env->startSection('content'); ?>
<style>
.az-posts{display:flex;flex-direction:column;gap:16px}.az-posts-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.az-eyebrow{font-size:10px;font-weight:800;letter-spacing:.13em;color:#635bff;text-transform:uppercase}.az-posts-head h1{margin:3px 0 4px;color:#101828;font-size:24px}.az-posts-head p{margin:0;color:#667085;font-size:12px}.az-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 12px;border:1px solid #dfe3ea;border-radius:10px;background:#fff;color:#344054;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}.az-btn-primary{background:#635bff;border-color:#635bff;color:#fff}.az-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.az-stat{background:#fff;border:1px solid #e4e7ec;border-radius:11px;padding:12px}.az-stat-top{display:flex;align-items:center;justify-content:space-between;gap:8px}.az-stat-label{color:#667085;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}.az-stat-icon{width:28px;height:28px;border-radius:8px;background:#f0efff;color:#635bff;display:grid;place-items:center;font-size:11px}.az-stat-value{margin-top:8px;color:#101828;font-size:20px;font-weight:800}.az-panel{background:#fff;border:1px solid #e4e7ec;border-radius:12px;overflow:hidden}.az-toolbar{padding:10px;border-bottom:1px solid #eef0f3}.az-filter-form{display:grid;grid-template-columns:minmax(220px,1fr) 160px 200px auto;gap:8px}.az-search{position:relative}.az-search i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#98a2b3;font-size:11px}.az-control{width:100%;height:36px;box-sizing:border-box;border:1px solid #dfe3ea;border-radius:9px;background:#fff;color:#344054;padding:0 10px;font-size:11px;outline:none}.az-search .az-control{padding-left:31px}.az-control:focus{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.az-table-wrap{overflow-x:auto}.az-table{width:100%;border-collapse:collapse}.az-table th{padding:9px 11px;background:#f9fafb;border-bottom:1px solid #e4e7ec;color:#667085;font-size:9px;text-align:left;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}.az-table td{padding:10px 11px;border-bottom:1px solid #eef0f3;color:#344054;font-size:11px;vertical-align:middle}.az-table tbody tr:last-child td{border-bottom:0}.az-post-cell{display:flex;align-items:center;gap:9px;min-width:260px}.az-thumb{width:48px;height:48px;flex:0 0 48px;border-radius:8px;overflow:hidden;background:#f2f4f7;display:grid;place-items:center;color:#98a2b3}.az-thumb img{width:100%;height:100%;object-fit:cover}.az-post-cell strong{display:block;color:#101828;font-size:11px}.az-post-cell small{display:block;margin-top:2px;color:#98a2b3;font-size:9px}.az-status{display:inline-flex;align-items:center;gap:5px;padding:4px 7px;border-radius:999px;font-size:9px;font-weight:800;text-transform:capitalize}.az-status:before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}.az-status-draft{background:#f2f4f7;color:#667085}.az-status-published{background:#ecfdf3;color:#027a48}.az-status-scheduled{background:#eff8ff;color:#175cd3}.az-status-archived{background:#fff6ed;color:#b54708}.az-category{display:inline-flex;margin:2px 3px 2px 0;padding:4px 7px;border-radius:999px;background:#f0efff;color:#5148d8;font-size:9px;font-weight:700}.az-category-primary{box-shadow:inset 0 0 0 1px #a9a4ff}.az-muted{color:#98a2b3;font-size:9px}.az-actions{display:flex;gap:5px}.az-icon-btn{width:30px;height:30px;border:1px solid #dfe3ea;border-radius:8px;background:#fff;display:grid;place-items:center;color:#475467;text-decoration:none;cursor:pointer}.az-icon-btn-danger{color:#c01048}.az-empty{text-align:center;padding:45px 20px;color:#667085}.az-pagination{padding:10px;border-top:1px solid #eef0f3}.az-modal-bg{position:fixed;inset:0;z-index:1300;background:rgba(15,23,42,.52);display:grid;place-items:center;padding:20px}.az-modal-bg[hidden]{display:none}.az-modal{width:min(420px,100%);background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:20px;box-shadow:0 24px 70px rgba(15,23,42,.22)}.az-modal-icon{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:#fff1f3;color:#c01048;margin-bottom:11px}.az-modal h2{margin:4px 0 7px;color:#101828;font-size:18px}.az-modal p{margin:0;color:#667085;font-size:11px;line-height:1.55}.az-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:17px}.az-delete{background:#d92d20;border-color:#d92d20;color:#fff}@media(max-width:1050px){.az-stats{grid-template-columns:repeat(3,1fr)}.az-filter-form{grid-template-columns:1fr 1fr}}@media(max-width:650px){.az-posts-head{flex-direction:column}.az-stats{grid-template-columns:1fr 1fr}.az-filter-form{grid-template-columns:1fr}.az-modal-actions{flex-direction:column-reverse}.az-modal-actions>*{width:100%}}
</style>

<div class="az-posts">
    <header class="az-posts-head">
        <div><div class="az-eyebrow">Content Management / Blog</div><h1>Posts</h1><p>Manage article publishing, hierarchy, authorship and search visibility.</p></div>
        <a href="<?php echo e(route('admin.posts.create')); ?>" class="az-btn az-btn-primary"><i class="fa-solid fa-plus"></i> Create Post</a>
    </header>

    <div class="az-stats">
        <?php $__currentLoopData = [
            ['Total', $stats['total'], 'fa-newspaper'],
            ['Draft', $stats['draft'], 'fa-file-pen'],
            ['Published', $stats['published'], 'fa-circle-check'],
            ['Scheduled', $stats['scheduled'], 'fa-clock'],
            ['Archived', $stats['archived'], 'fa-box-archive'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label,$value,$icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="az-stat"><div class="az-stat-top"><span class="az-stat-label"><?php echo e($label); ?></span><span class="az-stat-icon"><i class="fa-solid <?php echo e($icon); ?>"></i></span></div><div class="az-stat-value"><?php echo e(number_format($value)); ?></div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <section class="az-panel">
        <div class="az-toolbar">
            <form method="GET" action="<?php echo e(route('admin.posts.index')); ?>" class="az-filter-form">
                <div class="az-search"><i class="fa-solid fa-magnifying-glass"></i><input class="az-control" type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search title, slug, excerpt..."></div>
                <select class="az-control" name="status">
                    <option value="">All statuses</option>
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(request('status') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select class="az-control" name="category_id">
                    <option value="">All categories</option>
                    <?php
                        $renderFilterOptions = function ($nodes, $depth = 0) use (&$renderFilterOptions) {
                            foreach ($nodes as $node) {
                                echo '<option value="' . (int)$node->id . '" ' . ((string)request('category_id') === (string)$node->id ? 'selected' : '') . '>' . e(str_repeat('— ', $depth) . $node->title) . '</option>';
                                if ($node->relationLoaded('childrenRecursive')) $renderFilterOptions($node->childrenRecursive, $depth + 1);
                            }
                        };
                        $renderFilterOptions($categories);
                    ?>
                </select>
                <div style="display:flex;gap:6px">
                    <button class="az-btn az-btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
                    <?php if(request()->hasAny(['search','status','category_id'])): ?><a class="az-btn" href="<?php echo e(route('admin.posts.index')); ?>">Reset</a><?php endif; ?>
                </div>
            </form>
        </div>

        <div class="az-table-wrap">
            <table class="az-table">
                <thead><tr><th>Article</th><th>Status</th><th>Categories</th><th>Author</th><th>Publishing</th><th>Actions</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <div class="az-post-cell">
                                <div class="az-thumb"><?php if($post->feature_image_url): ?><img src="<?php echo e($post->feature_image_url); ?>" alt="<?php echo e($post->feature_image_alt ?: $post->title); ?>" loading="lazy"><?php else: ?><i class="fa-regular fa-image"></i><?php endif; ?></div>
                                <div><strong><?php echo e($post->title); ?></strong><small>/blogs/<?php echo e($post->slug); ?></small></div>
                            </div>
                        </td>
                        <td><span class="az-status az-status-<?php echo e($post->status); ?>"><?php echo e($statuses[$post->status] ?? ucfirst($post->status)); ?></span></td>
                        <td>
                            <?php $__empty_2 = true; $__currentLoopData = $post->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <span class="az-category <?php echo e((int)$post->primary_category_id === (int)$category->id ? 'az-category-primary' : ''); ?>"><?php echo e($category->title); ?><?php echo e((int)$post->primary_category_id === (int)$category->id ? ' ★' : ''); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><span class="az-muted">Uncategorized</span><?php endif; ?>
                        </td>
                        <td><?php echo e($post->author?->name ?: '—'); ?></td>
                        <td>
                            <?php if($post->status === 'scheduled' && $post->scheduled_at): ?>
                                <span class="az-muted">Scheduled<br><?php echo e($post->scheduled_at->format('M j, Y g:i A')); ?></span>
                            <?php elseif($post->published_at): ?>
                                <span class="az-muted">Published<br><?php echo e($post->published_at->format('M j, Y g:i A')); ?></span>
                            <?php else: ?>
                                <span class="az-muted">Updated<br><?php echo e($post->updated_at?->format('M j, Y')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><div class="az-actions">
                            <a class="az-icon-btn" href="<?php echo e(route('admin.posts.edit', $post)); ?>" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>
                            <button type="button" class="az-icon-btn az-icon-btn-danger" title="Delete" data-delete-post data-delete-url="<?php echo e(route('admin.posts.destroy', $post)); ?>" data-post-title="<?php echo e($post->title); ?>"><i class="fa-regular fa-trash-can"></i></button>
                        </div></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6"><div class="az-empty"><i class="fa-regular fa-newspaper" style="font-size:22px;margin-bottom:8px"></i><br>No posts match the current filters.</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($posts->hasPages()): ?><div class="az-pagination"><?php echo e($posts->links()); ?></div><?php endif; ?>
    </section>
</div>

<div class="az-modal-bg" data-delete-modal hidden>
    <div class="az-modal" role="dialog" aria-modal="true" aria-labelledby="az-delete-title">
        <div class="az-modal-icon"><i class="fa-regular fa-trash-can"></i></div>
        <div class="az-eyebrow">Permanent Action</div>
        <h2 id="az-delete-title">Delete this post?</h2>
        <p><strong data-delete-name>Post</strong> will be permanently deleted. This action cannot be undone.</p>
        <div class="az-modal-actions">
            <button type="button" class="az-btn" data-delete-cancel>Cancel</button>
            <form method="POST" data-delete-form><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button type="submit" class="az-btn az-delete"><i class="fa-regular fa-trash-can"></i> Delete Permanently</button></form>
        </div>
    </div>
</div>

<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.querySelector('[data-delete-modal]');
    const form = document.querySelector('[data-delete-form]');
    const name = document.querySelector('[data-delete-name]');
    document.querySelectorAll('[data-delete-post]').forEach(button => button.addEventListener('click', function () {
        form.action = button.dataset.deleteUrl || '';
        name.textContent = button.dataset.postTitle || 'Post';
        modal.hidden = false;
    }));
    document.querySelector('[data-delete-cancel]')?.addEventListener('click', () => modal.hidden = true);
    modal?.addEventListener('click', event => { if (event.target === modal) modal.hidden = true; });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && modal && !modal.hidden) modal.hidden = true; });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ArizonaOutfits\resources\views/admin/posts/index.blade.php ENDPATH**/ ?>