@php
    $editing = isset($productCategory) && $productCategory;

    $mediaUrl = function ($path) {
        if (!$path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '//') ||
            str_starts_with($path, 'data:')
        ) {
            return $path;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    };
@endphp

<div class="category-form-layout">

    <div class="category-form-main">

        <section class="admin-panel category-panel">
            <div class="admin-panel-header">
                <div>
                    <span class="admin-panel-eyebrow">Category details</span>
                    <h3>Basic Information</h3>
                </div>

                <div class="category-panel-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>

            <div class="category-form-body">

                <div class="category-field">
                    <label for="title">
                        Category Title
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $editing ? $productCategory->title : '') }}"
                        maxlength="255"
                        required
                        placeholder="e.g. Leather Jackets"
                    >

                    @error('title')
                        <span class="category-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="category-form-grid-2">

                    <div class="category-field">
                        <label for="slug">URL Slug</label>

                        <div class="category-input-prefix">
                            <span>/category/</span>

                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                value="{{ old('slug', $editing ? $productCategory->slug : '') }}"
                                maxlength="255"
                                placeholder="leather-jackets"
                            >
                        </div>

                        <span class="category-field-help">
                            Optional. Generated from the title and made unique on the server.
                        </span>

                        @error('slug')
                            <span class="category-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="category-field">
                        <label for="parent_id">Parent Category</label>

                        <select id="parent_id" name="parent_id">
                            <option value="">No Parent Category</option>

                            @foreach($parents as $parent)
                                <option
                                    value="{{ $parent->id }}"
                                    @selected(
                                        (string) old(
                                            'parent_id',
                                            $editing ? $productCategory->parent_id : ''
                                        ) === (string) $parent->id
                                    )
                                >
                                    {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>

                        <span class="category-field-help">
                            Leave empty to create a top-level category.
                        </span>

                        @error('parent_id')
                            <span class="category-field-error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>

                <div class="category-field">
                    <label for="description">Description</label>

                    <textarea
                        id="description"
                        name="description"
                        rows="7"
                        placeholder="Describe this category for customers and search engines..."
                    >{{ old('description', $editing ? $productCategory->description : '') }}</textarea>

                    @error('description')
                        <span class="category-field-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </section>

        <section class="admin-panel category-panel">
            <div class="admin-panel-header">
                <div>
                    <span class="admin-panel-eyebrow">Category photography</span>
                    <h3>Category Image</h3>
                </div>

                <div class="category-panel-icon">
                    <i class="fa-regular fa-image"></i>
                </div>
            </div>

            <div class="category-form-body">

                <div class="category-image-layout">

                    <div
                        class="category-image-preview"
                        id="categoryImagePreview"
                    >
                        @if($editing && $productCategory->featured_image)
                            <img
                                src="{{ $mediaUrl($productCategory->featured_image) }}"
                                alt="{{ $productCategory->title }}"
                                id="categoryImagePreviewImage"
                            >
                        @else
                            <img
                                src=""
                                alt=""
                                id="categoryImagePreviewImage"
                                hidden
                            >
                        @endif

                        <div
                            class="category-image-empty"
                            id="categoryImageEmpty"
                            @if($editing && $productCategory->featured_image) hidden @endif
                        >
                            <i class="fa-regular fa-image"></i>
                            <strong>No category image</strong>
                            <span>JPG, PNG or WebP · Maximum 5 MB</span>
                        </div>
                    </div>

                    <div class="category-image-controls">

                        <label
                            for="image"
                            class="admin-button admin-button-secondary category-image-choose"
                        >
                            <i class="fa-solid fa-image"></i>
                            <span id="categoryImageChooseText">
                                {{ $editing && $productCategory->featured_image
                                    ? 'Replace Image'
                                    : 'Choose Image' }}
                            </span>
                        </label>

                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            hidden
                        >

                        <button
                            type="button"
                            class="category-image-remove"
                            id="removeCategoryImage"
                            @if(!$editing || !$productCategory->featured_image) hidden @endif
                        >
                            <i class="fa-solid fa-trash"></i>
                            Remove Image
                        </button>

                        <button
                            type="button"
                            class="category-image-undo"
                            id="undoCategoryImage"
                            hidden
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Undo
                        </button>

                        <input
                            type="hidden"
                            name="remove_image"
                            id="removeCategoryImageInput"
                            value="{{ old('remove_image', 0) }}"
                        >

                        <span class="category-field-help">
                            Uploading a new image replaces the current one. Physical old files are removed only after a successful save.
                        </span>

                        @error('image')
                            <span class="category-field-error">{{ $message }}</span>
                        @enderror

                    </div>

                </div>

            </div>
        </section>

        <section class="admin-panel category-panel">
            <div class="admin-panel-header">
                <div>
                    <span class="admin-panel-eyebrow">Search appearance</span>
                    <h3>SEO</h3>
                </div>

                <div class="category-panel-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
            </div>

            <div class="category-form-body">

                <div class="category-field">
                    <div class="category-label-row">
                        <label for="meta_title">Meta Title</label>
                        <span id="metaTitleCount">0 / 60</span>
                    </div>

                    <input
                        type="text"
                        id="meta_title"
                        name="meta_title"
                        value="{{ old('meta_title', $editing ? $productCategory->meta_title : '') }}"
                        maxlength="255"
                        placeholder="SEO title for this category"
                    >

                    @error('meta_title')
                        <span class="category-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="category-field">
                    <div class="category-label-row">
                        <label for="meta_description">Meta Description</label>
                        <span id="metaDescriptionCount">0 / 160</span>
                    </div>

                    <textarea
                        id="meta_description"
                        name="meta_description"
                        rows="4"
                        maxlength="500"
                        placeholder="Describe this category for search results..."
                    >{{ old('meta_description', $editing ? $productCategory->meta_description : '') }}</textarea>

                    @error('meta_description')
                        <span class="category-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="category-field">
                    <label for="meta_keywords">Meta Keywords</label>

                    <textarea
                        id="meta_keywords"
                        name="meta_keywords"
                        rows="3"
                        maxlength="1000"
                        placeholder="leather jackets, men's jackets, outerwear"
                    >{{ old('meta_keywords', $editing ? $productCategory->meta_keywords : '') }}</textarea>

                    @error('meta_keywords')
                        <span class="category-field-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </section>

    </div>

    <aside class="category-form-sidebar">

        <section class="admin-panel category-panel category-sticky-panel">
            <div class="admin-panel-header">
                <div>
                    <span class="admin-panel-eyebrow">Save category</span>
                    <h3>{{ $editing ? 'Update' : 'Create' }}</h3>
                </div>
            </div>

            <div class="category-form-body">

                @if($editing)
                    <div class="category-summary">
                        <div>
                            <span>Category ID</span>
                            <strong>#{{ $productCategory->id }}</strong>
                        </div>

                        <div>
                            <span>Products</span>
                            <strong>{{ number_format($productCategory->products_count ?? 0) }}</strong>
                        </div>

                        <div>
                            <span>Children</span>
                            <strong>{{ number_format($productCategory->children_count ?? 0) }}</strong>
                        </div>
                    </div>
                @else
                    <div class="category-create-note">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>
                            Create the category first, then you can assign products to it from Product Management.
                        </span>
                    </div>
                @endif

                <button
                    type="submit"
                    class="admin-button admin-button-primary category-save-button"
                    data-category-submit
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>
                        {{ $editing ? 'Update Category' : 'Create Category' }}
                    </span>
                </button>

                <a
                    href="{{ route('admin.product-categories.index') }}"
                    class="admin-button admin-button-secondary category-cancel-button"
                >
                    Cancel
                </a>

            </div>
        </section>

    </aside>

</div>

@push('page-styles')
<style>
    .category-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 290px;
        gap: 20px;
        align-items: start;
    }

    .category-form-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
        min-width: 0;
    }

    .category-form-sidebar {
        min-width: 0;
    }

    .category-panel {
        overflow: hidden;
    }

    .category-panel-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #f5f3ff;
        color: #6d5dfc;
        font-size: 14px;
    }

    .category-form-body {
        padding: 20px;
    }

    .category-form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .category-field {
        min-width: 0;
        margin-bottom: 18px;
    }

    .category-field:last-child {
        margin-bottom: 0;
    }

    .category-field > label,
    .category-label-row label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 11px;
        font-weight: 750;
    }

    .category-field .required {
        color: #dc2626;
    }

    .category-field input[type="text"],
    .category-field select,
    .category-field textarea,
    .category-input-prefix {
        width: 100%;
    }

    .category-field input[type="text"],
    .category-field select,
    .category-field textarea {
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid rgba(15, 23, 42, .11);
        border-radius: 9px;
        outline: 0;
        background: #fff;
        color: #111827;
        font: inherit;
        font-size: 11px;
        transition: .18s ease;
    }

    .category-field textarea {
        resize: vertical;
        line-height: 1.6;
    }

    .category-field input:focus,
    .category-field select:focus,
    .category-field textarea:focus {
        border-color: #7c6cff;
        box-shadow: 0 0 0 3px rgba(124, 108, 255, .10);
    }

    .category-input-prefix {
        display: flex;
        align-items: stretch;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, .11);
        border-radius: 9px;
        background: #fff;
    }

    .category-input-prefix:focus-within {
        border-color: #7c6cff;
        box-shadow: 0 0 0 3px rgba(124, 108, 255, .10);
    }

    .category-input-prefix > span {
        display: inline-flex;
        align-items: center;
        padding: 0 10px;
        border-right: 1px solid rgba(15, 23, 42, .08);
        background: #f8fafc;
        color: #94a3b8;
        font-size: 10px;
        white-space: nowrap;
    }

    .category-input-prefix input {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .category-field-help {
        display: block;
        margin-top: 6px;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.55;
    }

    .category-field-error {
        display: block;
        margin-top: 6px;
        color: #dc2626;
        font-size: 9px;
        font-weight: 650;
    }

    .category-image-layout {
        display: grid;
        grid-template-columns: 190px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .category-image-preview {
        width: 190px;
        height: 190px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 13px;
        background: #f8fafc;
    }

    .category-image-preview img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .category-image-empty {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 15px;
        color: #94a3b8;
        text-align: center;
    }

    .category-image-empty i {
        font-size: 26px;
    }

    .category-image-empty strong {
        color: #64748b;
        font-size: 10px;
    }

    .category-image-empty span {
        font-size: 8px;
    }

    .category-image-controls {
        display: flex;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 8px;
    }

    .category-image-choose {
        margin: 0 !important;
        cursor: pointer;
    }

    .category-image-remove,
    .category-image-undo {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 13px;
        border-radius: 9px;
        background: #fff;
        font: inherit;
        font-size: 10px;
        font-weight: 750;
        cursor: pointer;
    }

    .category-image-remove {
        border: 1px solid #fecaca;
        color: #dc2626;
    }

    .category-image-undo {
        border: 1px solid #cbd5e1;
        color: #475569;
    }

    .category-image-remove[hidden],
    .category-image-undo[hidden] {
        display: none !important;
    }

    .category-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .category-label-row span {
        color: #94a3b8;
        font-size: 9px;
        font-weight: 650;
    }

    .category-sticky-panel {
        position: sticky;
        top: 20px;
    }

    .category-summary {
        display: grid;
        gap: 8px;
        margin-bottom: 15px;
    }

    .category-summary > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 8px;
        background: #f8fafc;
    }

    .category-summary span {
        color: #94a3b8;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .category-summary strong {
        color: #111827;
        font-size: 11px;
    }

    .category-create-note {
        display: flex;
        gap: 8px;
        margin-bottom: 15px;
        padding: 11px;
        border-radius: 9px;
        background: #f8fafc;
        color: #64748b;
        font-size: 9px;
        line-height: 1.55;
    }

    .category-create-note i {
        margin-top: 2px;
        color: #7c6cff;
    }

    .category-save-button,
    .category-cancel-button {
        width: 100%;
        justify-content: center;
    }

    .category-cancel-button {
        margin-top: 8px;
    }

    @media (max-width: 1050px) {
        .category-form-layout {
            grid-template-columns: 1fr;
        }

        .category-sticky-panel {
            position: static;
        }
    }

    @media (max-width: 680px) {
        .category-form-grid-2,
        .category-image-layout {
            grid-template-columns: 1fr;
        }

        .category-image-preview {
            width: 100%;
            max-width: 240px;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    const title =
        document.getElementById('title');

    const slug =
        document.getElementById('slug');

    let slugEdited =
        Boolean(slug?.value);

    function slugify(value) {
        return String(value || '')
            .toLowerCase()
            .trim()
            .replace(/['’]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    slug?.addEventListener('input', function () {
        slugEdited =
            this.value.trim() !== '';
    });

    title?.addEventListener('input', function () {
        if (
            slug &&
            !slugEdited
        ) {
            slug.value =
                slugify(this.value);
        }
    });


    const imageInput =
        document.getElementById('image');

    const preview =
        document.getElementById('categoryImagePreviewImage');

    const empty =
        document.getElementById('categoryImageEmpty');

    const removeButton =
        document.getElementById('removeCategoryImage');

    const undoButton =
        document.getElementById('undoCategoryImage');

    const removeInput =
        document.getElementById('removeCategoryImageInput');

    const chooseText =
        document.getElementById('categoryImageChooseText');

    const originalImage =
        @json(
            $editing && $productCategory->featured_image
                ? $mediaUrl($productCategory->featured_image)
                : null
        );

    let objectUrl = null;

    function showEmpty(message = 'No category image') {
        if (preview) {
            preview.src = '';
            preview.hidden = true;
        }

        if (empty) {
            empty.hidden = false;

            const strong =
                empty.querySelector('strong');

            if (strong) {
                strong.textContent = message;
            }
        }
    }

    function showImage(src) {
        if (preview) {
            preview.src = src;
            preview.hidden = false;
        }

        if (empty) {
            empty.hidden = true;
        }
    }

    imageInput?.addEventListener(
        'change',
        function () {
            const file =
                this.files?.[0];

            if (!file) {
                return;
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            objectUrl =
                URL.createObjectURL(file);

            showImage(objectUrl);

            if (removeInput) {
                removeInput.value = '0';
            }

            if (chooseText) {
                chooseText.textContent =
                    'Replace Image';
            }

            if (removeButton) {
                removeButton.hidden = false;
            }

            if (undoButton) {
                undoButton.hidden = true;
            }
        }
    );

    removeButton?.addEventListener(
        'click',
        function () {
            if (imageInput) {
                imageInput.value = '';
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (removeInput) {
                removeInput.value = '1';
            }

            showEmpty('Image will be removed');

            removeButton.hidden = true;

            if (undoButton && originalImage) {
                undoButton.hidden = false;
            }

            if (chooseText) {
                chooseText.textContent =
                    'Choose Image';
            }
        }
    );

    undoButton?.addEventListener(
        'click',
        function () {
            if (!originalImage) {
                return;
            }

            if (removeInput) {
                removeInput.value = '0';
            }

            showImage(originalImage);

            undoButton.hidden = true;

            if (removeButton) {
                removeButton.hidden = false;
            }

            if (chooseText) {
                chooseText.textContent =
                    'Replace Image';
            }
        }
    );


    const metaTitle =
        document.getElementById('meta_title');

    const metaDescription =
        document.getElementById('meta_description');

    const metaTitleCount =
        document.getElementById('metaTitleCount');

    const metaDescriptionCount =
        document.getElementById('metaDescriptionCount');

    function updateSeoCounters() {
        if (
            metaTitle &&
            metaTitleCount
        ) {
            metaTitleCount.textContent =
                metaTitle.value.length +
                ' / 60';
        }

        if (
            metaDescription &&
            metaDescriptionCount
        ) {
            metaDescriptionCount.textContent =
                metaDescription.value.length +
                ' / 160';
        }
    }

    metaTitle?.addEventListener(
        'input',
        updateSeoCounters
    );

    metaDescription?.addEventListener(
        'input',
        updateSeoCounters
    );

    updateSeoCounters();


    const form =
        document.querySelector(
            'form[data-category-form]'
        );

    const submitButton =
        form?.querySelector(
            '[data-category-submit]'
        );

    form?.addEventListener(
        'submit',
        function () {
            if (!submitButton) {
                return;
            }

            submitButton.disabled = true;

            const label =
                submitButton.querySelector(
                    'span'
                );

            if (label) {
                label.textContent =
                    'Saving...';
            }
        }
    );

});
</script>
@endpush


<style>
/* Arizona Category UI enhancements */
.category-field-error{display:none!important}
.az-cat-select{position:relative;width:100%}
.az-cat-native{position:absolute!important;width:1px!important;height:1px!important;opacity:0!important;pointer-events:none!important}
.az-cat-button{display:flex;align-items:center;justify-content:space-between;width:100%;min-height:40px;padding:0 11px;border:1px solid #e0e5ed;border-radius:7px;background:#fff;color:#344054;font-size:11px;font-weight:600;text-align:left;cursor:pointer}
.az-cat-select.open .az-cat-button,.az-cat-button:focus{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10);outline:none}
.az-cat-menu{position:absolute;z-index:12500;top:calc(100% + 6px);left:0;display:none;width:100%;max-height:240px;overflow:auto;padding:5px;border:1px solid #e0e5ed;border-radius:9px;background:#fff;box-shadow:0 14px 38px rgba(15,23,42,.16)}
.az-cat-select.open .az-cat-menu{display:block}
.az-cat-option{display:flex;align-items:center;justify-content:space-between;width:100%;min-height:34px;padding:7px 9px;border:0;border-radius:6px;background:transparent;color:#344054;font-size:11px;font-weight:600;text-align:left;cursor:pointer}
.az-cat-option:hover,.az-cat-option.selected{background:#eeedff;color:#5149d8}
.az-cat-option.selected::after{content:"\2713";color:#635bff;font-weight:900}
.cat-keyword-editor{display:flex;flex-wrap:wrap;align-items:center;gap:7px;min-height:44px;padding:6px 8px;border:1px solid #e0e5ed;border-radius:7px;background:#fff}
.cat-keyword-editor:focus-within{border-color:#635bff;box-shadow:0 0 0 3px rgba(99,91,255,.10)}
.cat-keyword-tag{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border:1px solid #dddfff;border-radius:6px;background:#f3f2ff;color:#5149d8;font-size:10px;font-weight:700}
.cat-keyword-tag button{border:0;background:transparent;color:#7770e7;cursor:pointer}
.cat-keyword-input{flex:1 1 160px;min-width:140px!important;min-height:30px!important;padding:3px!important;border:0!important;box-shadow:none!important}
.cat-error-popup{position:fixed;inset:0;z-index:13000;display:flex;align-items:center;justify-content:center;padding:20px}
.cat-error-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}
.cat-error-dialog{position:relative;z-index:2;width:100%;max-width:460px;padding:30px;border-radius:14px;background:#fff;box-shadow:0 24px 80px rgba(15,23,42,.24);text-align:center}
.cat-error-dialog ul{margin:14px 0;padding:12px 16px;border:1px solid #fecaca;border-radius:9px;background:#fff7f7;color:#b42318;font-size:10px;text-align:left}
</style>

@if($errors->any())
<div class="cat-error-popup" id="catErrorPopup" role="dialog" aria-modal="true">
 <div class="cat-error-backdrop" data-cat-error-close></div>
 <div class="cat-error-dialog">
  <span class="admin-page-eyebrow">Category management</span>
  <h3>Please correct the category details</h3>
  <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  <button type="button" class="admin-button admin-button-primary" data-cat-error-close>Review Fields</button>
 </div>
</div>
@endif

<script>
'use strict';
document.addEventListener('DOMContentLoaded',function(){
 const title=document.getElementById('title'),slug=document.getElementById('slug');
 if(title&&slug){
   let manual=slug.value.trim()!=='';
   const make=v=>v.toLowerCase().trim().replace(/['’]/g,'').replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');
   slug.addEventListener('input',()=>manual=slug.value.trim()!=='');
   title.addEventListener('input',()=>{if(!manual)slug.value=make(title.value)});
 }
 const select=document.getElementById('parent_id');
 if(select&&!select.multiple){
  const w=document.createElement('div');w.className='az-cat-select';
  const b=document.createElement('button');b.type='button';b.className='az-cat-button';
  const v=document.createElement('span'),i=document.createElement('i');i.className='fa-solid fa-chevron-down';b.append(v,i);
  const m=document.createElement('div');m.className='az-cat-menu';
  select.parentNode.insertBefore(w,select);w.append(select,b,m);select.classList.add('az-cat-native');
  function render(){m.innerHTML='';Array.from(select.options).forEach(o=>{const x=document.createElement('button');x.type='button';x.className='az-cat-option'+(String(o.value)===String(select.value)?' selected':'');x.textContent=o.textContent.trim();x.disabled=o.disabled;x.onclick=()=>{select.value=o.value;select.dispatchEvent(new Event('change',{bubbles:true}));render();w.classList.remove('open');b.focus()};m.append(x)});const o=select.options[select.selectedIndex];v.textContent=o?o.textContent.trim():'Select'}
  b.onclick=()=>w.classList.toggle('open');document.addEventListener('click',e=>{if(!w.contains(e.target))w.classList.remove('open')});document.addEventListener('keydown',e=>{if(e.key==='Escape')w.classList.remove('open')});render();
 }
 const old=document.querySelector('[name="meta_keywords"]');
 if(old){
  const initial=old.value||'', hidden=document.createElement('input');hidden.type='hidden';hidden.name='meta_keywords';
  old.removeAttribute('name');old.hidden=true;
  const ed=document.createElement('div');ed.className='cat-keyword-editor';const tags=document.createElement('div');tags.style.display='contents';const inp=document.createElement('input');inp.type='text';inp.className='cat-keyword-input';inp.placeholder='Type keyword and press Enter';
  old.parentNode.insertBefore(ed,old);ed.append(tags,inp,hidden);let words=[];
  const norm=x=>String(x||'').replace(/\s+/g,' ').trim().replace(/^,+|,+$/g,'').trim();
  function add(x){x=norm(x);if(x&&!words.some(y=>y.toLowerCase()===x.toLowerCase()))words.push(x)}
  function draw(){tags.innerHTML='';words.forEach((x,n)=>{const tag=document.createElement('span');tag.className='cat-keyword-tag';const s=document.createElement('span');s.textContent=x;const rm=document.createElement('button');rm.type='button';rm.innerHTML='&times;';rm.onclick=()=>{words.splice(n,1);draw()};tag.append(s,rm);tags.append(tag)});hidden.value=words.join(', ')}
  initial.split(',').forEach(add);draw();function commit(){inp.value.split(',').forEach(add);inp.value='';draw()}inp.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===','){e.preventDefault();commit()}});inp.addEventListener('blur',commit);const f=ed.closest('form');if(f)f.addEventListener('submit',commit);
 }
 const pop=document.getElementById('catErrorPopup');if(pop){document.body.style.overflow='hidden';pop.querySelectorAll('[data-cat-error-close]').forEach(x=>x.onclick=()=>{pop.remove();document.body.style.overflow=''})}
});
</script>
