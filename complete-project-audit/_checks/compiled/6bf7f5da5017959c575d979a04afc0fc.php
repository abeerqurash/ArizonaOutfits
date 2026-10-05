<?php
    $editing = isset($category);
    $imageUrl = $editing ? $category->image_url : null;
    $selectedParent = old('parent_id', $editing ? $category->parent_id : '');
?>

<style>
.az-cat-editor{display:flex;flex-direction:column;gap:16px}.az-cat-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.az-eyebrow{font-size:10px;font-weight:800;letter-spacing:.13em;color:#635bff;text-transform:uppercase}.az-cat-head h1{margin:3px 0 4px;color:#101828;font-size:24px}.az-cat-head p{margin:0;color:#667085;font-size:12px}.az-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:7px 12px;border:1px solid #dfe3ea;border-radius:10px;background:#fff;color:#344054;text-decoration:none;font-size:11px;font-weight:800;cursor:pointer}.az-btn:hover{background:#f9fafb}.az-btn-primary{background:#635bff;border-color:#635bff;color:#fff}.az-btn-primary:hover{background:#554de8}.az-cat-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(290px,.75fr);gap:14px;align-items:start}.az-stack{display:flex;flex-direction:column;gap:12px}.az-panel{background:#fff;border:1px solid #e4e7ec;border-radius:12px;padding:15px}.az-panel-head{margin-bottom:14px}.az-panel h2{margin:0 0 3px;color:#101828;font-size:13px}.az-panel-desc{margin:0;color:#98a2b3;font-size:10px;line-height:1.5}.az-field{margin-bottom:13px}.az-field:last-child{margin-bottom:0}.az-label{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:5px;color:#344054;font-size:10px;font-weight:800}.az-required{color:#d92d20}.az-control{width:100%;box-sizing:border-box;border:1px solid #dfe3ea;border-radius:9px;background:#fff;color:#344054;font-size:12px;outline:none}.az-input{height:38px;padding:0 11px}.az-textarea{min-height:100px;padding:10px 11px;resize:vertical;line-height:1.55}.az-control:focus{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.az-help{display:block;margin-top:4px;color:#98a2b3;font-size:9px;line-height:1.45}.az-error{display:block;margin-top:4px;color:#b42318;font-size:10px;font-weight:700}.az-counter{color:#98a2b3;font-size:9px}.az-slug{display:flex;align-items:center;border:1px solid #dfe3ea;border-radius:9px;overflow:hidden}.az-slug:focus-within{border-color:#8c86ff;box-shadow:0 0 0 3px rgba(99,91,255,.08)}.az-slug-prefix{height:36px;display:flex;align-items:center;padding:0 9px;border-right:1px solid #e4e7ec;background:#f9fafb;color:#98a2b3;font-size:10px}.az-slug input{height:36px;flex:1;min-width:0;padding:0 10px;border:0;outline:0;color:#344054;font-size:12px}.az-select-wrap{position:relative}.az-select-wrap select{appearance:none;padding-right:34px}.az-select-wrap:after{content:"\f078";font-family:"Font Awesome 6 Free";font-weight:900;position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:#98a2b3;font-size:9px}.az-upload{border:1px dashed #cfd4dc;border-radius:10px;background:#fafbfc;overflow:hidden}.az-upload-preview{height:185px;display:grid;place-items:center;color:#98a2b3}.az-upload-preview img{width:100%;height:100%;object-fit:cover}.az-upload-empty{text-align:center;font-size:10px}.az-upload-empty i{display:block;margin-bottom:7px;font-size:24px}.az-upload-actions{display:flex;align-items:center;gap:7px;padding:9px;border-top:1px solid #eaecf0;background:#fff}.az-file-native{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important}.az-upload-name{min-width:0;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#667085;font-size:9px}.az-remove{display:flex;align-items:center;gap:6px;margin-top:8px;color:#b42318;font-size:9px;font-weight:800}.az-hierarchy-note{display:flex;gap:9px;padding:10px;border:1px solid #e9e7ff;border-radius:9px;background:#f8f7ff;color:#5148d8;font-size:9px;line-height:1.5}.az-hierarchy-note i{margin-top:1px}.az-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:14px}@media(max-width:850px){.az-cat-grid{grid-template-columns:1fr}.az-cat-head{flex-direction:column}}@media(max-width:520px){.az-actions{flex-direction:column-reverse}.az-actions .az-btn{width:100%}}
</style>

<div class="az-cat-editor">
    <header class="az-cat-head">
        <div>
            <div class="az-eyebrow">Content Management / Blog Categories</div>
            <h1><?php echo e($editing ? 'Edit Category' : 'Create Category'); ?></h1>
            <p><?php echo e($editing ? 'Update hierarchy, category identity, image and search metadata.' : 'Create a parent or child category for the Arizona Outfits blog.'); ?></p>
        </div>
        <a href="<?php echo e(route('admin.categories.index')); ?>" class="az-btn"><i class="fa-solid fa-arrow-left"></i> Back to Categories</a>
    </header>

    <form action="<?php echo e($editing ? route('admin.categories.update', $category) : route('admin.categories.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php if($editing): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="az-cat-grid">
            <div class="az-stack">
                <section class="az-panel">
                    <div class="az-panel-head">
                        <h2>Category Information</h2>
                        <p class="az-panel-desc">Define the category name, URL and place in the blog hierarchy.</p>
                    </div>

                    <div class="az-field">
                        <label class="az-label" for="title"><span>Category Name <span class="az-required">*</span></span></label>
                        <input class="az-control az-input" id="title" type="text" name="title" value="<?php echo e(old('title', $editing ? $category->title : '')); ?>" required data-title>
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="az-field">
                        <label class="az-label" for="slug"><span>URL Slug</span></label>
                        <div class="az-slug">
                            <span class="az-slug-prefix">/blog/category/</span>
                            <input id="slug" type="text" name="slug" value="<?php echo e(old('slug', $editing ? $category->slug : '')); ?>" data-slug>
                        </div>
                        <span class="az-help">Generated from the category name until you manually edit it. The backend guarantees a safe unique slug.</span>
                        <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="az-field">
                        <label class="az-label" for="parent_id"><span>Parent Category</span></label>
                        <div class="az-select-wrap">
                            <select class="az-control az-input" id="parent_id" name="parent_id">
                                <option value="">No parent — Root category</option>
                                <?php $__currentLoopData = $parentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option['id']); ?>" <?php if((string)$selectedParent === (string)$option['id']): echo 'selected'; endif; ?>>
                                        <?php echo e(str_repeat('— ', $option['depth'])); ?><?php echo e($option['title']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <span class="az-help">Use a parent to build structures such as Buying Guides → Leather Jackets → Men's Leather Jackets.</span>
                        <?php $__errorArgs = ['parent_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="az-hierarchy-note">
                        <i class="fa-solid fa-diagram-project"></i>
                        <span><?php echo e($editing ? 'This category and all of its descendants are automatically excluded from its parent choices, preventing hierarchy loops.' : 'Root categories sit at the top level. Child categories can be nested to create a structured content taxonomy.'); ?></span>
                    </div>

                    <div class="az-field" style="margin-top:13px">
                        <label class="az-label" for="expert"><span>Category Description</span></label>
                        <textarea class="az-control az-textarea" id="expert" name="expert"><?php echo e(old('expert', $editing ? $category->expert : '')); ?></textarea>
                        <span class="az-help">Editorial description for this blog category.</span>
                        <?php $__errorArgs = ['expert'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </section>

                <section class="az-panel">
                    <div class="az-panel-head">
                        <h2>Search Metadata</h2>
                        <p class="az-panel-desc">Optional category-level metadata for the later public SEO integration.</p>
                    </div>
                    <div class="az-field">
                        <label class="az-label" for="meta_title"><span>Meta Title</span><span class="az-counter" data-count-for="meta_title"></span></label>
                        <input class="az-control az-input" id="meta_title" type="text" name="meta_title" maxlength="255" value="<?php echo e(old('meta_title', $editing ? $category->meta_title : '')); ?>" data-count>
                        <?php $__errorArgs = ['meta_title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="az-field">
                        <label class="az-label" for="meta_description"><span>Meta Description</span><span class="az-counter" data-count-for="meta_description"></span></label>
                        <textarea class="az-control az-textarea" id="meta_description" name="meta_description" data-count><?php echo e(old('meta_description', $editing ? $category->meta_description : '')); ?></textarea>
                        <?php $__errorArgs = ['meta_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </section>
            </div>

            <aside class="az-stack">
                <section class="az-panel">
                    <div class="az-panel-head">
                        <h2>Category Image</h2>
                        <p class="az-panel-desc">JPG, PNG or WebP up to 5 MB.</p>
                    </div>
                    <div class="az-upload">
                        <div class="az-upload-preview" data-image-preview>
                            <?php if($imageUrl): ?>
                                <img src="<?php echo e($imageUrl); ?>" alt="<?php echo e($editing ? $category->title : 'Category image preview'); ?>">
                            <?php else: ?>
                                <div class="az-upload-empty"><i class="fa-regular fa-image"></i>No category image selected</div>
                            <?php endif; ?>
                        </div>
                        <div class="az-upload-actions">
                            <input class="az-file-native" id="category_image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-image-input>
                            <label class="az-btn" for="category_image"><i class="fa-solid fa-arrow-up-from-bracket"></i> Choose Image</label>
                            <span class="az-upload-name" data-file-name>No new file selected</span>
                        </div>
                    </div>
                    <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="az-error"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    <?php if($editing && $category->image): ?>
                        <label class="az-remove"><input type="checkbox" name="remove_image" value="1"> Remove current category image</label>
                    <?php endif; ?>
                </section>

                <?php if($editing): ?>
                    <section class="az-panel">
                        <div class="az-panel-head">
                            <h2>Hierarchy Summary</h2>
                            <p class="az-panel-desc">Current structural relationships.</p>
                        </div>
                        <div style="display:grid;gap:9px;font-size:10px;color:#667085">
                            <div><strong style="color:#344054">Parent:</strong> <?php echo e($category->parent?->title ?: 'Root category'); ?></div>
                            <div><strong style="color:#344054">Direct children:</strong> <?php echo e($category->children->count()); ?></div>
                            <div><strong style="color:#344054">Slug:</strong> /<?php echo e($category->slug); ?></div>
                        </div>
                    </section>
                <?php endif; ?>
            </aside>
        </div>

        <div class="az-actions">
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="az-btn">Cancel</a>
            <button type="submit" class="az-btn az-btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?php echo e($editing ? 'Save Changes' : 'Create Category'); ?></button>
        </div>
    </form>
</div>

<script>
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    const title = document.querySelector('[data-title]');
    const slug = document.querySelector('[data-slug]');
    const imageInput = document.querySelector('[data-image-input]');
    const imagePreview = document.querySelector('[data-image-preview]');
    const fileName = document.querySelector('[data-file-name]');
    let slugManual = <?php echo e($editing ? 'true' : 'false'); ?>;

    const slugify = value => value.toString().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

    slug?.addEventListener('input', () => { slugManual = true; });
    title?.addEventListener('input', () => { if (!slugManual && slug) slug.value = slugify(title.value); });

    imageInput?.addEventListener('change', function () {
        const file = imageInput.files && imageInput.files[0];
        if (!file) return;
        if (fileName) fileName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = event => {
            imagePreview.innerHTML = '';
            const img = document.createElement('img');
            img.src = event.target.result;
            img.alt = 'Category image preview';
            imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });

    document.querySelectorAll('[data-count]').forEach(field => {
        const target = document.querySelector('[data-count-for="' + field.id + '"]');
        if (!target) return;
        const update = () => {
            const max = field.getAttribute('maxlength');
            target.textContent = field.value.length + (max ? ' / ' + max : '') + ' characters';
        };
        field.addEventListener('input', update);
        update();
    });
});
</script>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\admin\categories\_form.blade.php ENDPATH**/ ?>