@php
$editing = isset($product) && $product;

// Defensive normalization so this partial works with both create/edit wrappers.
$productOptions = $productOptions ?? $options ?? collect();
$categories = $categories ?? collect();
$tags = $tags ?? collect();

$selectedCategories = collect(
old(
'categories',
$editing
? $product->categories->pluck('id')->all()
: []
)
)->map(fn ($id) => (int) $id)->all();

$selectedTags = collect(
old(
'tags',
$editing
? $product->tags->pluck('id')->all()
: []
)
)->map(fn ($id) => (int) $id)->all();

$selectedOptionIds = collect(
old(
'product_options',
$editing
? $product->options->pluck('id')->all()
: []
)
)->map(fn ($id) => (int) $id)->all();

$selectedValueIds = collect(
old(
'product_option_values',
$editing
? $product->optionValues->pluck('id')->all()
: []
)
)->map(fn ($id) => (int) $id)->all();

/*
|--------------------------------------------------------------------------
| Existing / previously submitted variants
|--------------------------------------------------------------------------
*/

$oldVariants = old('variants');

if (is_array($oldVariants)) {
    $initialVariants = collect($oldVariants)
        ->map(function ($variant) {
            return [
                'id' => $variant['id'] ?? null,
                'sku' => $variant['sku'] ?? '',
                'regular_price' => $variant['regular_price'] ?? '',
                'sale_price' => $variant['sale_price'] ?? '',
                'stock' => $variant['stock'] ?? 0,
                'reorder_point' => $variant['reorder_point'] ?? '',
                'reorder_quantity' => $variant['reorder_quantity'] ?? '',
                'image' => $variant['old_image'] ?? null,
                'remove_image' => (bool) ($variant['remove_image'] ?? false),
                'options' => is_array($variant['options'] ?? null)
                    ? $variant['options']
                    : [],
            ];
        })
        ->values()
        ->all();
} elseif ($editing) {
    $initialVariants = $product->variants
        ->map(function ($variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'regular_price' => $variant->regular_price,
                'sale_price' => $variant->sale_price,
                'stock' => $variant->stock,
                'reorder_point' => $variant->reorder_point,
                'reorder_quantity' => $variant->reorder_quantity,
                'image' => $variant->image,
                'remove_image' => false,
                'options' => is_array($variant->options)
                    ? $variant->options
                    : [],
            ];
        })
        ->values()
        ->all();
} else {
    $initialVariants = [];
}


/*
|--------------------------------------------------------------------------
| Image helper
|--------------------------------------------------------------------------
*/

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

$path = ltrim(
str_replace('\\', '/', $path),
'/'
);

if (str_starts_with($path, 'public/')) {
$path = substr($path, 7);
}

if (str_starts_with($path, 'storage/')) {
return asset($path);
}

return asset('storage/' . $path);
};
@endphp


{{-- ============================================================
     MAIN PRODUCT FORM GRID
============================================================ --}}

