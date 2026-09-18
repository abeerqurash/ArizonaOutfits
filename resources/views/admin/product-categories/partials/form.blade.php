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