<div class="product-form-layout">

    {{-- ========================================================
         LEFT COLUMN
    ========================================================= --}}

    <div class="product-form-main">


        {{-- ====================================================
             BASIC INFORMATION
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Product details
                    </span>

                    <h3>Basic Information</h3>
                </div>

                <div class="product-panel-icon">
                    <i class="fa-solid fa-box"></i>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-field">

                    <label for="title">
                        Product Title
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title', $editing ? $product->title : '') }}"
                        maxlength="255"
                        required
                        placeholder="e.g. Men's Black Leather Jacket">

                    @error('title')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror

                </div>


                <div class="product-form-grid-2">

                    <div class="product-field">

                        <label for="slug">
                            URL Slug
                        </label>

                        <div class="product-input-prefix">

                            <span>/products/</span>

                            <input
                                type="text"
                                id="slug"
                                name="slug"
                                value="{{ old('slug', $editing ? $product->slug : '') }}"
                                maxlength="255"
                                placeholder="mens-black-leather-jacket">

                        </div>

                        <span class="product-field-help">
                            Optional. Generated automatically from the title and made unique on the server.
                        </span>

                        @error('slug')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>


                    <div class="product-field">

                        <label for="sku">
                            Product SKU
                        </label>

                        <input
                            type="text"
                            id="sku"
                            name="sku"
                            value="{{ old('sku', $editing ? $product->sku : '') }}"
                            maxlength="100"
                            placeholder="AO-JACKET-001">

                        <span class="product-field-help">
                            Leave blank if this product is managed entirely through variant SKUs.
                        </span>

                        @error('sku')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>

                </div>


                <div class="product-field">

                    <label for="short_description">
                        Short Description
                    </label>

                    <textarea
                        id="short_description"
                        name="short_description"
                        rows="3"
                        placeholder="A short description used in product summaries...">{{ old('short_description', $editing ? $product->short_description : '') }}</textarea>

                    @error('short_description')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror

                </div>


                <div class="product-field">

                    <label for="long_description">
                        Full Description
                    </label>

                    <textarea
                        id="long_description"
                        name="long_description"
                        rows="9"
                        placeholder="Complete product description...">{{ old('long_description', $editing ? $product->long_description : '') }}</textarea>

                    @error('long_description')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror

                </div>


                <div class="product-field">

                    <label for="additional_info">
                        Additional Information
                    </label>

                    <textarea
                        id="additional_info"
                        name="additional_info"
                        rows="5"
                        placeholder="Materials, care instructions, fit information, shipping notes...">{{ old('additional_info', $editing ? $product->additional_info : '') }}</textarea>

                    @error('additional_info')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror

                </div>

            </div>

        </section>


        {{-- ====================================================
             PRICING
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Product pricing
                    </span>

                    <h3>Pricing</h3>
                </div>

                <div class="product-panel-icon">
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-form-grid-3">

                    <div class="product-field">

                        <label for="regular_price">
                            Regular Price
                            <span class="required">*</span>
                        </label>

                        <div class="product-money-input">

                            <span>$</span>

                            <input
                                type="number"
                                id="regular_price"
                                name="regular_price"
                                value="{{ old('regular_price', $editing ? $product->regular_price : '') }}"
                                min="0"
                                step="0.01"
                                required
                                placeholder="0.00">

                        </div>

                        @error('regular_price')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>


                    <div class="product-field">

                        <label for="sale_price">
                            Sale Price
                        </label>

                        <div class="product-money-input">

                            <span>$</span>

                            <input
                                type="number"
                                id="sale_price"
                                name="sale_price"
                                value="{{ old('sale_price', $editing ? $product->sale_price : '') }}"
                                min="0"
                                step="0.01"
                                placeholder="0.00">

                        </div>

                        @error('sale_price')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>


                    <div class="product-field">

                        <label for="cost_price">
                            Cost Price
                            <span class="required">*</span>
                        </label>

                        <div class="product-money-input">

                            <span>$</span>

                            <input
                                type="number"
                                id="cost_price"
                                name="cost_price"
                                value="{{ old('cost_price', $editing ? $product->cost_price : '') }}"
                                min="0"
                                step="0.01"
                                required
                                placeholder="0.00">

                        </div>

                        @error('cost_price')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>

                </div>


                <div
                    class="product-margin-preview"
                    id="productMarginPreview">

                    <div>
                        <span>Current selling price</span>
                        <strong id="marginSellingPrice">$0.00</strong>
                    </div>

                    <div>
                        <span>Estimated margin</span>
                        <strong id="marginAmount">$0.00</strong>
                    </div>

                    <div>
                        <span>Margin percentage</span>
                        <strong id="marginPercentage">0%</strong>
                    </div>

                </div>

            </div>

        </section>


        {{-- ====================================================
             INVENTORY
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Stock management
                    </span>

                    <h3>Inventory</h3>
                </div>

                <div class="product-panel-icon">
                    <i class="fa-solid fa-warehouse"></i>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-form-grid-3">

                    <div class="product-field">

                        <label for="stock">
                            Stock Quantity
                        </label>

                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            value="{{ old('stock', $editing ? $product->stock : 0) }}"
                            min="0"
                            step="1">

                        @error('stock')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>


                    <div class="product-field">

                        <label for="reorder_point">
                            Reorder Point
                        </label>

                        <input
                            type="number"
                            id="reorder_point"
                            name="reorder_point"
                            value="{{ old('reorder_point', $editing ? $product->reorder_point : '') }}"
                            min="0"
                            step="1"
                            placeholder="5">

                        <span class="product-field-help">
                            Low-stock threshold.
                        </span>

                        @error('reorder_point')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>


                    <div class="product-field">

                        <label for="reorder_quantity">
                            Reorder Quantity
                        </label>

                        <input
                            type="number"
                            id="reorder_quantity"
                            name="reorder_quantity"
                            value="{{ old('reorder_quantity', $editing ? $product->reorder_quantity : '') }}"
                            min="1"
                            step="1"
                            placeholder="10">

                        @error('reorder_quantity')
                        <span class="product-field-error">
                            {{ $message }}
                        </span>
                        @enderror

                    </div>

                </div>

            </div>

        </section>


        {{-- ====================================================
             PRODUCT MEDIA
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Product photography
                    </span>

                    <h3>Product Media</h3>
                </div>

                <div class="product-panel-icon">
                    <i class="fa-regular fa-images"></i>
                </div>

            </div>


            <div class="product-form-body">

                {{-- Featured image --}}

                <div class="product-field">

                    <label>
                        Featured Image
                    </label>

                    <label
                        for="featured_image"
                        class="product-media-uploader"
                        id="featuredUploader">

                        <input
                            type="file"
                            id="featured_image"
                            name="featured_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">

                        <div
                            class="product-featured-preview"
                            id="featuredPreview">

                            @if($editing && $product->featured_image)

                            <img
                                src="{{ $mediaUrl($product->featured_image) }}"
                                alt="{{ $product->title }}"
                                id="featuredPreviewImage">

                            <div
                                class="product-upload-empty"
                                id="featuredEmpty"
                                hidden>
                                <i class="fa-solid fa-cloud-arrow-up"></i>

                                <strong>
                                    Choose featured image
                                </strong>

                                <span>
                                    JPG, PNG or WebP · Maximum 5 MB
                                </span>
                            </div>

                            @else

                            <img
                                src=""
                                alt=""
                                id="featuredPreviewImage"
                                hidden>

                            <div
                                class="product-upload-empty"
                                id="featuredEmpty">
                                <i class="fa-solid fa-cloud-arrow-up"></i>

                                <strong>
                                    Choose featured image
                                </strong>

                                <span>
                                    JPG, PNG or WebP · Maximum 5 MB
                                </span>
                            </div>

                            @endif

                        </div>

                    </label>


                    <div class="product-featured-image-actions">

                        <label
                            for="featured_image"
                            class="admin-button admin-button-secondary">
                            <i class="fa-solid fa-image"></i>

                            <span id="featuredChooseText">
                                {{ $editing && $product->featured_image
                    ? 'Replace Image'
                    : 'Choose Image' }}
                            </span>
                        </label>


                        <button
                            type="button"
                            class="product-remove-featured-button"
                            id="removeFeaturedImage"
                            @if(!$editing || !$product->featured_image)
                            hidden
                            @endif
                            >
                            <i class="fa-solid fa-trash"></i>
                            Remove Image
                        </button>

                    </div>


                    {{-- Tells ProductController to remove current image --}}
                    <input
                        type="hidden"
                        name="remove_featured_image"
                        id="removeFeaturedImageInput"
                        value="{{ old('remove_featured_image', 0) }}">


                    <span class="product-field-help">
                        Uploading a new image replaces the current featured image.
                        Remove Image permanently removes the current image after saving.
                    </span>


                    @error('featured_image')

                    <span class="product-field-error">
                        {{ $message }}
                    </span>

                    @enderror

                </div>


                {{-- ============================================================
     GALLERY IMAGES
============================================================ --}}

                <div class="product-field">

                    <label>
                        Gallery Images
                    </label>

                    <div class="product-gallery-toolbar">

                        <label
                            for="gallery_images"
                            class="admin-button admin-button-secondary product-gallery-add-button">
                            <i class="fa-solid fa-images"></i>
                            Add Gallery Images
                        </label>

                        <input
                            type="file"
                            id="gallery_images"
                            name="gallery_images[]"
                            multiple
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            hidden>

                        <span
                            class="product-gallery-count"
                            id="galleryImageCount"></span>

                    </div>

                    <span class="product-field-help">
                        Select multiple JPG, PNG or WebP images. You can remove
                        existing or newly selected images before saving.
                    </span>


                    {{-- ========================================================
         EXISTING GALLERY IMAGES — EDIT PAGE
    ========================================================= --}}

                    @if(
                    $editing &&
                    $product->images->isNotEmpty()
                    )

                    <div
                        class="product-existing-gallery"
                        id="existingGallery">

                        @foreach($product->images as $image)

                        <div
                            class="product-existing-image"
                            data-existing-gallery-image
                            data-image-id="{{ $image->id }}">

                            <img
                                src="{{ $mediaUrl($image->image) }}"
                                alt="{{ $product->title }} gallery image"
                                loading="lazy">


                            <div class="product-gallery-image-overlay">

                                <button
                                    type="button"
                                    class="product-gallery-remove-button"
                                    data-remove-existing-gallery
                                    title="Remove image">
                                    <i class="fa-solid fa-trash"></i>

                                    <span>
                                        Remove
                                    </span>
                                </button>

                            </div>


                            <input
                                type="checkbox"
                                name="remove_gallery_images[]"
                                value="{{ $image->id }}"
                                data-existing-remove-checkbox
                                @checked(
                                in_array(
                                $image->id,
                            old(
                            'remove_gallery_images',
                            []
                            )
                            )
                            )
                            hidden
                            >


                            <div
                                class="product-gallery-remove-state"
                                data-gallery-remove-state
                                hidden>

                                <i class="fa-solid fa-trash"></i>

                                <strong>
                                    Will be removed
                                </strong>

                                <button
                                    type="button"
                                    data-undo-gallery-remove>
                                    Undo
                                </button>

                            </div>

                        </div>

                        @endforeach

                    </div>

                    @endif


                    {{-- ========================================================
         NEW GALLERY IMAGE PREVIEWS
    ========================================================= --}}

                    <div
                        class="product-gallery-preview"
                        id="galleryPreview"></div>


                    @error('gallery_images')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror


                    @error('gallery_images.*')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror


                    @error('remove_gallery_images.*')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror

                </div>

            </div>

        </section>


        {{-- ====================================================
             OPTIONS AND VARIANTS
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Sizes, colors and variations
                    </span>

                    <h3>Product Options & Variants</h3>
                </div>

                <div class="product-panel-icon">
                    <i class="fa-solid fa-code-branch"></i>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-options-toolbar">

                    <p>
                        Select the values available for this product,
                        then generate every possible variant combination.
                    </p>

                    <button
                        type="button"
                        class="admin-button admin-button-secondary"
                        id="openNewOptionModal">
                        <i class="fa-solid fa-plus"></i>
                        New Option
                    </button>

                </div>


                <div
                    class="product-options-list"
                    id="productOptionsList">

                    @forelse($productOptions as $option)

                    <div
                        class="product-option-card"
                        data-option-card
                        data-option-id="{{ $option->id }}"
                        data-option-name="{{ $option->name }}">

                        <div class="product-option-heading">

                            <div>

                                <div class="product-option-title">

                                    <label class="product-option-enable">

                                        <input
                                            type="checkbox"
                                            name="product_options[]"
                                            value="{{ $option->id }}"
                                            data-option-toggle
                                            @checked(
                                            in_array(
                                            $option->id,
                                        $selectedOptionIds
                                        )
                                        )
                                        >

                                        <span>
                                            {{ $option->name }}
                                        </span>

                                    </label>

                                    <span class="product-option-type">
                                        {{ ucfirst($option->type) }}
                                    </span>

                                </div>

                            </div>


                            <button
                                type="button"
                                class="product-add-value-button"
                                data-add-value
                                data-option-id="{{ $option->id }}"
                                data-option-name="{{ $option->name }}">
                                <i class="fa-solid fa-plus"></i>
                                Add Value
                            </button>

                        </div>


                        <div class="product-option-values">

                            @forelse($option->values as $value)

                            <label
                                class="product-option-value"
                                data-option-value>

                                <input
                                    type="checkbox"
                                    name="product_option_values[]"
                                    value="{{ $value->id }}"
                                    data-option-value-checkbox
                                    data-option-id="{{ $option->id }}"
                                    data-option-name="{{ $option->name }}"
                                    data-value-id="{{ $value->id }}"
                                    data-value-label="{{ $value->label }}"
                                    @checked(
                                    in_array(
                                    $value->id,
                                $selectedValueIds
                                )
                                )
                                >

                                @if($value->color_code)

                                <span
                                    class="product-color-swatch"
                                    style="background: {{ $value->color_code }};"></span>

                                @endif

                                <span>
                                    {{ $value->label }}
                                </span>

                            </label>

                            @empty

                            <span
                                class="product-option-empty"
                                data-option-empty>
                                No values yet.
                            </span>

                            @endforelse

                        </div>

                    </div>

                    @empty

                    <div
                        class="product-options-empty"
                        id="noProductOptions">
                        <i class="fa-solid fa-sliders"></i>

                        <strong>
                            No product options yet
                        </strong>

                        <span>
                            Create an option such as Color or Size.
                        </span>
                    </div>

                    @endforelse

                </div>


                <div class="product-variant-generator">

                    <div>

                        <strong>
                            Generate Variants
                        </strong>

                        <span>
                            Existing matching variants are preserved when you regenerate.
                        </span>

                    </div>

                    <button
                        type="button"
                        class="admin-button admin-button-primary"
                        id="generateVariants">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        Generate Variants
                    </button>

                </div>


                @error('variants')
                <span class="product-field-error product-variant-main-error">
                    {{ $message }}
                </span>
                @enderror


                <div
                    class="product-variants-container"
                    id="variantsContainer">

                    <div
                        class="product-variants-empty"
                        id="variantsEmpty">
                        <i class="fa-solid fa-code-branch"></i>

                        <strong>No variants generated</strong>

                        <span>
                            Select option values above and click Generate Variants.
                        </span>
                    </div>

                </div>

            </div>

        </section>

    </div>


    {{-- ========================================================
         RIGHT COLUMN
    ========================================================= --}}

    <aside class="product-form-sidebar">


        {{-- ====================================================
             PUBLISH
        ===================================================== --}}

        <section class="admin-panel product-form-panel product-sticky-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Visibility
                    </span>

                    <h3>Publish</h3>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-field">

                    <label for="status">
                        Product Status
                        <span class="required">*</span>
                    </label>

                    <select
                        id="status"
                        name="status"
                        required>

                        <option
                            value="draft"
                            @selected(
                            old( 'status' ,
                            $editing
                            ? $product->status
                            : 'draft'
                            ) === 'draft'
                            )
                            >
                            Draft
                        </option>

                        <option
                            value="active"
                            @selected(
                            old( 'status' ,
                            $editing
                            ? $product->status
                            : ''
                            ) === 'active'
                            )
                            >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(
                            old( 'status' ,
                            $editing
                            ? $product->status
                            : ''
                            ) === 'inactive'
                            )
                            >
                            Inactive
                        </option>

                    </select>

                    @error('status')
                    <span class="product-field-error">
                        {{ $message }}
                    </span>
                    @enderror

                </div>


                <label class="product-featured-toggle">

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0">

                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        @checked(
                        old( 'is_featured' ,
                        $editing
                        ? $product->is_featured
                    : false
                    )
                    )
                    >

                    <span class="product-toggle-ui"></span>

                    <span>

                        <strong>
                            Featured Product
                        </strong>

                        <small>
                            Highlight this product throughout the storefront.
                        </small>

                    </span>

                </label>

            </div>

        </section>


        {{-- ====================================================
             CATEGORIES
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Organization
                    </span>

                    <h3>Categories</h3>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-checkbox-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        placeholder="Search categories..."
                        data-filter-checkboxes="#categoryChecklist">

                </div>


                <div
                    class="product-checklist"
                    id="categoryChecklist">

                    @forelse($categories as $category)

                    <label
                        class="product-check-item"
                        data-check-label="{{ strtolower($category->title) }}">

                        <input
                            type="checkbox"
                            name="categories[]"
                            value="{{ $category->id }}"
                            @checked(
                            in_array(
                            $category->id,
                        $selectedCategories
                        )
                        )
                        >

                        <span>
                            {{ $category->title }}

                            @if($category->parent)
                            <small>
                                {{ $category->parent->title }}
                            </small>
                            @endif
                        </span>

                    </label>

                    @empty

                    <p class="product-sidebar-empty">
                        No categories available.
                    </p>

                    @endforelse

                </div>

                @error('categories.*')
                <span class="product-field-error">
                    {{ $message }}
                </span>
                @enderror

            </div>

        </section>


        {{-- ====================================================
             TAGS
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Discoverability
                    </span>

                    <h3>Tags</h3>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-checkbox-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        placeholder="Search tags..."
                        data-filter-checkboxes="#tagChecklist">

                </div>


                <div
                    class="product-checklist"
                    id="tagChecklist">

                    @forelse($tags as $tag)

                    <label
                        class="product-check-item"
                        data-check-label="{{ strtolower($tag->title) }}">

                        <input
                            type="checkbox"
                            name="tags[]"
                            value="{{ $tag->id }}"
                            @checked(
                            in_array(
                            $tag->id,
                        $selectedTags
                        )
                        )
                        >

                        <span>
                            {{ $tag->title }}
                        </span>

                    </label>

                    @empty

                    <p class="product-sidebar-empty">
                        No tags available.
                    </p>

                    @endforelse

                </div>

            </div>

        </section>


        {{-- ====================================================
             SEO
        ===================================================== --}}

        <section class="admin-panel product-form-panel">

            <div class="admin-panel-header">

                <div>
                    <span class="admin-panel-eyebrow">
                        Search engines
                    </span>

                    <h3>SEO</h3>
                </div>

            </div>


            <div class="product-form-body">

                <div class="product-field">

                    <label for="meta_title">
                        Meta Title
                    </label>

                    <input
                        type="text"
                        id="meta_title"
                        name="meta_title"
                        maxlength="255"
                        value="{{ old('meta_title', $editing ? $product->meta_title : '') }}"
                        placeholder="SEO title...">

                    <div class="product-character-count">
                        <span id="metaTitleCount">0</span>/60 recommended
                    </div>

                </div>


                <div class="product-field">

                    <label for="meta_description">
                        Meta Description
                    </label>

                    <textarea
                        id="meta_description"
                        name="meta_description"
                        rows="5"
                        placeholder="SEO description...">{{ old('meta_description', $editing ? $product->meta_description : '') }}</textarea>

                    <div class="product-character-count">
                        <span id="metaDescriptionCount">0</span>/160 recommended
                    </div>

                </div>


                <div class="product-field">

                    <label for="meta_keywords">
                        Meta Keywords
                    </label>

                    <textarea
                        id="meta_keywords"
                        name="meta_keywords"
                        rows="3"
                        placeholder="leather jacket, mens jacket, black jacket">{{ old('meta_keywords', $editing ? $product->meta_keywords : '') }}</textarea>

                </div>

            </div>

        </section>

    </aside>

</div>


{{-- ============================================================
     FORM ACTIONS
============================================================ --}}

<div class="product-form-actions">

    <a
        href="{{ route('admin.products.index') }}"
        class="admin-button admin-button-secondary">
        <i class="fa-solid fa-xmark"></i>
        Cancel
    </a>


    <button
        type="submit"
        class="admin-button admin-button-primary product-save-button"
        data-product-submit>

        <i class="fa-solid fa-floppy-disk"></i>

        {{ $editing ? 'Update Product' : 'Create Product' }}

    </button>

</div>


{{-- ============================================================
     NEW OPTION MODAL
============================================================ --}}

<div
    class="product-form-modal"
    id="newOptionModal"
    aria-hidden="true">

    <div
        class="product-form-modal-backdrop"
        data-close-option-modal></div>

    <div class="product-form-modal-dialog">

        <button
            type="button"
            class="product-form-modal-close"
            data-close-option-modal>
            <i class="fa-solid fa-xmark"></i>
        </button>

        <span class="admin-panel-eyebrow">
            Product options
        </span>

        <h3>Create New Option</h3>

        <div
            class="product-modal-message"
            id="newOptionMessage"></div>


        <div class="product-field">

            <label for="newOptionName">
                Option Name
            </label>

            <input
                type="text"
                id="newOptionName"
                placeholder="e.g. Color">

        </div>


        <div class="product-field">

            <label for="newOptionType">
                Option Type
            </label>

            <select id="newOptionType">

                <option value="select">
                    Select
                </option>

                <option value="color">
                    Color
                </option>

                <option value="text">
                    Text
                </option>

            </select>

        </div>


        <div class="product-modal-actions">

            <button
                type="button"
                class="admin-button admin-button-secondary"
                data-close-option-modal>
                Cancel
            </button>

            <button
                type="button"
                class="admin-button admin-button-primary"
                id="saveNewOption">
                <i class="fa-solid fa-plus"></i>
                Create Option
            </button>

        </div>

    </div>

</div>


{{-- ============================================================
     NEW OPTION VALUE MODAL
============================================================ --}}

<div
    class="product-form-modal"
    id="newValueModal"
    aria-hidden="true">

    <div
        class="product-form-modal-backdrop"
        data-close-value-modal></div>

    <div class="product-form-modal-dialog">

        <button
            type="button"
            class="product-form-modal-close"
            data-close-value-modal>
            <i class="fa-solid fa-xmark"></i>
        </button>

        <span class="admin-panel-eyebrow">
            Option value
        </span>

        <h3 id="newValueModalTitle">
            Add Value
        </h3>

        <div
            class="product-modal-message"
            id="newValueMessage"></div>


        <input
            type="hidden"
            id="newValueOptionId">


        <div class="product-field">

            <label for="newValueLabel">
                Label
            </label>

            <input
                type="text"
                id="newValueLabel"
                placeholder="e.g. Black">

        </div>


        <div class="product-field">

            <label for="newValueValue">
                Value / Slug
            </label>

            <input
                type="text"
                id="newValueValue"
                placeholder="black">

            <span class="product-field-help">
                Leave blank to generate automatically.
            </span>

        </div>


        <div class="product-field">

            <label for="newValueColor">
                Color Code
            </label>

            <input
                type="text"
                id="newValueColor"
                placeholder="#000000">

        </div>


        <div class="product-modal-actions">

            <button
                type="button"
                class="admin-button admin-button-secondary"
                data-close-value-modal>
                Cancel
            </button>

            <button
                type="button"
                class="admin-button admin-button-primary"
                id="saveNewValue">
                <i class="fa-solid fa-plus"></i>
                Add Value
            </button>

        </div>

    </div>

</div>


@push('page-styles')

<style>
    .product-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 24px;
        align-items: start;
    }

    .product-form-main,
    .product-form-sidebar {
        display: grid;
        gap: 24px;
    }

    .product-form-panel {
        overflow: hidden;
    }

    .product-form-body {
        padding: 20px;
    }

    .product-panel-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #f3f4f6;
        color: #4b5563;
    }

    .product-form-grid-2,
    .product-form-grid-3 {
        display: grid;
        gap: 16px;
    }

    .product-form-grid-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .product-form-grid-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .product-field {
        margin-bottom: 18px;
    }

    .product-field:last-child {
        margin-bottom: 0;
    }

    .product-field>label {
        display: block;
        margin-bottom: 7px;
        color: #1f2937;
        font-size: 12px;
        font-weight: 750;
    }

    .required {
        color: #dc2626;
    }

    .product-field input[type="text"],
    .product-field input[type="search"],
    .product-field input[type="number"],
    .product-field input[type="url"],
    .product-field select,
    .product-field textarea,
    .product-checkbox-search input {
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .13);
        border-radius: 10px;
        background: #fff;
        color: #111827;
        font: inherit;
        font-size: 13px;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .product-field input[type="text"],
    .product-field input[type="search"],
    .product-field input[type="number"],
    .product-field input[type="url"],
    .product-field select {
        height: 44px;
        padding: 0 13px;
    }

    /* ============================================================
   FEATURED IMAGE ACTIONS
============================================================ */

    .product-featured-image-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 12px;
    }

    .product-featured-image-actions label {
        margin: 0 !important;
        cursor: pointer;
    }

    .product-remove-featured-button {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 14px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fff;
        color: #dc2626;
        font: inherit;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition:
            background .2s ease,
            border-color .2s ease,
            color .2s ease;
    }

    .product-remove-featured-button:hover {
        border-color: #dc2626;
        background: #fef2f2;
    }

    .product-remove-featured-button[hidden] {
        display: none !important;
    }

    .product-featured-preview.is-removing {
        opacity: .55;
    }

    .product-featured-preview.is-removing::after {
        content: "Image will be removed when you save";
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(17, 24, 39, .72);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-align: center;
    }

    .product-field textarea {
        min-height: 100px;
        padding: 12px 13px;
        resize: vertical;
        line-height: 1.6;
    }

    .product-field input:focus,
    .product-field select:focus,
    .product-field textarea:focus {
        border-color: rgba(17, 24, 39, .45);
        box-shadow: 0 0 0 3px rgba(17, 24, 39, .06);
    }

    .product-field-help,
    .product-character-count {
        display: block;
        margin-top: 6px;
        color: #9ca3af;
        font-size: 10px;
        line-height: 1.5;
    }

    .product-character-count {
        text-align: right;
    }

    .product-field-error {
        display: block;
        margin-top: 6px;
        color: #dc2626;
        font-size: 11px;
        font-weight: 650;
    }

    .product-input-prefix,
    .product-money-input {
        display: flex;
        align-items: center;
        border: 1px solid rgba(15, 23, 42, .13);
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .product-input-prefix>span,
    .product-money-input>span {
        align-self: stretch;
        display: flex;
        align-items: center;
        padding: 0 11px;
        background: #f9fafb;
        border-right: 1px solid rgba(15, 23, 42, .08);
        color: #6b7280;
        font-size: 11px;
        white-space: nowrap;
    }

    .product-input-prefix input,
    .product-money-input input {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .product-margin-preview {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 4px;
        padding: 14px;
        border-radius: 11px;
        background: #f9fafb;
    }

    .product-margin-preview div {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .product-margin-preview span {
        color: #9ca3af;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .product-margin-preview strong {
        color: #111827;
        font-size: 14px;
    }


    /* Media */

    .product-media-uploader {
        display: block !important;
        margin: 0 !important;
        cursor: pointer;
    }

    .product-media-uploader>input,
    .product-gallery-upload-button>input {
        display: none;
    }

    .product-featured-preview {
        min-height: 280px;
        position: relative;
        overflow: hidden;
        border: 2px dashed rgba(15, 23, 42, .13);
        border-radius: 14px;
        background: #f9fafb;
    }

    .product-featured-preview img {
        width: 100%;
        max-height: 460px;
        display: block;
        object-fit: contain;
        background: #f3f4f6;
    }

    .product-upload-empty {
        min-height: 280px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 25px;
        color: #9ca3af;
        text-align: center;
    }

    .product-upload-empty i {
        font-size: 32px;
    }

    .product-upload-empty strong {
        color: #4b5563;
        font-size: 13px;
    }

    .product-upload-empty span {
        font-size: 10px;
    }

    .product-gallery-upload-button {
        width: max-content;
        display: inline-flex !important;
        align-items: center;
        gap: 7px;
        min-height: 39px;
        padding: 8px 13px;
        margin-bottom: 0 !important;
        border: 1px solid rgba(15, 23, 42, .13);
        border-radius: 9px;
        background: #fff;
        cursor: pointer;
    }

    .product-existing-gallery,
    .product-gallery-preview {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .product-existing-image,
    .product-gallery-preview-item {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1 / 1;
        border-radius: 10px;
        background: #f3f4f6;
    }

    .product-existing-image img,
    .product-gallery-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-remove-image {
        position: absolute;
        inset: auto 7px 7px 7px;
    }

    .product-remove-image input {
        position: absolute;
        opacity: 0;
    }

    .product-remove-image span {
        display: flex;
        justify-content: center;
        gap: 5px;
        padding: 7px;
        border-radius: 7px;
        background: rgba(17, 24, 39, .82);
        color: #fff;
        font-size: 9px;
        cursor: pointer;
    }

    .product-remove-image input:checked+span {
        background: #dc2626;
    }


    /* Sidebar */

    .product-featured-toggle {
        display: flex !important;
        align-items: flex-start;
        gap: 11px;
        margin: 0 !important;
        cursor: pointer;
    }

    .product-featured-toggle>input[type="checkbox"] {
        position: absolute;
        opacity: 0;
    }

    .product-toggle-ui {
        width: 40px;
        height: 22px;
        position: relative;
        flex: 0 0 auto;
        border-radius: 999px;
        background: #d1d5db;
        transition: .2s ease;
    }

    .product-toggle-ui::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        transition: .2s ease;
    }

    .product-featured-toggle input:checked+.product-toggle-ui {
        background: #111827;
    }

    .product-featured-toggle input:checked+.product-toggle-ui::after {
        transform: translateX(18px);
    }

    .product-featured-toggle strong,
    .product-featured-toggle small {
        display: block;
    }

    .product-featured-toggle strong {
        color: #111827;
        font-size: 12px;
    }

    .product-featured-toggle small {
        margin-top: 3px;
        color: #9ca3af;
        font-size: 10px;
        line-height: 1.5;
    }

    .product-checkbox-search {
        position: relative;
        margin-bottom: 10px;
    }

    .product-checkbox-search i {
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 11px;
    }

    .product-checkbox-search input {
        height: 40px;
        padding: 0 12px 0 34px;
    }

    .product-checklist {
        max-height: 280px;
        overflow-y: auto;
        display: grid;
        gap: 4px;
    }

    .product-check-item {
        display: flex !important;
        align-items: center;
        gap: 8px;
        margin: 0 !important;
        padding: 8px;
        border-radius: 8px;
        cursor: pointer;
    }

    .product-check-item:hover {
        background: #f9fafb;
    }

    .product-check-item input {
        width: 15px;
        height: 15px;
    }

    .product-check-item>span {
        color: #374151;
        font-size: 11px;
        font-weight: 650;
    }

    .product-check-item small {
        display: block;
        margin-top: 2px;
        color: #9ca3af;
        font-size: 9px;
    }

    .product-sidebar-empty {
        color: #9ca3af;
        font-size: 11px;
    }


    /* Options */

    .product-options-toolbar,
    .product-variant-generator {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
    }

    .product-options-toolbar {
        margin-bottom: 16px;
    }

    .product-options-toolbar p {
        max-width: 650px;
        margin: 0;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.6;
    }

    .product-options-list {
        display: grid;
        gap: 12px;
    }

    .product-option-card {
        padding: 15px;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 12px;
    }

    .product-option-heading {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 12px;
    }

    .product-option-title {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .product-option-enable {
        display: flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
    }

    .product-option-enable span {
        color: #111827;
        font-size: 13px;
        font-weight: 750;
    }

    .product-option-type {
        padding: 3px 7px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 8px;
        font-weight: 750;
        text-transform: uppercase;
    }

    .product-add-value-button {
        border: 0;
        background: transparent;
        color: #4b5563;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    .product-option-values {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .product-option-value {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        margin: 0 !important;
        padding: 7px 10px;
        border: 1px solid rgba(15, 23, 42, .1);
        border-radius: 8px;
        cursor: pointer;
    }

    .product-option-value:has(input:checked) {
        border-color: #111827;
        background: #f9fafb;
    }

    .product-option-value input {
        width: 14px;
        height: 14px;
    }

    .product-color-swatch {
        width: 15px;
        height: 15px;
        border: 1px solid rgba(0, 0, 0, .15);
        border-radius: 50%;
    }

    .product-option-empty {
        color: #9ca3af;
        font-size: 10px;
    }

    .product-options-empty,
    .product-variants-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 35px;
        border: 1px dashed rgba(15, 23, 42, .13);
        border-radius: 12px;
        color: #9ca3af;
        text-align: center;
    }

    .product-options-empty i,
    .product-variants-empty i {
        font-size: 25px;
    }

    .product-options-empty strong,
    .product-variants-empty strong {
        color: #4b5563;
        font-size: 12px;
    }

    .product-options-empty span,
    .product-variants-empty span {
        font-size: 10px;
    }

    .product-variant-generator {
        margin-top: 18px;
        padding: 14px;
        border-radius: 11px;
        background: #f9fafb;
    }

    .product-variant-generator strong,
    .product-variant-generator span {
        display: block;
    }

    .product-variant-generator strong {
        color: #111827;
        font-size: 12px;
    }

    .product-variant-generator span {
        margin-top: 3px;
        color: #9ca3af;
        font-size: 10px;
    }

    .product-variant-main-error {
        margin-top: 12px;
    }


    /* Variants */

    .product-variants-container {
        margin-top: 15px;
    }

    .product-variant-row {
        margin-bottom: 10px;
        padding: 14px;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 12px;
    }

    .product-variant-row:last-child {
        margin-bottom: 0;
    }

    .product-variant-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 12px;
    }

    .product-variant-heading strong {
        color: #111827;
        font-size: 12px;
    }

    .product-variant-remove {
        border: 0;
        background: transparent;
        color: #dc2626;
        font-size: 10px;
        cursor: pointer;
    }

    .product-variant-fields {
        display: grid;
        grid-template-columns:
            minmax(130px, 1.2fr) repeat(5, minmax(90px, 1fr)) minmax(120px, 1fr);
        gap: 9px;
        align-items: end;
    }

    .product-variant-field label {
        display: block;
        margin-bottom: 5px;
        color: #6b7280;
        font-size: 8px;
        font-weight: 750;
        text-transform: uppercase;
    }

    .product-variant-field input {
        width: 100%;
        height: 38px;
        padding: 0 9px;
        border: 1px solid rgba(15, 23, 42, .11);
        border-radius: 8px;
        font-size: 11px;
    }

    /* ============================================================
       VARIANT IMAGE MANAGEMENT
    ============================================================ */

    .product-variant-image-input {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .product-variant-image-preview {
        position: relative;
        width: 100%;
        height: 76px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(15, 23, 42, .10);
        border-radius: 9px;
        background: #f8fafc;
    }

    .product-variant-image-preview img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .product-variant-image-empty {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 8px;
        color: #94a3b8;
        font-size: 8px;
        font-weight: 650;
        text-align: center;
    }

    .product-variant-image-empty i {
        font-size: 16px;
    }

    .product-variant-image-empty.is-removing {
        background: #fff7f7;
        color: #dc2626;
    }

    .product-variant-image-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .product-variant-image-action {
        min-height: 29px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 5px 8px;
        border: 1px solid rgba(15, 23, 42, .11);
        border-radius: 7px;
        background: #fff;
        color: #374151;
        font: inherit;
        font-size: 8px;
        font-weight: 750;
        line-height: 1;
        cursor: pointer;
        transition: .18s ease;
    }

    .product-variant-image-action:hover {
        border-color: #7c6cff;
        color: #5b4df6;
        background: #f8f7ff;
    }

    .product-variant-image-action.is-danger {
        border-color: #fecaca;
        color: #dc2626;
    }

    .product-variant-image-action.is-danger:hover {
        border-color: #dc2626;
        background: #fef2f2;
    }

    .product-variant-image-action.is-undo {
        border-color: #cbd5e1;
        color: #475569;
    }

    .product-variant-image-action[hidden] {
        display: none !important;
    }


    /* Actions */

    .product-form-actions {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 24px;
        padding: 14px 18px;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 12px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 -6px 25px rgba(15, 23, 42, .06);
        backdrop-filter: blur(10px);
    }


    /* Modals */

    .product-form-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .product-form-modal.is-open {
        display: flex;
    }

    .product-form-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .58);
    }

    .product-form-modal-dialog {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 450px;
        padding: 25px;
        border-radius: 16px;
        background: #fff;
    }

    .product-form-modal-dialog h3 {
        margin: 4px 0 20px;
    }

    .product-form-modal-close {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 8px;
        background: #f3f4f6;
        cursor: pointer;
    }

    .product-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 20px;
    }

    .product-modal-message {
        display: none;
        margin-bottom: 14px;
        padding: 9px 11px;
        border-radius: 8px;
        font-size: 11px;
    }

    .product-modal-message.is-error {
        display: block;
        background: #fef2f2;
        color: #b91c1c;
    }

    .product-modal-message.is-success {
        display: block;
        background: #ecfdf3;
        color: #15803d;
    }

    /* ============================================================
   GALLERY IMAGES
============================================================ */

    .product-gallery-toolbar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .product-gallery-add-button {
        margin: 0 !important;
        cursor: pointer;
    }

    .product-gallery-count {
        color: #6b7280;
        font-size: 11px;
        font-weight: 650;
    }

    .product-existing-gallery,
    .product-gallery-preview {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-top: 15px;
    }


    /* Individual image */

    .product-existing-image,
    .product-gallery-preview-item {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1 / 1;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 12px;
        background: #f3f4f6;
    }

    .product-existing-image img,
    .product-gallery-preview-item img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }


    /* Hover overlay */

    .product-gallery-image-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 9px;
        background:
            linear-gradient(transparent 45%,
                rgba(17, 24, 39, .8));
        opacity: 0;
        transition: opacity .2s ease;
    }

    .product-existing-image:hover .product-gallery-image-overlay,
    .product-gallery-preview-item:hover .product-gallery-image-overlay {
        opacity: 1;
    }


    /* Remove button */

    .product-gallery-remove-button {
        width: 100%;
        min-height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 10px;
        border: 0;
        border-radius: 8px;
        background: #dc2626;
        color: #fff;
        font: inherit;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    .product-gallery-remove-button:hover {
        background: #b91c1c;
    }


    /* New image filename */

    .product-gallery-file-name {
        position: absolute;
        top: 7px;
        left: 7px;
        right: 7px;
        overflow: hidden;
        padding: 5px 7px;
        border-radius: 6px;
        background: rgba(17, 24, 39, .72);
        color: #fff;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }


    /* Existing image marked for removal */

    .product-existing-image.is-removing img {
        opacity: .22;
    }

    .product-existing-image.is-removing .product-gallery-image-overlay {
        display: none;
    }

    .product-gallery-remove-state {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 12px;
        background: rgba(254, 242, 242, .94);
        color: #dc2626;
        text-align: center;
    }

    .product-gallery-remove-state[hidden] {
        display: none;
    }

    .product-gallery-remove-state>i {
        font-size: 19px;
    }

    .product-gallery-remove-state strong {
        font-size: 10px;
    }

    .product-gallery-remove-state button {
        padding: 5px 9px;
        border: 1px solid #fecaca;
        border-radius: 7px;
        background: #fff;
        color: #dc2626;
        font: inherit;
        font-size: 9px;
        font-weight: 700;
        cursor: pointer;
    }

    .product-gallery-remove-state button:hover {
        border-color: #dc2626;
    }


    /* Responsive */

    @media (max-width: 900px) {

        .product-existing-gallery,
        .product-gallery-preview {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

    }

    @media (max-width: 600px) {

        .product-existing-gallery,
        .product-gallery-preview {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }

    @media (max-width: 380px) {

        .product-existing-gallery,
        .product-gallery-preview {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 1200px) {

        .product-form-layout {
            grid-template-columns: 1fr;
        }

        .product-form-sidebar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .product-sticky-panel {
            position: static;
        }

        .product-variant-fields {
            grid-template-columns: repeat(3, 1fr);
        }

    }


    @media (max-width: 760px) {

        .product-form-grid-2,
        .product-form-grid-3,
        .product-form-sidebar,
        .product-margin-preview {
            grid-template-columns: 1fr;
        }

        .product-existing-gallery,
        .product-gallery-preview {
            grid-template-columns: repeat(2, 1fr);
        }

        .product-options-toolbar,
        .product-variant-generator {
            align-items: stretch;
            flex-direction: column;
        }

        .product-variant-fields {
            grid-template-columns: repeat(2, 1fr);
        }

        .product-form-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .product-form-actions .admin-button {
            justify-content: center;
        }

    }


    @media (max-width: 480px) {

        .product-variant-fields {
            grid-template-columns: 1fr;
        }

    }
</style>

@endpush


@push('page-scripts')

<script>
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        /*
        |--------------------------------------------------------------------------
        | Server data
        |--------------------------------------------------------------------------
        */

        const initialVariants =
            @json($initialVariants);

        const mediaBaseUrl =
            @json(asset('storage'));

        const optionStoreUrl =
            @json(route('admin.product-options.store'));

        const optionValueStoreUrlTemplate =
            @json(route('admin.product-options.values.store', ['productOption' => '__OPTION_ID__']));

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute('content') ||
            @json(csrf_token());


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            const div =
                document.createElement('div');

            div.textContent =
                value == null ? '' : String(value);

            return div.innerHTML;
        }


        function slugify(value) {

            return String(value || '')
                .normalize('NFKD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim()
                .replace(/['"]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }


        function normaliseImageUrl(path) {

            if (!path) {
                return '';
            }

            path = String(path);

            if (
                path.startsWith('http://') ||
                path.startsWith('https://') ||
                path.startsWith('//') ||
                path.startsWith('data:')
            ) {
                return path;
            }

            path =
                path.replace(/\\/g, '/')
                .replace(/^\/+/, '');

            if (path.startsWith('public/')) {
                path = path.substring(7);
            }

            if (path.startsWith('storage/')) {
                return '/' + path;
            }

            return mediaBaseUrl.replace(/\/$/, '') +
                '/' +
                path;
        }


        function optionSignature(options) {

            return [...options]
                .map(function(option) {

                    return {
                        option_id: Number(option.option_id),

                        value_id: Number(option.value_id)
                    };

                })
                .sort(function(a, b) {

                    return a.option_id - b.option_id;

                })
                .map(function(option) {

                    return option.option_id +
                        ':' +
                        option.value_id;

                })
                .join('|');
        }


        /*
        |--------------------------------------------------------------------------
        | Slug generation
        |--------------------------------------------------------------------------
        */

        const titleInput =
            document.getElementById('title');

        const slugInput =
            document.getElementById('slug');

        let slugEditedManually =
            Boolean(slugInput?.value);

        @if(!$editing && !old('slug'))
        slugEditedManually = false;
        @endif

        slugInput?.addEventListener(
            'input',
            function() {

                slugEditedManually = true;

            }
        );

        titleInput?.addEventListener(
            'input',
            function() {

                if (
                    slugInput &&
                    !slugEditedManually
                ) {
                    slugInput.value =
                        slugify(titleInput.value);
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Pricing margin
        |--------------------------------------------------------------------------
        */

        const regularPrice =
            document.getElementById(
                'regular_price'
            );

        const salePrice =
            document.getElementById(
                'sale_price'
            );

        const costPrice =
            document.getElementById(
                'cost_price'
            );

        function updateMargin() {

            const regular =
                Number(regularPrice?.value || 0);

            const sale =
                Number(salePrice?.value || 0);

            const cost =
                Number(costPrice?.value || 0);

            const selling =
                sale > 0 && sale < regular ?
                sale :
                regular;

            const margin =
                selling - cost;

            const percentage =
                selling > 0 ?
                (margin / selling) * 100 :
                0;

            document.getElementById(
                    'marginSellingPrice'
                ).textContent =
                '$' + selling.toFixed(2);

            document.getElementById(
                    'marginAmount'
                ).textContent =
                '$' + margin.toFixed(2);

            document.getElementById(
                    'marginPercentage'
                ).textContent =
                percentage.toFixed(1) + '%';
        }

        [
            regularPrice,
            salePrice,
            costPrice
        ].forEach(function(element) {

            element?.addEventListener(
                'input',
                updateMargin
            );

        });

        updateMargin();


        /*
|--------------------------------------------------------------------------
| Featured Image
|--------------------------------------------------------------------------
|
| Supports:
| - Add
| - Preview
| - Replace
| - Remove
| - Select another image after removal
|
*/

        const featuredInput =
            document.getElementById(
                'featured_image'
            );

        const featuredImage =
            document.getElementById(
                'featuredPreviewImage'
            );

        const featuredEmpty =
            document.getElementById(
                'featuredEmpty'
            );

        const featuredPreview =
            document.getElementById(
                'featuredPreview'
            );

        const removeFeaturedButton =
            document.getElementById(
                'removeFeaturedImage'
            );

        const removeFeaturedInput =
            document.getElementById(
                'removeFeaturedImageInput'
            );

        const featuredChooseText =
            document.getElementById(
                'featuredChooseText'
            );


        let featuredObjectUrl = null;


        /*
        |--------------------------------------------------------------------------
        | New / replacement image selected
        |--------------------------------------------------------------------------
        */

        featuredInput?.addEventListener(
            'change',
            function() {

                const file =
                    this.files?.[0];

                if (!file) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Release previous browser preview
                |--------------------------------------------------------------------------
                */

                if (featuredObjectUrl) {

                    URL.revokeObjectURL(
                        featuredObjectUrl
                    );

                }


                featuredObjectUrl =
                    URL.createObjectURL(
                        file
                    );


                /*
                |--------------------------------------------------------------------------
                | Show preview
                |--------------------------------------------------------------------------
                */

                if (featuredImage) {

                    featuredImage.src =
                        featuredObjectUrl;

                    featuredImage.alt =
                        file.name;

                    featuredImage.hidden =
                        false;

                }


                if (featuredEmpty) {

                    featuredEmpty.hidden =
                        true;

                }


                /*
                |--------------------------------------------------------------------------
                | Selecting another image cancels removal
                |--------------------------------------------------------------------------
                */

                if (removeFeaturedInput) {

                    removeFeaturedInput.value =
                        '0';

                }


                featuredPreview?.classList.remove(
                    'is-removing'
                );


                /*
                |--------------------------------------------------------------------------
                | Show remove button
                |--------------------------------------------------------------------------
                */

                if (removeFeaturedButton) {

                    removeFeaturedButton.hidden =
                        false;

                }


                if (featuredChooseText) {

                    featuredChooseText.textContent =
                        'Replace Image';

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Remove featured image
        |--------------------------------------------------------------------------
        */

        removeFeaturedButton?.addEventListener(
            'click',
            function() {

                /*
                |--------------------------------------------------------------------------
                | Clear newly selected file
                |--------------------------------------------------------------------------
                */

                if (featuredInput) {

                    featuredInput.value = '';

                }


                if (featuredObjectUrl) {

                    URL.revokeObjectURL(
                        featuredObjectUrl
                    );

                    featuredObjectUrl = null;

                }


                /*
                |--------------------------------------------------------------------------
                | Tell backend to remove existing image
                |--------------------------------------------------------------------------
                */

                if (removeFeaturedInput) {

                    removeFeaturedInput.value =
                        '1';

                }


                /*
                |--------------------------------------------------------------------------
                | Hide image
                |--------------------------------------------------------------------------
                */

                if (featuredImage) {

                    featuredImage.src = '';
                    featuredImage.hidden = true;

                }


                /*
                |--------------------------------------------------------------------------
                | Show upload placeholder
                |--------------------------------------------------------------------------
                */

                if (featuredEmpty) {

                    featuredEmpty.hidden =
                        false;

                }


                /*
                |--------------------------------------------------------------------------
                | Hide remove button
                |--------------------------------------------------------------------------
                */

                removeFeaturedButton.hidden =
                    true;


                if (featuredChooseText) {

                    featuredChooseText.textContent =
                        'Choose Image';

                }


                featuredPreview?.classList.remove(
                    'is-removing'
                );

            }
        );


        /*
|--------------------------------------------------------------------------
| Gallery Images
|--------------------------------------------------------------------------
|
| Supports:
| - Multiple uploads
| - Preview before saving
| - Remove newly selected image
| - Remove existing image
| - Undo existing image removal
| - Add more images without losing previous selection
|
*/

        const galleryInput =
            document.getElementById(
                'gallery_images'
            );

        const galleryPreview =
            document.getElementById(
                'galleryPreview'
            );

        const galleryImageCount =
            document.getElementById(
                'galleryImageCount'
            );


        /*
        |--------------------------------------------------------------------------
        | New files selected by admin
        |--------------------------------------------------------------------------
        */

        let selectedGalleryFiles = [];


        /*
        |--------------------------------------------------------------------------
        | Unique file identifier
        |--------------------------------------------------------------------------
        */

        function galleryFileKey(file) {

            return [
                file.name,
                file.size,
                file.lastModified
            ].join('::');

        }


        /*
        |--------------------------------------------------------------------------
        | Update actual <input type="file">
        |--------------------------------------------------------------------------
        |
        | Because FileList is read-only, DataTransfer is used to rebuild it after
        | an individual image is removed.
        |
        */

        function syncGalleryInput() {

            if (!galleryInput) {
                return;
            }

            const transfer =
                new DataTransfer();

            selectedGalleryFiles.forEach(
                function(file) {

                    transfer.items.add(
                        file
                    );

                }
            );

            galleryInput.files =
                transfer.files;

        }


        /*
        |--------------------------------------------------------------------------
        | Gallery count
        |--------------------------------------------------------------------------
        */

        function updateGalleryCount() {

            if (!galleryImageCount) {
                return;
            }

            const existingImages =
                document.querySelectorAll(
                    '[data-existing-gallery-image]'
                );

            let existingCount = 0;

            existingImages.forEach(
                function(image) {

                    const checkbox =
                        image.querySelector(
                            '[data-existing-remove-checkbox]'
                        );

                    if (
                        !checkbox ||
                        !checkbox.checked
                    ) {
                        existingCount++;
                    }

                }
            );

            const newCount =
                selectedGalleryFiles.length;

            const total =
                existingCount + newCount;

            if (total === 0) {

                galleryImageCount.textContent =
                    'No gallery images';

                return;
            }

            galleryImageCount.textContent =
                total +
                (
                    total === 1 ?
                    ' image' :
                    ' images'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Render newly selected images
        |--------------------------------------------------------------------------
        */

        function renderGalleryPreview() {

            if (!galleryPreview) {
                return;
            }

            galleryPreview.innerHTML = '';


            selectedGalleryFiles.forEach(
                function(file) {

                    const fileKey =
                        galleryFileKey(file);


                    const wrapper =
                        document.createElement(
                            'div'
                        );

                    wrapper.className =
                        'product-gallery-preview-item';

                    wrapper.dataset.galleryFileKey =
                        fileKey;


                    /*
                    |--------------------------------------------------------------------------
                    | Preview
                    |--------------------------------------------------------------------------
                    */

                    const image =
                        document.createElement(
                            'img'
                        );

                    const objectUrl =
                        URL.createObjectURL(
                            file
                        );

                    image.src =
                        objectUrl;

                    image.alt =
                        file.name;

                    image.onload =
                        function() {

                            URL.revokeObjectURL(
                                objectUrl
                            );

                        };


                    /*
                    |--------------------------------------------------------------------------
                    | Filename
                    |--------------------------------------------------------------------------
                    */

                    const fileName =
                        document.createElement(
                            'div'
                        );

                    fileName.className =
                        'product-gallery-file-name';

                    fileName.textContent =
                        file.name;


                    /*
                    |--------------------------------------------------------------------------
                    | Overlay
                    |--------------------------------------------------------------------------
                    */

                    const overlay =
                        document.createElement(
                            'div'
                        );

                    overlay.className =
                        'product-gallery-image-overlay';


                    /*
                    |--------------------------------------------------------------------------
                    | Remove button
                    |--------------------------------------------------------------------------
                    */

                    const removeButton =
                        document.createElement(
                            'button'
                        );

                    removeButton.type =
                        'button';

                    removeButton.className =
                        'product-gallery-remove-button';

                    removeButton.innerHTML =
                        '<i class="fa-solid fa-trash"></i>' +
                        '<span>Remove</span>';


                    removeButton.addEventListener(
                        'click',
                        function() {

                            selectedGalleryFiles =
                                selectedGalleryFiles.filter(
                                    function(
                                        selectedFile
                                    ) {

                                        return (
                                            galleryFileKey(
                                                selectedFile
                                            ) !== fileKey
                                        );

                                    }
                                );


                            syncGalleryInput();

                            renderGalleryPreview();

                            updateGalleryCount();

                        }
                    );


                    overlay.appendChild(
                        removeButton
                    );

                    wrapper.appendChild(
                        image
                    );

                    wrapper.appendChild(
                        fileName
                    );

                    wrapper.appendChild(
                        overlay
                    );

                    galleryPreview.appendChild(
                        wrapper
                    );

                }
            );


            updateGalleryCount();

        }


        /*
        |--------------------------------------------------------------------------
        | Add selected files
        |--------------------------------------------------------------------------
        */

        galleryInput?.addEventListener(
            'change',
            function() {

                const incomingFiles =
                    Array.from(
                        this.files || []
                    );


                incomingFiles.forEach(
                    function(file) {

                        const key =
                            galleryFileKey(
                                file
                            );


                        const alreadyExists =
                            selectedGalleryFiles.some(
                                function(
                                    existingFile
                                ) {

                                    return (
                                        galleryFileKey(
                                            existingFile
                                        ) === key
                                    );

                                }
                            );


                        if (!alreadyExists) {

                            selectedGalleryFiles.push(
                                file
                            );

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Keep maximum at 20 new files
                |--------------------------------------------------------------------------
                */

                if (
                    selectedGalleryFiles.length >
                    20
                ) {

                    selectedGalleryFiles =
                        selectedGalleryFiles.slice(
                            0,
                            20
                        );

                    alert(
                        'You can upload a maximum of 20 gallery images at one time.'
                    );

                }


                syncGalleryInput();

                renderGalleryPreview();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Existing Gallery Images
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-existing-gallery-image]'
            )
            .forEach(
                function(imageCard) {

                    const checkbox =
                        imageCard.querySelector(
                            '[data-existing-remove-checkbox]'
                        );

                    const removeButton =
                        imageCard.querySelector(
                            '[data-remove-existing-gallery]'
                        );

                    const undoButton =
                        imageCard.querySelector(
                            '[data-undo-gallery-remove]'
                        );

                    const removeState =
                        imageCard.querySelector(
                            '[data-gallery-remove-state]'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Apply state
                    |--------------------------------------------------------------------------
                    */

                    function applyRemovalState() {

                        const removing =
                            Boolean(
                                checkbox?.checked
                            );


                        imageCard.classList.toggle(
                            'is-removing',
                            removing
                        );


                        if (removeState) {

                            removeState.hidden = !removing;

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Mark existing image for removal
                    |--------------------------------------------------------------------------
                    */

                    removeButton?.addEventListener(
                        'click',
                        function() {

                            if (!checkbox) {
                                return;
                            }

                            checkbox.checked =
                                true;

                            applyRemovalState();

                            updateGalleryCount();

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Undo removal
                    |--------------------------------------------------------------------------
                    */

                    undoButton?.addEventListener(
                        'click',
                        function() {

                            if (!checkbox) {
                                return;
                            }

                            checkbox.checked =
                                false;

                            applyRemovalState();

                            updateGalleryCount();

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Restore state after Laravel validation redirect
                    |--------------------------------------------------------------------------
                    */

                    applyRemovalState();

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Initial count
        |--------------------------------------------------------------------------
        */

        updateGalleryCount();


        /*
        |--------------------------------------------------------------------------
        | Search categories / tags
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '[data-filter-checkboxes]'
            )
            .forEach(function(input) {

                input.addEventListener(
                    'input',
                    function() {

                        const container =
                            document.querySelector(
                                input.dataset
                                .filterCheckboxes
                            );

                        if (!container) {
                            return;
                        }

                        const query =
                            input.value
                            .trim()
                            .toLowerCase();

                        container
                            .querySelectorAll(
                                '[data-check-label]'
                            )
                            .forEach(
                                function(item) {

                                    item.style.display =
                                        item.dataset
                                        .checkLabel
                                        .includes(query) ?
                                        '' :
                                        'none';

                                }
                            );

                    }
                );

            });


        /*
        |--------------------------------------------------------------------------
        | Meta counters
        |--------------------------------------------------------------------------
        */

        function setupCounter(
            inputId,
            counterId
        ) {

            const input =
                document.getElementById(
                    inputId
                );

            const counter =
                document.getElementById(
                    counterId
                );

            if (!input || !counter) {
                return;
            }

            function update() {
                counter.textContent =
                    input.value.length;
            }

            input.addEventListener(
                'input',
                update
            );

            update();
        }

        setupCounter(
            'meta_title',
            'metaTitleCount'
        );

        setupCounter(
            'meta_description',
            'metaDescriptionCount'
        );


        /*
        |--------------------------------------------------------------------------
        | Enable option when a value is selected
        |--------------------------------------------------------------------------
        */

        function bindOptionValueCheckbox(
            checkbox
        ) {

            checkbox.addEventListener(
                'change',
                function() {

                    if (!checkbox.checked) {
                        return;
                    }

                    const card =
                        checkbox.closest(
                            '[data-option-card]'
                        );

                    card
                        ?.querySelector(
                            '[data-option-toggle]'
                        )
                        ?.setAttribute(
                            'checked',
                            'checked'
                        );

                    const optionToggle =
                        card?.querySelector(
                            '[data-option-toggle]'
                        );

                    if (optionToggle) {
                        optionToggle.checked = true;
                    }

                }
            );

        }

        document
            .querySelectorAll(
                '[data-option-value-checkbox]'
            )
            .forEach(
                bindOptionValueCheckbox
            );


        /*
        |--------------------------------------------------------------------------
        | Variant generator
        |--------------------------------------------------------------------------
        */

        const variantsContainer =
            document.getElementById(
                'variantsContainer'
            );

        let variantsState =
            Array.isArray(initialVariants) ?
            initialVariants : [];


        function getSelectedOptions() {

            const result = [];

            document
                .querySelectorAll(
                    '[data-option-card]'
                )
                .forEach(function(card) {

                    const enabled =
                        card.querySelector(
                            '[data-option-toggle]'
                        );

                    if (
                        !enabled ||
                        !enabled.checked
                    ) {
                        return;
                    }

                    const values = [];

                    card
                        .querySelectorAll(
                            '[data-option-value-checkbox]:checked'
                        )
                        .forEach(
                            function(checkbox) {

                                values.push({
                                    option_id: Number(
                                        checkbox.dataset
                                        .optionId
                                    ),

                                    option_name: checkbox.dataset
                                        .optionName,

                                    value_id: Number(
                                        checkbox.dataset
                                        .valueId
                                    ),

                                    value_label: checkbox.dataset
                                        .valueLabel
                                });

                            }
                        );

                    if (values.length) {
                        result.push(values);
                    }

                });

            return result;
        }


        function cartesianProduct(groups) {

            return groups.reduce(
                function(combinations, group) {

                    const result = [];

                    combinations.forEach(
                        function(combination) {

                            group.forEach(
                                function(item) {

                                    result.push([
                                        ...combination,
                                        item
                                    ]);

                                }
                            );

                        }
                    );

                    return result;

                },
                [
                    []
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Read current DOM before regeneration
        |--------------------------------------------------------------------------
        |
        | This is the key preservation behavior.
        | If the admin edits SKU/price/stock and then clicks Generate Variants,
        | the current values are retained for matching combinations.
        |--------------------------------------------------------------------------
        */

        function captureCurrentVariants() {

            const captured = [];

            variantsContainer
                .querySelectorAll(
                    '[data-variant-row]'
                )
                .forEach(function(row) {

                    const optionsJson =
                        row.dataset.options;

                    let options = [];

                    try {
                        options =
                            JSON.parse(
                                optionsJson || '[]'
                            );
                    } catch (error) {
                        options = [];
                    }

                    captured.push({
                        id: row.querySelector(
                            '[data-variant-id]'
                        )?.value || null,

                        sku: row.querySelector(
                            '[data-variant-sku]'
                        )?.value || '',

                        regular_price: row.querySelector(
                            '[data-variant-regular]'
                        )?.value || '',

                        sale_price: row.querySelector(
                            '[data-variant-sale]'
                        )?.value || '',

                        stock: row.querySelector(
                            '[data-variant-stock]'
                        )?.value || '0',

                        reorder_point: row.querySelector(
                            '[data-variant-reorder-point]'
                        )?.value || '',

                        reorder_quantity: row.querySelector(
                            '[data-variant-reorder-quantity]'
                        )?.value || '',

                        image: row.dataset
                            .existingImage || null,

                        remove_image: row.querySelector(
                            '[data-variant-remove-image]'
                        )?.value === '1',

                        options: options
                    });

                });

            if (captured.length) {
                variantsState = captured;
            }
        }


        function renderVariants(
            variants
        ) {

            variantsContainer.innerHTML = '';

            if (!variants.length) {

                variantsContainer.innerHTML = `
                <div class="product-variants-empty">
                    <i class="fa-solid fa-code-branch"></i>
                    <strong>No variants generated</strong>
                    <span>
                        Select option values above and click Generate Variants.
                    </span>
                </div>
            `;

                return;
            }

            variants.forEach(
                function(variant, index) {

                    const options =
                        Array.isArray(
                            variant.options
                        ) ?
                        variant.options : [];

                    const title =
                        options
                        .map(
                            option =>
                            option.value_label
                        )
                        .join(' / ');

                    const row =
                        document.createElement(
                            'div'
                        );

                    row.className =
                        'product-variant-row';

                    row.dataset.variantRow = '';
                    row.dataset.options =
                        JSON.stringify(options);

                    row.dataset.existingImage =
                        variant.image || '';

                    const optionInputs =
                        options
                        .map(
                            function(
                                option,
                                optionIndex
                            ) {

                                return `
                                    <input
                                        type="hidden"
                                        name="variants[${index}][options][${optionIndex}][option_id]"
                                        value="${escapeHtml(option.option_id)}"
                                    >

                                    <input
                                        type="hidden"
                                        name="variants[${index}][options][${optionIndex}][option_name]"
                                        value="${escapeHtml(option.option_name)}"
                                    >

                                    <input
                                        type="hidden"
                                        name="variants[${index}][options][${optionIndex}][value_id]"
                                        value="${escapeHtml(option.value_id)}"
                                    >

                                    <input
                                        type="hidden"
                                        name="variants[${index}][options][${optionIndex}][value_label]"
                                        value="${escapeHtml(option.value_label)}"
                                    >
                                `;

                            }
                        )
                        .join('');

                    const signature =
                        optionSignature(options);

                    const hasExistingImage =
                        Boolean(variant.image);

                    const removingImage =
                        Boolean(variant.remove_image);

                    const previewMarkup =
                        hasExistingImage && !removingImage
                            ? `
                                <img
                                    src="${escapeHtml(normaliseImageUrl(variant.image))}"
                                    alt="${escapeHtml(title || 'Variant image')}"
                                    data-variant-preview-image
                                >
                            `
                            : removingImage
                                ? `
                                    <div class="product-variant-image-empty is-removing">
                                        <i class="fa-solid fa-trash"></i>
                                        <span>Will be removed</span>
                                    </div>
                                `
                                : `
                                    <div class="product-variant-image-empty">
                                        <i class="fa-regular fa-image"></i>
                                        <span>No image</span>
                                    </div>
                                `;

                    row.innerHTML = `

                    ${variant.id ? `
                        <input
                            type="hidden"
                            name="variants[${index}][id]"
                            value="${escapeHtml(variant.id)}"
                            data-variant-id
                        >
                    ` : `
                        <input
                            type="hidden"
                            value=""
                            data-variant-id
                        >
                    `}

                    ${optionInputs}

                    <input
                        type="hidden"
                        name="variants[${index}][old_image]"
                        value="${escapeHtml(variant.image || '')}"
                    >

                    <input
                        type="hidden"
                        name="variants[${index}][remove_image]"
                        value="${variant.remove_image ? '1' : '0'}"
                        data-variant-remove-image
                    >

                    <div class="product-variant-heading">

                        <strong>
                            ${escapeHtml(title || 'Variant')}
                        </strong>

                        <button
                            type="button"
                            class="product-variant-remove"
                            data-remove-variant
                        >
                            <i class="fa-solid fa-trash"></i>
                            Remove
                        </button>

                    </div>


                    <div class="product-variant-fields">

                        <div class="product-variant-field">

                            <label>SKU</label>

                            <input
                                type="text"
                                name="variants[${index}][sku]"
                                value="${escapeHtml(variant.sku || '')}"
                                maxlength="100"
                                data-variant-sku
                            >

                        </div>


                        <div class="product-variant-field">

                            <label>Regular Price</label>

                            <input
                                type="number"
                                name="variants[${index}][regular_price]"
                                value="${escapeHtml(variant.regular_price ?? '')}"
                                min="0"
                                step="0.01"
                                data-variant-regular
                            >

                        </div>


                        <div class="product-variant-field">

                            <label>Sale Price</label>

                            <input
                                type="number"
                                name="variants[${index}][sale_price]"
                                value="${escapeHtml(variant.sale_price ?? '')}"
                                min="0"
                                step="0.01"
                                data-variant-sale
                            >

                        </div>


                        <div class="product-variant-field">

                            <label>Stock</label>

                            <input
                                type="number"
                                name="variants[${index}][stock]"
                                value="${escapeHtml(variant.stock ?? 0)}"
                                min="0"
                                step="1"
                                data-variant-stock
                            >

                        </div>


                        <div class="product-variant-field">

                            <label>Reorder Point</label>

                            <input
                                type="number"
                                name="variants[${index}][reorder_point]"
                                value="${escapeHtml(variant.reorder_point ?? '')}"
                                min="0"
                                step="1"
                                data-variant-reorder-point
                            >

                        </div>


                        <div class="product-variant-field">

                            <label>Reorder Qty</label>

                            <input
                                type="number"
                                name="variants[${index}][reorder_quantity]"
                                value="${escapeHtml(variant.reorder_quantity ?? '')}"
                                min="1"
                                step="1"
                                data-variant-reorder-quantity
                            >

                        </div>


                        <div class="product-variant-field">

                            <label>Image</label>

                            <div
                                class="product-variant-image-input"
                                data-variant-media
                                data-variant-signature="${escapeHtml(signature)}"
                            >

                                <div
                                    class="product-variant-image-preview"
                                    data-variant-image-preview
                                >
                                    ${previewMarkup}
                                </div>

                                <div class="product-variant-image-actions">

                                    <label class="product-variant-image-action">
                                        <i class="fa-solid fa-image"></i>

                                        <span data-variant-image-choose-text>
                                            ${hasExistingImage && !removingImage ? 'Replace' : 'Choose'}
                                        </span>

                                        <input
                                            type="file"
                                            name="variants[${index}][image]"
                                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                            data-variant-image-picker
                                            hidden
                                        >
                                    </label>

                                    <button
                                        type="button"
                                        class="product-variant-image-action is-danger"
                                        data-remove-variant-image
                                        ${(!hasExistingImage || removingImage) ? 'hidden' : ''}
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        Remove
                                    </button>

                                    <button
                                        type="button"
                                        class="product-variant-image-action is-undo"
                                        data-undo-variant-image
                                        ${removingImage ? '' : 'hidden'}
                                    >
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Undo
                                    </button>

                                    <button
                                        type="button"
                                        class="product-variant-image-action is-danger"
                                        data-clear-new-variant-image
                                        hidden
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                        Clear
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>
                `;

                    variantsContainer.appendChild(
                        row
                    );

                }
            );

            bindVariantRemoveButtons();
        }


        function buildVariantSku(options) {

            const base = slugify(
                document.getElementById('sku')?.value ||
                document.getElementById('title')?.value ||
                'product'
            )
                .replace(/-/g, '')
                .toUpperCase()
                .slice(0, 18) || 'PRODUCT';

            const suffix = options
                .map(option => slugify(option.value_label || option.value_id))
                .filter(Boolean)
                .map(value => value.replace(/-/g, '').toUpperCase().slice(0, 8))
                .join('-');

            return suffix ? `${base}-${suffix}` : base;
        }


        function generateVariants() {

            captureCurrentVariants();

            const selectedGroups =
                getSelectedOptions();

            if (!selectedGroups.length) {

                alert(
                    'Select at least one option and one value before generating variants.'
                );

                return;
            }

            const combinations =
                cartesianProduct(
                    selectedGroups
                );

            const existingBySignature =
                new Map();

            variantsState.forEach(
                function(variant) {

                    const signature =
                        optionSignature(
                            variant.options || []
                        );

                    if (signature) {
                        existingBySignature.set(
                            signature,
                            variant
                        );
                    }

                }
            );

            const generated =
                combinations.map(
                    function(options) {

                        const signature =
                            optionSignature(
                                options
                            );

                        const existing =
                            existingBySignature.get(
                                signature
                            );

                        if (existing) {

                            return {
                                ...existing,
                                options: options
                            };

                        }

                        return {
                            id: null,
                            sku: buildVariantSku(options),
                            regular_price: regularPrice?.value || '',
                            sale_price: salePrice?.value || '',
                            stock: document.getElementById('stock')?.value || 0,
                            reorder_point: document.getElementById('reorder_point')?.value || '',
                            reorder_quantity: document.getElementById('reorder_quantity')?.value || '',
                            image: null,
                            remove_image: false,
                            options: options
                        };

                    }
                );

            variantsState = generated;

            renderVariants(
                variantsState
            );
        }


        document
            .getElementById(
                'generateVariants'
            )
            ?.addEventListener(
                'click',
                generateVariants
            );


        /*
        |--------------------------------------------------------------------------
        | Variant image file state
        |--------------------------------------------------------------------------
        |
        | File inputs normally lose selected files when variant rows are rendered
        | again. Store selected File objects by variant signature so Generate
        | Variants can safely re-render matching rows without losing the upload.
        |--------------------------------------------------------------------------
        */

        const variantImageFiles =
            new Map();


        function restoreVariantImageFile(
            input,
            signature
        ) {

            const file =
                variantImageFiles.get(
                    signature
                );

            if (
                !file ||
                !input
            ) {
                return;
            }

            try {

                const transfer =
                    new DataTransfer();

                transfer.items.add(
                    file
                );

                input.files =
                    transfer.files;

            } catch (error) {
                /*
                 * Modern Chrome / Edge support DataTransfer assignment.
                 * If another browser blocks it, the visual preview remains,
                 * but the admin will need to choose the file again.
                 */
            }
        }


        function setVariantImagePreview(
            preview,
            file
        ) {

            if (
                !preview ||
                !file
            ) {
                return;
            }

            const objectUrl =
                URL.createObjectURL(
                    file
                );

            preview.innerHTML = `
                <img
                    src="${objectUrl}"
                    alt="${escapeHtml(file.name)}"
                    data-variant-preview-image
                >
            `;

            const image =
                preview.querySelector(
                    '[data-variant-preview-image]'
                );

            image?.addEventListener(
                'load',
                function() {
                    URL.revokeObjectURL(
                        objectUrl
                    );
                },
                {
                    once: true
                }
            );
        }


        function bindVariantImageControls() {

            variantsContainer
                .querySelectorAll(
                    '[data-variant-media]'
                )
                .forEach(
                    function(media) {

                        const row =
                            media.closest(
                                '[data-variant-row]'
                            );

                        const signature =
                            media.dataset
                                .variantSignature || '';

                        const picker =
                            media.querySelector(
                                '[data-variant-image-picker]'
                            );

                        const preview =
                            media.querySelector(
                                '[data-variant-image-preview]'
                            );

                        const removeFlag =
                            row?.querySelector(
                                '[data-variant-remove-image]'
                            );

                        const removeButton =
                            media.querySelector(
                                '[data-remove-variant-image]'
                            );

                        const undoButton =
                            media.querySelector(
                                '[data-undo-variant-image]'
                            );

                        const clearButton =
                            media.querySelector(
                                '[data-clear-new-variant-image]'
                            );

                        const chooseText =
                            media.querySelector(
                                '[data-variant-image-choose-text]'
                            );

                        const existingImage =
                            row?.dataset
                                .existingImage || '';

                        /*
                        |--------------------------------------------------------------------------
                        | Restore newly selected file after regeneration
                        |--------------------------------------------------------------------------
                        */

                        const rememberedFile =
                            variantImageFiles.get(
                                signature
                            );

                        if (
                            rememberedFile &&
                            picker
                        ) {

                            restoreVariantImageFile(
                                picker,
                                signature
                            );

                            setVariantImagePreview(
                                preview,
                                rememberedFile
                            );

                            if (removeFlag) {
                                removeFlag.value =
                                    '0';
                            }

                            if (chooseText) {
                                chooseText.textContent =
                                    'Replace';
                            }

                            if (removeButton) {
                                removeButton.hidden =
                                    true;
                            }

                            if (undoButton) {
                                undoButton.hidden =
                                    true;
                            }

                            if (clearButton) {
                                clearButton.hidden =
                                    false;
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Choose / Replace
                        |--------------------------------------------------------------------------
                        */

                        picker?.addEventListener(
                            'change',
                            function() {

                                const file =
                                    this.files?.[0];

                                if (!file) {
                                    return;
                                }

                                variantImageFiles.set(
                                    signature,
                                    file
                                );

                                if (removeFlag) {
                                    removeFlag.value =
                                        '0';
                                }

                                setVariantImagePreview(
                                    preview,
                                    file
                                );

                                if (chooseText) {
                                    chooseText.textContent =
                                        'Replace';
                                }

                                if (removeButton) {
                                    removeButton.hidden =
                                        true;
                                }

                                if (undoButton) {
                                    undoButton.hidden =
                                        true;
                                }

                                if (clearButton) {
                                    clearButton.hidden =
                                        false;
                                }
                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Remove existing database image
                        |--------------------------------------------------------------------------
                        */

                        removeButton?.addEventListener(
                            'click',
                            function() {

                                variantImageFiles.delete(
                                    signature
                                );

                                if (picker) {
                                    picker.value = '';
                                }

                                if (removeFlag) {
                                    removeFlag.value =
                                        '1';
                                }

                                if (preview) {
                                    preview.innerHTML = `
                                        <div class="product-variant-image-empty is-removing">
                                            <i class="fa-solid fa-trash"></i>
                                            <span>Will be removed</span>
                                        </div>
                                    `;
                                }

                                removeButton.hidden =
                                    true;

                                if (undoButton) {
                                    undoButton.hidden =
                                        false;
                                }

                                if (clearButton) {
                                    clearButton.hidden =
                                        true;
                                }

                                if (chooseText) {
                                    chooseText.textContent =
                                        'Choose';
                                }
                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Undo existing image removal
                        |--------------------------------------------------------------------------
                        */

                        undoButton?.addEventListener(
                            'click',
                            function() {

                                if (removeFlag) {
                                    removeFlag.value =
                                        '0';
                                }

                                if (
                                    preview &&
                                    existingImage
                                ) {

                                    preview.innerHTML = `
                                        <img
                                            src="${escapeHtml(normaliseImageUrl(existingImage))}"
                                            alt="Variant image"
                                            data-variant-preview-image
                                        >
                                    `;
                                }

                                undoButton.hidden =
                                    true;

                                if (removeButton) {
                                    removeButton.hidden =
                                        !existingImage;
                                }

                                if (clearButton) {
                                    clearButton.hidden =
                                        true;
                                }

                                if (chooseText) {
                                    chooseText.textContent =
                                        existingImage
                                            ? 'Replace'
                                            : 'Choose';
                                }
                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Clear newly selected image
                        |--------------------------------------------------------------------------
                        */

                        clearButton?.addEventListener(
                            'click',
                            function() {

                                variantImageFiles.delete(
                                    signature
                                );

                                if (picker) {
                                    picker.value = '';
                                }

                                if (removeFlag) {
                                    removeFlag.value =
                                        '0';
                                }

                                if (
                                    preview &&
                                    existingImage
                                ) {

                                    preview.innerHTML = `
                                        <img
                                            src="${escapeHtml(normaliseImageUrl(existingImage))}"
                                            alt="Variant image"
                                            data-variant-preview-image
                                        >
                                    `;

                                } else if (preview) {

                                    preview.innerHTML = `
                                        <div class="product-variant-image-empty">
                                            <i class="fa-regular fa-image"></i>
                                            <span>No image</span>
                                        </div>
                                    `;
                                }

                                clearButton.hidden =
                                    true;

                                if (removeButton) {
                                    removeButton.hidden =
                                        !existingImage;
                                }

                                if (undoButton) {
                                    undoButton.hidden =
                                        true;
                                }

                                if (chooseText) {
                                    chooseText.textContent =
                                        existingImage
                                            ? 'Replace'
                                            : 'Choose';
                                }
                            }
                        );

                    }
                );
        }


        function bindVariantRemoveButtons() {

            bindVariantImageControls();

            variantsContainer
                .querySelectorAll(
                    '[data-remove-variant]'
                )
                .forEach(function(button) {

                    button.addEventListener(
                        'click',
                        function() {

                            const row =
                                button.closest(
                                    '[data-variant-row]'
                                );

                            if (row) {

                                let options = [];

                                try {
                                    options =
                                        JSON.parse(
                                            row.dataset.options ||
                                            '[]'
                                        );
                                } catch (error) {
                                    options = [];
                                }

                                const signature =
                                    optionSignature(
                                        options
                                    );

                                variantImageFiles.delete(
                                    signature
                                );

                                row.remove();
                            }

                            captureCurrentVariants();

                            if (
                                !variantsContainer
                                .querySelector(
                                    '[data-variant-row]'
                                )
                            ) {
                                renderVariants([]);
                            }

                        }
                    );

                });

        }


        /*
        |--------------------------------------------------------------------------
        | Render existing / validation variants immediately
        |--------------------------------------------------------------------------
        */

        renderVariants(
            variantsState
        );


        /*
        |--------------------------------------------------------------------------
        | Modal helpers
        |--------------------------------------------------------------------------
        */

        function openModal(modal) {

            modal?.classList.add(
                'is-open'
            );

            modal?.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.style
                .overflow = 'hidden';
        }


        function closeModal(modal) {

            modal?.classList.remove(
                'is-open'
            );

            modal?.setAttribute(
                'aria-hidden',
                'true'
            );

            document.body.style
                .overflow = '';
        }


        const optionModal =
            document.getElementById(
                'newOptionModal'
            );

        const valueModal =
            document.getElementById(
                'newValueModal'
            );


        document
            .getElementById(
                'openNewOptionModal'
            )
            ?.addEventListener(
                'click',
                function() {

                    openModal(
                        optionModal
                    );

                }
            );


        document
            .querySelectorAll(
                '[data-close-option-modal]'
            )
            .forEach(function(button) {

                button.addEventListener(
                    'click',
                    function() {

                        closeModal(
                            optionModal
                        );

                    }
                );

            });


        document
            .querySelectorAll(
                '[data-close-value-modal]'
            )
            .forEach(function(button) {

                button.addEventListener(
                    'click',
                    function() {

                        closeModal(
                            valueModal
                        );

                    }
                );

            });


        /*
        |--------------------------------------------------------------------------
        | Add Value modal
        |--------------------------------------------------------------------------
        */

        function bindAddValueButtons() {

            document
                .querySelectorAll(
                    '[data-add-value]'
                )
                .forEach(function(button) {

                    if (
                        button.dataset.bound ===
                        '1'
                    ) {
                        return;
                    }

                    button.dataset.bound = '1';

                    button.addEventListener(
                        'click',
                        function() {

                            document.getElementById(
                                    'newValueOptionId'
                                ).value =
                                button.dataset
                                .optionId;

                            document.getElementById(
                                    'newValueModalTitle'
                                ).textContent =
                                'Add ' +
                                button.dataset
                                .optionName +
                                ' Value';

                            document.getElementById(
                                'newValueLabel'
                            ).value = '';

                            document.getElementById(
                                'newValueValue'
                            ).value = '';

                            document.getElementById(
                                'newValueColor'
                            ).value = '';

                            document.getElementById(
                                    'newValueMessage'
                                ).className =
                                'product-modal-message';

                            openModal(
                                valueModal
                            );

                        }
                    );

                });

        }

        bindAddValueButtons();


        /*
        |--------------------------------------------------------------------------
        | Create new option
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'saveNewOption'
            )
            ?.addEventListener(
                'click',
                async function() {

                    const button = this;

                    const name =
                        document.getElementById(
                            'newOptionName'
                        ).value.trim();

                    const type =
                        document.getElementById(
                            'newOptionType'
                        ).value;

                    const message =
                        document.getElementById(
                            'newOptionMessage'
                        );

                    if (!name) {

                        message.className =
                            'product-modal-message is-error';

                        message.textContent =
                            'Please enter an option name.';

                        return;
                    }

                    button.disabled = true;

                    try {

                        const response =
                            await fetch(
                                optionStoreUrl, {
                                    method: 'POST',

                                    headers: {
                                        'Accept': 'application/json',

                                        'Content-Type': 'application/json',

                                        'X-CSRF-TOKEN': csrfToken
                                    },

                                    body: JSON.stringify({
                                        name: name,
                                        type: type
                                    })
                                }
                            );

                        const data =
                            await response.json();

                        if (!response.ok) {

                            throw new Error(
                                data.message ||
                                Object.values(
                                    data.errors || {}
                                ).flat()[0] ||
                                'Unable to create option.'
                            );

                        }

                        const option =
                            data.option ?? data.product_option ?? data;

                        appendOptionCard(
                            option
                        );

                        closeModal(
                            optionModal
                        );

                        document.getElementById(
                            'newOptionName'
                        ).value = '';

                    } catch (error) {

                        message.className =
                            'product-modal-message is-error';

                        message.textContent =
                            error.message;

                    } finally {

                        button.disabled = false;

                    }

                }
            );


        function appendOptionCard(
            option
        ) {

            document.getElementById(
                'noProductOptions'
            )?.remove();

            const container =
                document.getElementById(
                    'productOptionsList'
                );

            const card =
                document.createElement(
                    'div'
                );

            card.className =
                'product-option-card';

            card.dataset.optionCard = '';
            card.dataset.optionId =
                option.id;

            card.dataset.optionName =
                option.name;

            card.innerHTML = `

            <div class="product-option-heading">

                <div class="product-option-title">

                    <label class="product-option-enable">

                        <input
                            type="checkbox"
                            name="product_options[]"
                            value="${escapeHtml(option.id)}"
                            data-option-toggle
                            checked
                        >

                        <span>
                            ${escapeHtml(option.name)}
                        </span>

                    </label>

                    <span class="product-option-type">
                        ${escapeHtml(option.type || 'select')}
                    </span>

                </div>

                <button
                    type="button"
                    class="product-add-value-button"
                    data-add-value
                    data-option-id="${escapeHtml(option.id)}"
                    data-option-name="${escapeHtml(option.name)}"
                >
                    <i class="fa-solid fa-plus"></i>
                    Add Value
                </button>

            </div>

            <div class="product-option-values">

                <span
                    class="product-option-empty"
                    data-option-empty
                >
                    No values yet.
                </span>

            </div>
        `;

            container.appendChild(
                card
            );

            bindAddValueButtons();
        }


        /*
        |--------------------------------------------------------------------------
        | Create new option value
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                'saveNewValue'
            )
            ?.addEventListener(
                'click',
                async function() {

                    const button = this;

                    const optionId =
                        document.getElementById(
                            'newValueOptionId'
                        ).value;

                    const label =
                        document.getElementById(
                            'newValueLabel'
                        ).value.trim();

                    const value =
                        document.getElementById(
                            'newValueValue'
                        ).value.trim();

                    const colorCode =
                        document.getElementById(
                            'newValueColor'
                        ).value.trim();

                    const message =
                        document.getElementById(
                            'newValueMessage'
                        );

                    if (!label) {

                        message.className =
                            'product-modal-message is-error';

                        message.textContent =
                            'Please enter a value label.';

                        return;
                    }

                    button.disabled = true;

                    try {

                        const url =
                            optionValueStoreUrlTemplate
                            .replace(
                                '__OPTION_ID__',
                                optionId
                            );

                        const response =
                            await fetch(
                                url, {
                                    method: 'POST',

                                    headers: {
                                        'Accept': 'application/json',

                                        'Content-Type': 'application/json',

                                        'X-CSRF-TOKEN': csrfToken
                                    },

                                    body: JSON.stringify({
                                        label: label,
                                        value: value || null,
                                        color_code: colorCode || null
                                    })
                                }
                            );

                        const data =
                            await response.json();

                        if (!response.ok) {

                            throw new Error(
                                data.message ||
                                Object.values(
                                    data.errors || {}
                                ).flat()[0] ||
                                'Unable to create value.'
                            );

                        }

                        const optionValue =
                            data.value ?? data.option_value ?? data;

                        appendOptionValue(
                            optionId,
                            optionValue
                        );

                        closeModal(
                            valueModal
                        );

                    } catch (error) {

                        message.className =
                            'product-modal-message is-error';

                        message.textContent =
                            error.message;

                    } finally {

                        button.disabled = false;

                    }

                }
            );


        function appendOptionValue(
            optionId,
            value
        ) {

            const card =
                document.querySelector(
                    `[data-option-card][data-option-id="${CSS.escape(String(optionId))}"]`
                );

            if (!card) {
                return;
            }

            card.querySelector(
                '[data-option-empty]'
            )?.remove();

            const valuesContainer =
                card.querySelector(
                    '.product-option-values'
                );

            const optionName =
                card.dataset.optionName;

            const label =
                document.createElement(
                    'label'
                );

            label.className =
                'product-option-value';

            label.dataset.optionValue = '';

            const colorSwatch =
                value.color_code ?
                `
                    <span
                        class="product-color-swatch"
                        style="background:${escapeHtml(value.color_code)}"
                    ></span>
                ` :
                '';

            label.innerHTML = `

            <input
                type="checkbox"
                name="product_option_values[]"
                value="${escapeHtml(value.id)}"
                data-option-value-checkbox
                data-option-id="${escapeHtml(optionId)}"
                data-option-name="${escapeHtml(optionName)}"
                data-value-id="${escapeHtml(value.id)}"
                data-value-label="${escapeHtml(value.label)}"
                checked
            >

            ${colorSwatch}

            <span>
                ${escapeHtml(value.label)}
            </span>
        `;

            valuesContainer.appendChild(
                label
            );

            const checkbox =
                label.querySelector(
                    '[data-option-value-checkbox]'
                );

            bindOptionValueCheckbox(
                checkbox
            );

            const optionToggle =
                card.querySelector(
                    '[data-option-toggle]'
                );

            if (optionToggle) {
                optionToggle.checked = true;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Form submit
        |--------------------------------------------------------------------------
        */

        const form =
            document.querySelector(
                'form[data-product-form]'
            );

        form?.addEventListener(
            'submit',
            function() {

                captureCurrentVariants();

                const submitButton =
                    form.querySelector(
                        '[data-product-submit]'
                    );

                if (submitButton) {

                    submitButton.disabled = true;

                    submitButton.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

                }

            }
        );

    });
</script>

@endpush