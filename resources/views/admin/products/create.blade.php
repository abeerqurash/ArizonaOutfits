@extends('layouts.app')

@section('title', 'Add Product')

@section('content')

@php
$selectedCategoryIds = array_map(
'strval',
old('categories', [])
);

$selectedTagIds = array_map(
'strval',
old('tags', [])
);

$selectedOptionIds = array_map(
'strval',
old('product_options', [])
);

$selectedOptionValueIds = array_map(
'strval',
old('product_option_values', [])
);

$oldVariants = old('variants', []);
@endphp

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">

            <div class="container">

                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:20px;
                        flex-wrap:wrap;
                        margin-bottom:25px;
                    ">
                    <div>
                        <h1 style="margin:0 0 5px;">
                            Add Product
                        </h1>

                        <p style="margin:0;color:#666;">
                            Create a product with categories, tags, attributes and variants.
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.products.index') }}"
                        style="
                            padding:10px 18px;
                            background:#eee;
                            color:#222;
                            text-decoration:none;
                            border-radius:5px;
                        ">
                        Back to Products
                    </a>
                </div>

                @if ($errors->any())

                <div
                    style="
                            padding:15px;
                            background:#f8d7da;
                            color:#721c24;
                            border:1px solid #f5c6cb;
                            border-radius:5px;
                            margin-bottom:20px;
                        ">
                    <strong>
                        Please fix the following errors:
                    </strong>

                    <ul style="margin:10px 0 0;padding-left:20px;">
                        @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                @endif

                @if (session('error'))

                <div
                    style="
                            padding:15px;
                            background:#f8d7da;
                            color:#721c24;
                            border-radius:5px;
                            margin-bottom:20px;
                        ">
                    {{ session('error') }}
                </div>

                @endif

                <form
                    action="{{ route('admin.products.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="product-form">
                    @csrf

                    <div
                        style="
                            display:grid;
                            grid-template-columns:minmax(0, 2fr) minmax(280px, 1fr);
                            gap:25px;
                            align-items:start;
                        ">

                        {{-- LEFT COLUMN --}}
                        <div>

                            {{-- BASIC INFORMATION --}}
                            <div style="{{ $cardStyle = 'background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;margin-bottom:20px;' }}">

                                <h2 style="margin-top:0;">
                                    Basic Information
                                </h2>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="title"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Product Title *
                                    </label>

                                    <input
                                        type="text"
                                        id="title"
                                        name="title"
                                        value="{{ old('title') }}"
                                        required
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">

                                    @error('title')
                                    <small style="color:red;">
                                        {{ $message }}
                                    </small>
                                    @enderror
                                </div>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="slug"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Slug *
                                    </label>

                                    <input
                                        type="text"
                                        id="slug"
                                        name="slug"
                                        value="{{ old('slug') }}"
                                        required
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">

                                    @error('slug')
                                    <small style="color:red;">
                                        {{ $message }}
                                    </small>
                                    @enderror
                                </div>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="sku"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Product SKU
                                    </label>

                                    <input
                                        type="text"
                                        id="sku"
                                        name="sku"
                                        value="{{ old('sku') }}"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">
                                </div>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="short_description"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Short Description
                                    </label>

                                    <textarea
                                        id="short_description"
                                        name="short_description"
                                        rows="4"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">{{ old('short_description') }}</textarea>
                                </div>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="long_description"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Long Description
                                    </label>

                                    <textarea
                                        id="long_description"
                                        name="long_description"
                                        rows="8"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">{{ old('long_description') }}</textarea>
                                </div>

                                <div>
                                    <label
                                        for="additional_info"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Additional Information
                                    </label>

                                    <textarea
                                        id="additional_info"
                                        name="additional_info"
                                        rows="5"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">{{ old('additional_info') }}</textarea>
                                </div>

                            </div>

                            {{-- PRICING AND STOCK --}}
                            <div style="{{ $cardStyle }}">

                                <h2 style="margin-top:0;">
                                    Pricing and Stock
                                </h2>

                                <div
                                    style="
                                        display:grid;
                                        grid-template-columns:repeat(2, minmax(0, 1fr));
                                        gap:15px;
                                    ">
                                    <div class="form-group">
                                        <label for="cost_price">
                                            Cost Price
                                        </label>

                                        <input
                                            type="number"
                                            name="cost_price"
                                            id="cost_price"
                                            class="form-control @error('cost_price') is-invalid @enderror"
                                            step="0.01"
                                            min="0"
                                            value="{{ old('cost_price', 0) }}"
                                            required>

                                        @error('cost_price')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div>
                                        <label
                                            for="regular_price"
                                            style="display:block;font-weight:bold;margin-bottom:6px;">
                                            Regular Price *
                                        </label>

                                        <input
                                            type="number"
                                            id="regular_price"
                                            name="regular_price"
                                            value="{{ old('regular_price') }}"
                                            min="0"
                                            step="0.01"
                                            required
                                            style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">
                                    </div>

                                    <div>
                                        <label
                                            for="sale_price"
                                            style="display:block;font-weight:bold;margin-bottom:6px;">
                                            Sale Price
                                        </label>

                                        <input
                                            type="number"
                                            id="sale_price"
                                            name="sale_price"
                                            value="{{ old('sale_price') }}"
                                            min="0"
                                            step="0.01"
                                            style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">
                                    </div>

                                    <div>
                                        <label
                                            for="stock"
                                            style="display:block;font-weight:bold;margin-bottom:6px;">
                                            Main Product Stock
                                        </label>

                                        <input
                                            type="number"
                                            id="stock"
                                            name="stock"
                                            value="{{ old('stock', 0) }}"
                                            min="0"
                                            step="1"
                                            style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">
                                    </div>

                                    <div>
                                        <label
                                            for="status"
                                            style="display:block;font-weight:bold;margin-bottom:6px;">
                                            Status *
                                        </label>

                                        <select
                                            id="status"
                                            name="status"
                                            required
                                            style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">
                                            <option
                                                value="active"
                                                {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                                Active
                                            </option>

                                            <option
                                                value="draft"
                                                {{ old('status') === 'draft' ? 'selected' : '' }}>
                                                Draft
                                            </option>

                                            <option
                                                value="inactive"
                                                {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            {{-- ATTRIBUTES --}}
                            <div style="{{ $cardStyle }}">

                                <div
                                    style="
                                        display:flex;
                                        justify-content:space-between;
                                        align-items:center;
                                        gap:15px;
                                        flex-wrap:wrap;
                                        margin-bottom:15px;
                                    ">
                                    <div>
                                        <h2 style="margin:0 0 5px;">
                                            Product Attributes
                                        </h2>

                                        <p style="margin:0;color:#666;">
                                            Select attributes and then select their values.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        id="show-add-attribute"
                                        style="
                                            padding:9px 14px;
                                            background:#000;
                                            color:#fff;
                                            border:0;
                                            border-radius:5px;
                                            cursor:pointer;
                                        ">
                                        Add New Attribute
                                    </button>
                                </div>

                                <div
                                    id="new-attribute-form"
                                    style="
                                        display:none;
                                        padding:15px;
                                        background:#f7f7f7;
                                        border:1px solid #ddd;
                                        border-radius:6px;
                                        margin-bottom:20px;
                                    ">
                                    <div
                                        style="
                                            display:grid;
                                            grid-template-columns:2fr 1fr auto;
                                            gap:10px;
                                            align-items:end;
                                        ">
                                        <div>
                                            <label
                                                for="new-attribute-name"
                                                style="display:block;font-weight:bold;margin-bottom:5px;">
                                                Attribute Name
                                            </label>

                                            <input
                                                type="text"
                                                id="new-attribute-name"
                                                placeholder="Example: Size"
                                                style="width:100%;padding:10px;border:1px solid #ccc;border-radius:5px;">
                                        </div>

                                        <div>
                                            <label
                                                for="new-attribute-type"
                                                style="display:block;font-weight:bold;margin-bottom:5px;">
                                                Type
                                            </label>

                                            <select
                                                id="new-attribute-type"
                                                style="width:100%;padding:10px;border:1px solid #ccc;border-radius:5px;">
                                                <option value="text">
                                                    Text
                                                </option>

                                                <option value="select">
                                                    Select
                                                </option>

                                                <option value="color">
                                                    Color
                                                </option>
                                            </select>
                                        </div>

                                        <button
                                            type="button"
                                            id="save-new-attribute"
                                            style="
                                                padding:10px 15px;
                                                background:#198754;
                                                color:#fff;
                                                border:0;
                                                border-radius:5px;
                                                cursor:pointer;
                                            ">
                                            Save
                                        </button>
                                    </div>

                                    <div
                                        id="attribute-message"
                                        style="margin-top:10px;"></div>
                                </div>

                                <div id="product-options-container">

                                    @forelse ($productOptions as $option)

                                    @php
                                    $optionChecked = in_array(
                                    (string) $option->id,
                                    $selectedOptionIds,
                                    true
                                    );

                                    $hasSelectedValues = $option->values
                                    ->pluck('id')
                                    ->map(fn ($id) => (string) $id)
                                    ->intersect($selectedOptionValueIds)
                                    ->isNotEmpty();

                                    $showValues = $optionChecked || $hasSelectedValues;
                                    @endphp

                                    <div
                                        class="product-option-box"
                                        data-option-id="{{ $option->id }}"
                                        data-option-name="{{ $option->name }}"
                                        style="
                                                border:1px solid #ddd;
                                                border-radius:7px;
                                                margin-bottom:15px;
                                                overflow:hidden;
                                            ">
                                        <div
                                            style="
                                                    display:flex;
                                                    justify-content:space-between;
                                                    align-items:center;
                                                    gap:15px;
                                                    padding:13px 15px;
                                                    background:#f7f7f7;
                                                ">
                                            <label
                                                style="
                                                        display:flex;
                                                        align-items:center;
                                                        gap:8px;
                                                        cursor:pointer;
                                                        font-weight:bold;
                                                    ">
                                                <input
                                                    type="checkbox"
                                                    class="enable-option-checkbox"
                                                    name="product_options[]"
                                                    value="{{ $option->id }}"
                                                    {{ $showValues ? 'checked' : '' }}>

                                                <span>
                                                    {{ $option->name }}
                                                </span>

                                                <small style="color:#777;font-weight:normal;">
                                                    ({{ ucfirst($option->type) }})
                                                </small>
                                            </label>

                                            <button
                                                type="button"
                                                class="show-add-value-button"
                                                style="
                                                        padding:7px 10px;
                                                        border:1px solid #bbb;
                                                        background:#fff;
                                                        border-radius:4px;
                                                        cursor:pointer;
                                                    ">
                                                Add Value
                                            </button>
                                        </div>

                                        <div
                                            class="option-values-container"
                                            style="
                                                    display:{{ $showValues ? 'block' : 'none' }};
                                                    padding:15px;
                                                ">
                                            <div
                                                class="option-values-list"
                                                style="
                                                        display:flex;
                                                        flex-wrap:wrap;
                                                        gap:10px;
                                                    ">
                                                @forelse ($option->values as $value)

                                                @php
                                                $valueLabel =
                                                $value->label
                                                ?: $value->value;

                                                $valueChecked = in_array(
                                                (string) $value->id,
                                                $selectedOptionValueIds,
                                                true
                                                );
                                                @endphp

                                                <label
                                                    style="
                                                                display:flex;
                                                                align-items:center;
                                                                gap:7px;
                                                                padding:8px 11px;
                                                                border:1px solid #ddd;
                                                                border-radius:5px;
                                                                cursor:pointer;
                                                            ">
                                                    <input
                                                        type="checkbox"
                                                        class="option-value-checkbox"
                                                        name="product_option_values[]"
                                                        value="{{ $value->id }}"
                                                        data-value-id="{{ $value->id }}"
                                                        data-value-label="{{ $valueLabel }}"
                                                        {{ $valueChecked ? 'checked' : '' }}>

                                                    @if (!empty($value->color_code))

                                                    <span
                                                        style="
                                                                        width:18px;
                                                                        height:18px;
                                                                        display:inline-block;
                                                                        border-radius:50%;
                                                                        border:1px solid #999;
                                                                        background:{{ $value->color_code }};
                                                                    "></span>

                                                    @endif

                                                    <span>
                                                        {{ $valueLabel }}
                                                    </span>
                                                </label>

                                                @empty

                                                <p
                                                    class="no-values-message"
                                                    style="margin:0;color:#777;">
                                                    No values added yet.
                                                </p>

                                                @endforelse
                                            </div>

                                            <div
                                                class="new-value-form"
                                                style="
                                                        display:none;
                                                        margin-top:15px;
                                                        padding-top:15px;
                                                        border-top:1px solid #ddd;
                                                    ">
                                                <div
                                                    style="
                                                            display:grid;
                                                            grid-template-columns:1fr 1fr 130px auto;
                                                            gap:10px;
                                                            align-items:end;
                                                        ">
                                                    <div>
                                                        <label
                                                            style="display:block;font-weight:bold;margin-bottom:5px;">
                                                            Label
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="new-value-label"
                                                            placeholder="Example: Small"
                                                            style="width:100%;padding:9px;border:1px solid #ccc;border-radius:5px;">
                                                    </div>

                                                    <div>
                                                        <label
                                                            style="display:block;font-weight:bold;margin-bottom:5px;">
                                                            Value
                                                        </label>

                                                        <input
                                                            type="text"
                                                            class="new-value-text"
                                                            placeholder="Example: small"
                                                            style="width:100%;padding:9px;border:1px solid #ccc;border-radius:5px;">
                                                    </div>

                                                    <div>
                                                        <label
                                                            style="display:block;font-weight:bold;margin-bottom:5px;">
                                                            Colour
                                                        </label>

                                                        <input
                                                            type="color"
                                                            class="new-value-color"
                                                            value="#000000"
                                                            style="width:100%;height:40px;border:1px solid #ccc;border-radius:5px;">
                                                    </div>

                                                    <button
                                                        type="button"
                                                        class="save-new-value"
                                                        style="
                                                                padding:10px 13px;
                                                                background:#198754;
                                                                color:#fff;
                                                                border:0;
                                                                border-radius:5px;
                                                                cursor:pointer;
                                                            ">
                                                        Save
                                                    </button>
                                                </div>

                                                <div
                                                    class="value-message"
                                                    style="margin-top:8px;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    @empty

                                    <p
                                        id="no-options-message"
                                        style="color:#777;">
                                        No attributes found. Add your first attribute.
                                    </p>

                                    @endforelse

                                </div>

                            </div>

                            {{-- VARIANTS --}}
                            <div style="{{ $cardStyle }}">

                                <div
                                    style="
                                        display:flex;
                                        justify-content:space-between;
                                        align-items:center;
                                        gap:15px;
                                        flex-wrap:wrap;
                                        margin-bottom:15px;
                                    ">
                                    <div>
                                        <h2 style="margin:0 0 5px;">
                                            Product Variants
                                        </h2>

                                        <p style="margin:0;color:#666;">
                                            Generate combinations from selected attribute values.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        id="generate-variants"
                                        style="
                                            padding:9px 15px;
                                            background:#0d6efd;
                                            color:#fff;
                                            border:0;
                                            border-radius:5px;
                                            cursor:pointer;
                                        ">
                                        Generate Variants
                                    </button>
                                </div>

                                <div
                                    id="variants-container"
                                    style="overflow-x:auto;">
                                    @if (!empty($oldVariants))

                                    @foreach ($oldVariants as $variantIndex => $variant)

                                    <div
                                        class="variant-row"
                                        style="
                                                    border:1px solid #ddd;
                                                    border-radius:7px;
                                                    padding:15px;
                                                    margin-bottom:15px;
                                                ">
                                        <div
                                            style="
                                                        display:flex;
                                                        justify-content:space-between;
                                                        align-items:center;
                                                        gap:15px;
                                                        margin-bottom:12px;
                                                    ">
                                            <strong class="variant-title">
                                                Variant {{ $variantIndex + 1 }}
                                            </strong>

                                            <button
                                                type="button"
                                                class="remove-variant"
                                                style="
                                                            padding:6px 10px;
                                                            border:0;
                                                            border-radius:4px;
                                                            background:#dc3545;
                                                            color:#fff;
                                                            cursor:pointer;
                                                        ">
                                                Remove
                                            </button>
                                        </div>

                                        <div class="variant-options-hidden">

                                            @foreach (($variant['options'] ?? []) as $optionIndex => $variantOption)

                                            <input
                                                type="hidden"
                                                name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][option_id]"
                                                value="{{ $variantOption['option_id'] ?? '' }}">

                                            <input
                                                type="hidden"
                                                name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][option_name]"
                                                value="{{ $variantOption['option_name'] ?? '' }}">

                                            <input
                                                type="hidden"
                                                name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][value_id]"
                                                value="{{ $variantOption['value_id'] ?? '' }}">

                                            <input
                                                type="hidden"
                                                name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][value_label]"
                                                value="{{ $variantOption['value_label'] ?? '' }}">

                                            @endforeach

                                        </div>

                                        <div
                                            style="
                                                        display:grid;
                                                        grid-template-columns:repeat(4, minmax(140px, 1fr));
                                                        gap:12px;
                                                    ">
                                            <div>
                                                <label style="display:block;font-weight:bold;margin-bottom:5px;">
                                                    SKU
                                                </label>

                                                <input
                                                    type="text"
                                                    name="variants[{{ $variantIndex }}][sku]"
                                                    value="{{ $variant['sku'] ?? '' }}"
                                                    style="width:100%;padding:9px;border:1px solid #ccc;border-radius:5px;">
                                            </div>

                                            <div>
                                                <label style="display:block;font-weight:bold;margin-bottom:5px;">
                                                    Regular Price
                                                </label>

                                                <input
                                                    type="number"
                                                    name="variants[{{ $variantIndex }}][regular_price]"
                                                    value="{{ $variant['regular_price'] ?? '' }}"
                                                    min="0"
                                                    step="0.01"
                                                    style="width:100%;padding:9px;border:1px solid #ccc;border-radius:5px;">
                                            </div>

                                            <div>
                                                <label style="display:block;font-weight:bold;margin-bottom:5px;">
                                                    Sale Price
                                                </label>

                                                <input
                                                    type="number"
                                                    name="variants[{{ $variantIndex }}][sale_price]"
                                                    value="{{ $variant['sale_price'] ?? '' }}"
                                                    min="0"
                                                    step="0.01"
                                                    style="width:100%;padding:9px;border:1px solid #ccc;border-radius:5px;">
                                            </div>

                                            <div>
                                                <label style="display:block;font-weight:bold;margin-bottom:5px;">
                                                    Stock
                                                </label>

                                                <input
                                                    type="number"
                                                    name="variants[{{ $variantIndex }}][stock]"
                                                    value="{{ $variant['stock'] ?? 0 }}"
                                                    min="0"
                                                    step="1"
                                                    style="width:100%;padding:9px;border:1px solid #ccc;border-radius:5px;">
                                            </div>

                                            <div>
                                                <label style="display:block;font-weight:bold;margin-bottom:5px;">
                                                    Image
                                                </label>

                                                <input
                                                    type="file"
                                                    name="variants[{{ $variantIndex }}][image]"
                                                    accept="image/*">
                                            </div>
                                        </div>
                                    </div>

                                    @endforeach

                                    @else

                                    <p
                                        id="no-variants-message"
                                        style="color:#777;">
                                        Select attribute values and click “Generate Variants”.
                                    </p>

                                    @endif
                                </div>

                            </div>

                            {{-- SEO --}}
                            <div style="{{ $cardStyle }}">

                                <h2 style="margin-top:0;">
                                    SEO Information
                                </h2>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="meta_title"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Meta Title
                                    </label>

                                    <input
                                        type="text"
                                        id="meta_title"
                                        name="meta_title"
                                        value="{{ old('meta_title') }}"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">
                                </div>

                                <div style="margin-bottom:15px;">
                                    <label
                                        for="meta_description"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Meta Description
                                    </label>

                                    <textarea
                                        id="meta_description"
                                        name="meta_description"
                                        rows="4"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">{{ old('meta_description') }}</textarea>
                                </div>

                                <div>
                                    <label
                                        for="meta_keywords"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Meta Keywords
                                    </label>

                                    <textarea
                                        id="meta_keywords"
                                        name="meta_keywords"
                                        rows="3"
                                        style="width:100%;padding:11px;border:1px solid #ccc;border-radius:5px;">{{ old('meta_keywords') }}</textarea>
                                </div>

                            </div>

                        </div>

                        {{-- RIGHT COLUMN --}}
                        <div>

                            {{-- IMAGES --}}
                            <div style="{{ $cardStyle }}">

                                <h2 style="margin-top:0;">
                                    Product Images
                                </h2>

                                <div style="margin-bottom:18px;">
                                    <label
                                        for="featured_image"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Featured Image
                                    </label>

                                    <input
                                        type="file"
                                        id="featured_image"
                                        name="featured_image"
                                        accept="image/jpeg,image/png,image/webp">
                                </div>

                                <div>
                                    <label
                                        for="gallery_images"
                                        style="display:block;font-weight:bold;margin-bottom:6px;">
                                        Gallery Images
                                    </label>

                                    <input
                                        type="file"
                                        id="gallery_images"
                                        name="gallery_images[]"
                                        accept="image/jpeg,image/png,image/webp"
                                        multiple>
                                </div>

                            </div>

                            {{-- CATEGORIES --}}
                            <div style="{{ $cardStyle }}">

                                <h2 style="margin-top:0;">
                                    Categories
                                </h2>

                                <div
                                    style="
                                        max-height:260px;
                                        overflow-y:auto;
                                        border:1px solid #ddd;
                                        padding:12px;
                                        border-radius:5px;
                                    ">
                                    @forelse ($categories as $category)

                                    <label
                                        style="
                                                display:flex;
                                                align-items:center;
                                                gap:8px;
                                                margin-bottom:9px;
                                            ">
                                        <input
                                            type="checkbox"
                                            name="categories[]"
                                            value="{{ $category->id }}"
                                            {{ in_array(
                                                    (string) $category->id,
                                                    $selectedCategoryIds,
                                                    true
                                                ) ? 'checked' : '' }}>

                                        <span>
                                            {{ $category->title }}
                                        </span>
                                    </label>

                                    @empty

                                    <p style="margin:0;color:#777;">
                                        No categories available.
                                    </p>

                                    @endforelse
                                </div>

                            </div>

                            {{-- TAGS --}}
                            <div style="{{ $cardStyle }}">

                                <h2 style="margin-top:0;">
                                    Tags
                                </h2>

                                <div
                                    style="
                                        max-height:260px;
                                        overflow-y:auto;
                                        border:1px solid #ddd;
                                        padding:12px;
                                        border-radius:5px;
                                    ">
                                    @forelse ($tags as $tag)

                                    <label
                                        style="
                                                display:flex;
                                                align-items:center;
                                                gap:8px;
                                                margin-bottom:9px;
                                            ">
                                        <input
                                            type="checkbox"
                                            name="tags[]"
                                            value="{{ $tag->id }}"
                                            {{ in_array(
                                                    (string) $tag->id,
                                                    $selectedTagIds,
                                                    true
                                                ) ? 'checked' : '' }}>

                                        <span>
                                            {{ $tag->title }}
                                        </span>
                                    </label>

                                    @empty

                                    <p style="margin:0;color:#777;">
                                        No tags available.
                                    </p>

                                    @endforelse
                                </div>

                            </div>

                            <div
                                style="
                                    position:sticky;
                                    top:20px;
                                    background:#fff;
                                    border:1px solid #ddd;
                                    border-radius:8px;
                                    padding:20px;
                                ">
                                <button
                                    type="submit"
                                    style="
                                        width:100%;
                                        padding:13px 20px;
                                        background:#000;
                                        color:#fff;
                                        border:0;
                                        border-radius:5px;
                                        font-size:16px;
                                        cursor:pointer;
                                    ">
                                    Save Product
                                </button>
                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.getAttribute('content') || '{{ csrf_token() }}';

        const productOptionsContainer = document.getElementById(
            'product-options-container'
        );

        const variantsContainer = document.getElementById(
            'variants-container'
        );

        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');

        const showAddAttributeButton = document.getElementById(
            'show-add-attribute'
        );

        const newAttributeForm = document.getElementById(
            'new-attribute-form'
        );

        const saveNewAttributeButton = document.getElementById(
            'save-new-attribute'
        );

        const generateVariantsButton = document.getElementById(
            'generate-variants'
        );

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value === null || value === undefined ?
                '' :
                String(value);

            return div.innerHTML;
        }

        function createSlug(value) {
            return String(value || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        if (titleInput && slugInput) {
            let slugChangedManually = slugInput.value.trim() !== '';

            slugInput.addEventListener('input', function() {
                slugChangedManually = true;
            });

            titleInput.addEventListener('input', function() {
                if (!slugChangedManually) {
                    slugInput.value = createSlug(this.value);
                }
            });
        }

        if (showAddAttributeButton && newAttributeForm) {
            showAddAttributeButton.addEventListener(
                'click',
                function() {
                    newAttributeForm.style.display =
                        newAttributeForm.style.display === 'none' ?
                        'block' :
                        'none';
                }
            );
        }

        function bindOptionBox(optionBox) {
            if (
                !optionBox ||
                optionBox.dataset.bound === 'true'
            ) {
                return;
            }

            optionBox.dataset.bound = 'true';

            const enableCheckbox = optionBox.querySelector(
                '.enable-option-checkbox'
            );

            const valuesContainer = optionBox.querySelector(
                '.option-values-container'
            );

            const showAddValueButton = optionBox.querySelector(
                '.show-add-value-button'
            );

            const newValueForm = optionBox.querySelector(
                '.new-value-form'
            );

            const saveNewValueButton = optionBox.querySelector(
                '.save-new-value'
            );

            if (enableCheckbox && valuesContainer) {
                enableCheckbox.addEventListener(
                    'change',
                    function() {
                        valuesContainer.style.display =
                            this.checked ? 'block' : 'none';

                        if (!this.checked) {
                            valuesContainer
                                .querySelectorAll(
                                    '.option-value-checkbox'
                                )
                                .forEach(function(checkbox) {
                                    checkbox.checked = false;
                                });
                        }
                    }
                );

                optionBox.addEventListener(
                    'change',
                    function(event) {
                        if (
                            event.target.classList.contains(
                                'option-value-checkbox'
                            ) &&
                            event.target.checked
                        ) {
                            enableCheckbox.checked = true;
                            valuesContainer.style.display = 'block';
                        }
                    }
                );
            }

            if (showAddValueButton && newValueForm) {
                showAddValueButton.addEventListener(
                    'click',
                    function() {
                        newValueForm.style.display =
                            newValueForm.style.display === 'none' ?
                            'block' :
                            'none';
                    }
                );
            }

            if (saveNewValueButton) {
                saveNewValueButton.addEventListener(
                    'click',
                    async function() {
                        const optionId = optionBox.dataset.optionId;

                        const labelInput = optionBox.querySelector(
                            '.new-value-label'
                        );

                        const valueInput = optionBox.querySelector(
                            '.new-value-text'
                        );

                        const colorInput = optionBox.querySelector(
                            '.new-value-color'
                        );

                        const messageBox = optionBox.querySelector(
                            '.value-message'
                        );

                        const label = labelInput.value.trim();
                        const value = valueInput.value.trim();

                        if (!label) {
                            messageBox.innerHTML =
                                '<span style="color:red;">Value label is required.</span>';

                            return;
                        }

                        saveNewValueButton.disabled = true;
                        saveNewValueButton.textContent = 'Saving...';

                        try {
                            const response = await fetch(
                                '{{ url('/admin/product-options') }}/' +
                                encodeURIComponent(optionId) +
                                '/values', {
                                    method: 'POST',

                                    headers: {
                                        'Content-Type': 'application/json',

                                        'Accept': 'application/json',

                                        'X-CSRF-TOKEN': csrfToken,
                                    },

                                    body: JSON.stringify({
                                        label: label,
                                        value: value || null,
                                        color_code: colorInput ?
                                            colorInput.value : null,
                                    }),
                                }
                            );

                            const data = await response.json();

                            if (!response.ok) {
                                const errorMessage =
                                    data.message ||
                                    Object.values(
                                        data.errors || {}
                                    ).flat()[0] ||
                                    'Unable to save value.';

                                throw new Error(errorMessage);
                            }

                            const valuesList = optionBox.querySelector(
                                '.option-values-list'
                            );

                            const noValuesMessage =
                                valuesList.querySelector(
                                    '.no-values-message'
                                );

                            if (noValuesMessage) {
                                noValuesMessage.remove();
                            }

                            const labelElement =
                                document.createElement('label');

                            labelElement.style.cssText =
                                'display:flex;' +
                                'align-items:center;' +
                                'gap:7px;' +
                                'padding:8px 11px;' +
                                'border:1px solid #ddd;' +
                                'border-radius:5px;' +
                                'cursor:pointer;';

                            let colorCircle = '';

                            if (data.value.color_code) {
                                colorCircle =
                                    '<span style="' +
                                    'width:18px;' +
                                    'height:18px;' +
                                    'display:inline-block;' +
                                    'border-radius:50%;' +
                                    'border:1px solid #999;' +
                                    'background:' +
                                    escapeHtml(
                                        data.value.color_code
                                    ) +
                                    ';"></span>';
                            }

                            labelElement.innerHTML =
                                '<input ' +
                                'type="checkbox" ' +
                                'class="option-value-checkbox" ' +
                                'name="product_option_values[]" ' +
                                'value="' +
                                escapeHtml(data.value.id) +
                                '" ' +
                                'data-value-id="' +
                                escapeHtml(data.value.id) +
                                '" ' +
                                'data-value-label="' +
                                escapeHtml(data.value.label) +
                                '" checked>' +
                                colorCircle +
                                '<span>' +
                                escapeHtml(data.value.label) +
                                '</span>';

                            valuesList.appendChild(labelElement);

                            enableCheckbox.checked = true;
                            valuesContainer.style.display = 'block';

                            labelInput.value = '';
                            valueInput.value = '';

                            messageBox.innerHTML =
                                '<span style="color:green;">' +
                                escapeHtml(data.message) +
                                '</span>';
                        } catch (error) {
                            messageBox.innerHTML =
                                '<span style="color:red;">' +
                                escapeHtml(error.message) +
                                '</span>';
                        } finally {
                            saveNewValueButton.disabled = false;
                            saveNewValueButton.textContent = 'Save';
                        }
                    }
                );
            }
        }

        document
            .querySelectorAll('.product-option-box')
            .forEach(bindOptionBox);

        if (saveNewAttributeButton) {
            saveNewAttributeButton.addEventListener(
                'click',
                async function() {
                    const nameInput = document.getElementById(
                        'new-attribute-name'
                    );

                    const typeInput = document.getElementById(
                        'new-attribute-type'
                    );

                    const messageBox = document.getElementById(
                        'attribute-message'
                    );

                    const name = nameInput.value.trim();
                    const type = typeInput.value;

                    if (!name) {
                        messageBox.innerHTML =
                            '<span style="color:red;">Attribute name is required.</span>';

                        return;
                    }

                    saveNewAttributeButton.disabled = true;
                    saveNewAttributeButton.textContent = 'Saving...';

                    try {
                        const response = await fetch(
                            '{{ route('
                            admin.product - options.store ') }}', {
                                method: 'POST',

                                headers: {
                                    'Content-Type': 'application/json',

                                    'Accept': 'application/json',

                                    'X-CSRF-TOKEN': csrfToken,
                                },

                                body: JSON.stringify({
                                    name: name,
                                    type: type,
                                }),
                            }
                        );

                        const data = await response.json();

                        if (!response.ok) {
                            const errorMessage =
                                data.message ||
                                Object.values(
                                    data.errors || {}
                                ).flat()[0] ||
                                'Unable to save attribute.';

                            throw new Error(errorMessage);
                        }

                        const noOptionsMessage =
                            document.getElementById(
                                'no-options-message'
                            );

                        if (noOptionsMessage) {
                            noOptionsMessage.remove();
                        }

                        const optionBox =
                            document.createElement('div');

                        optionBox.className =
                            'product-option-box';

                        optionBox.dataset.optionId =
                            data.option.id;

                        optionBox.dataset.optionName =
                            data.option.name;

                        optionBox.style.cssText =
                            'border:1px solid #ddd;' +
                            'border-radius:7px;' +
                            'margin-bottom:15px;' +
                            'overflow:hidden;';

                        optionBox.innerHTML =
                            '<div style="' +
                            'display:flex;' +
                            'justify-content:space-between;' +
                            'align-items:center;' +
                            'gap:15px;' +
                            'padding:13px 15px;' +
                            'background:#f7f7f7;' +
                            '">' +
                            '<label style="' +
                            'display:flex;' +
                            'align-items:center;' +
                            'gap:8px;' +
                            'cursor:pointer;' +
                            'font-weight:bold;' +
                            '">' +
                            '<input ' +
                            'type="checkbox" ' +
                            'class="enable-option-checkbox" ' +
                            'name="product_options[]" ' +
                            'value="' +
                            escapeHtml(data.option.id) +
                            '" checked>' +
                            '<span>' +
                            escapeHtml(data.option.name) +
                            '</span>' +
                            '<small style="' +
                            'color:#777;' +
                            'font-weight:normal;' +
                            '">(' +
                            escapeHtml(data.option.type) +
                            ')</small>' +
                            '</label>' +
                            '<button ' +
                            'type="button" ' +
                            'class="show-add-value-button" ' +
                            'style="' +
                            'padding:7px 10px;' +
                            'border:1px solid #bbb;' +
                            'background:#fff;' +
                            'border-radius:4px;' +
                            'cursor:pointer;' +
                            '">' +
                            'Add Value' +
                            '</button>' +
                            '</div>' +
                            '<div ' +
                            'class="option-values-container" ' +
                            'style="display:block;padding:15px;">' +
                            '<div ' +
                            'class="option-values-list" ' +
                            'style="' +
                            'display:flex;' +
                            'flex-wrap:wrap;' +
                            'gap:10px;' +
                            '">' +
                            '<p ' +
                            'class="no-values-message" ' +
                            'style="margin:0;color:#777;' +
                            '">' +
                            'No values added yet.' +
                            '</p>' +
                            '</div>' +
                            '<div ' +
                            'class="new-value-form" ' +
                            'style="' +
                            'display:none;' +
                            'margin-top:15px;' +
                            'padding-top:15px;' +
                            'border-top:1px solid #ddd;' +
                            '">' +
                            '<div style="' +
                            'display:grid;' +
                            'grid-template-columns:1fr 1fr 130px auto;' +
                            'gap:10px;' +
                            'align-items:end;' +
                            '">' +
                            '<div>' +
                            '<label style="' +
                            'display:block;' +
                            'font-weight:bold;' +
                            'margin-bottom:5px;' +
                            '">Label</label>' +
                            '<input ' +
                            'type="text" ' +
                            'class="new-value-label" ' +
                            'style="' +
                            'width:100%;' +
                            'padding:9px;' +
                            'border:1px solid #ccc;' +
                            'border-radius:5px;' +
                            '">' +
                            '</div>' +
                            '<div>' +
                            '<label style="' +
                            'display:block;' +
                            'font-weight:bold;' +
                            'margin-bottom:5px;' +
                            '">Value</label>' +
                            '<input ' +
                            'type="text" ' +
                            'class="new-value-text" ' +
                            'style="' +
                            'width:100%;' +
                            'padding:9px;' +
                            'border:1px solid #ccc;' +
                            'border-radius:5px;' +
                            '">' +
                            '</div>' +
                            '<div>' +
                            '<label style="' +
                            'display:block;' +
                            'font-weight:bold;' +
                            'margin-bottom:5px;' +
                            '">Colour</label>' +
                            '<input ' +
                            'type="color" ' +
                            'class="new-value-color" ' +
                            'value="#000000" ' +
                            'style="' +
                            'width:100%;' +
                            'height:40px;' +
                            'border:1px solid #ccc;' +
                            'border-radius:5px;' +
                            '">' +
                            '</div>' +
                            '<button ' +
                            'type="button" ' +
                            'class="save-new-value" ' +
                            'style="' +
                            'padding:10px 13px;' +
                            'background:#198754;' +
                            'color:#fff;' +
                            'border:0;' +
                            'border-radius:5px;' +
                            'cursor:pointer;' +
                            '">' +
                            'Save' +
                            '</button>' +
                            '</div>' +
                            '<div ' +
                            'class="value-message" ' +
                            'style="margin-top:8px;' +
                            '"></div>' +
                            '</div>' +
                            '</div>';

                        productOptionsContainer.appendChild(
                            optionBox
                        );

                        bindOptionBox(optionBox);

                        nameInput.value = '';

                        messageBox.innerHTML =
                            '<span style="color:green;">' +
                            escapeHtml(data.message) +
                            '</span>';
                    } catch (error) {
                        messageBox.innerHTML =
                            '<span style="color:red;">' +
                            escapeHtml(error.message) +
                            '</span>';
                    } finally {
                        saveNewAttributeButton.disabled = false;
                        saveNewAttributeButton.textContent = 'Save';
                    }
                }
            );
        }

        function getSelectedOptionGroups() {
            const groups = [];

            document
                .querySelectorAll('.product-option-box')
                .forEach(function(optionBox) {
                    const enableCheckbox = optionBox.querySelector(
                        '.enable-option-checkbox'
                    );

                    if (!enableCheckbox || !enableCheckbox.checked) {
                        return;
                    }

                    const checkedValues = Array.from(
                        optionBox.querySelectorAll(
                            '.option-value-checkbox:checked'
                        )
                    );

                    if (checkedValues.length === 0) {
                        return;
                    }

                    groups.push({
                        option_id: optionBox.dataset.optionId,
                        option_name: optionBox.dataset.optionName,
                        values: checkedValues.map(function(checkbox) {
                            return {
                                value_id: checkbox.dataset.valueId,
                                value_label: checkbox.dataset.valueLabel,
                            };
                        }),
                    });
                });

            return groups;
        }

        function cartesianProduct(groups) {
            return groups.reduce(
                function(combinations, group) {
                    const next = [];

                    combinations.forEach(function(combination) {
                        group.values.forEach(function(value) {
                            next.push(
                                combination.concat([{
                                    option_id: group.option_id,
                                    option_name: group.option_name,
                                    value_id: value.value_id,
                                    value_label: value.value_label,
                                }, ])
                            );
                        });
                    });

                    return next;
                },
                [
                    []
                ]
            );
        }

        function renderVariants(combinations) {
            variantsContainer.innerHTML = '';

            const productRegularPrice =
                document.getElementById(
                    'regular_price'
                ).value;

            const productSalePrice =
                document.getElementById(
                    'sale_price'
                ).value;

            combinations.forEach(
                function(combination, variantIndex) {
                    const title = combination
                        .map(function(item) {
                            return item.option_name +
                                ': ' +
                                item.value_label;
                        })
                        .join(' / ');

                    const hiddenOptions = combination
                        .map(function(item, optionIndex) {
                            return (
                                '<input ' +
                                'type="hidden" ' +
                                'name="variants[' +
                                variantIndex +
                                '][options][' +
                                optionIndex +
                                '][option_id]" ' +
                                'value="' +
                                escapeHtml(item.option_id) +
                                '">' +
                                '<input ' +
                                'type="hidden" ' +
                                'name="variants[' +
                                variantIndex +
                                '][options][' +
                                optionIndex +
                                '][option_name]" ' +
                                'value="' +
                                escapeHtml(item.option_name) +
                                '">' +
                                '<input ' +
                                'type="hidden" ' +
                                'name="variants[' +
                                variantIndex +
                                '][options][' +
                                optionIndex +
                                '][value_id]" ' +
                                'value="' +
                                escapeHtml(item.value_id) +
                                '">' +
                                '<input ' +
                                'type="hidden" ' +
                                'name="variants[' +
                                variantIndex +
                                '][options][' +
                                optionIndex +
                                '][value_label]" ' +
                                'value="' +
                                escapeHtml(item.value_label) +
                                '">'
                            );
                        })
                        .join('');

                    const variantRow =
                        document.createElement('div');

                    variantRow.className = 'variant-row';

                    variantRow.style.cssText =
                        'border:1px solid #ddd;' +
                        'border-radius:7px;' +
                        'padding:15px;' +
                        'margin-bottom:15px;';

                    variantRow.innerHTML =
                        '<div style="' +
                        'display:flex;' +
                        'justify-content:space-between;' +
                        'align-items:center;' +
                        'gap:15px;' +
                        'margin-bottom:12px;' +
                        '">' +
                        '<strong class="variant-title">' +
                        escapeHtml(title) +
                        '</strong>' +
                        '<button ' +
                        'type="button" ' +
                        'class="remove-variant" ' +
                        'style="' +
                        'padding:6px 10px;' +
                        'border:0;' +
                        'border-radius:4px;' +
                        'background:#dc3545;' +
                        'color:#fff;' +
                        'cursor:pointer;' +
                        '">' +
                        'Remove' +
                        '</button>' +
                        '</div>' +
                        '<div class="variant-options-hidden">' +
                        hiddenOptions +
                        '</div>' +
                        '<div style="' +
                        'display:grid;' +
                        'grid-template-columns:repeat(4, minmax(140px, 1fr));' +
                        'gap:12px;' +
                        '">' +
                        '<div>' +
                        '<label style="' +
                        'display:block;' +
                        'font-weight:bold;' +
                        'margin-bottom:5px;' +
                        '">SKU</label>' +
                        '<input ' +
                        'type="text" ' +
                        'name="variants[' +
                        variantIndex +
                        '][sku]" ' +
                        'style="' +
                        'width:100%;' +
                        'padding:9px;' +
                        'border:1px solid #ccc;' +
                        'border-radius:5px;' +
                        '">' +
                        '</div>' +
                        '<div>' +
                        '<label style="' +
                        'display:block;' +
                        'font-weight:bold;' +
                        'margin-bottom:5px;' +
                        '">Regular Price</label>' +
                        '<input ' +
                        'type="number" ' +
                        'name="variants[' +
                        variantIndex +
                        '][regular_price]" ' +
                        'value="' +
                        escapeHtml(productRegularPrice) +
                        '" ' +
                        'min="0" ' +
                        'step="0.01" ' +
                        'style="' +
                        'width:100%;' +
                        'padding:9px;' +
                        'border:1px solid #ccc;' +
                        'border-radius:5px;' +
                        '">' +
                        '</div>' +
                        '<div>' +
                        '<label style="' +
                        'display:block;' +
                        'font-weight:bold;' +
                        'margin-bottom:5px;' +
                        '">Sale Price</label>' +
                        '<input ' +
                        'type="number" ' +
                        'name="variants[' +
                        variantIndex +
                        '][sale_price]" ' +
                        'value="' +
                        escapeHtml(productSalePrice) +
                        '" ' +
                        'min="0" ' +
                        'step="0.01" ' +
                        'style="' +
                        'width:100%;' +
                        'padding:9px;' +
                        'border:1px solid #ccc;' +
                        'border-radius:5px;' +
                        '">' +
                        '</div>' +
                        '<div>' +
                        '<label style="' +
                        'display:block;' +
                        'font-weight:bold;' +
                        'margin-bottom:5px;' +
                        '">Stock</label>' +
                        '<input ' +
                        'type="number" ' +
                        'name="variants[' +
                        variantIndex +
                        '][stock]" ' +
                        'value="0" ' +
                        'min="0" ' +
                        'step="1" ' +
                        'style="' +
                        'width:100%;' +
                        'padding:9px;' +
                        'border:1px solid #ccc;' +
                        'border-radius:5px;' +
                        '">' +
                        '</div>' +
                        '<div>' +
                        '<label style="' +
                        'display:block;' +
                        'font-weight:bold;' +
                        'margin-bottom:5px;' +
                        '">Image</label>' +
                        '<input ' +
                        'type="file" ' +
                        'name="variants[' +
                        variantIndex +
                        '][image]" ' +
                        'accept="image/*">' +
                        '</div>' +
                        '</div>';

                    variantsContainer.appendChild(variantRow);
                }
            );

            bindRemoveVariantButtons();
        }

        if (generateVariantsButton) {
            generateVariantsButton.addEventListener(
                'click',
                function() {
                    const groups = getSelectedOptionGroups();

                    if (groups.length === 0) {
                        alert(
                            'Please select at least one attribute and one value.'
                        );

                        return;
                    }

                    const combinations =
                        cartesianProduct(groups);

                    renderVariants(combinations);
                }
            );
        }

        function bindRemoveVariantButtons() {
            document
                .querySelectorAll('.remove-variant')
                .forEach(function(button) {
                    if (button.dataset.bound === 'true') {
                        return;
                    }

                    button.dataset.bound = 'true';

                    button.addEventListener(
                        'click',
                        function() {
                            this.closest('.variant-row')?.remove();
                            reindexVariants();
                        }
                    );
                });
        }

        function reindexVariants() {
            document
                .querySelectorAll('.variant-row')
                .forEach(function(row, newIndex) {
                    row.querySelectorAll(
                        'input[name^="variants["]'
                    ).forEach(function(input) {
                        input.name = input.name.replace(
                            /^variants\[\d+\]/,
                            'variants[' + newIndex + ']'
                        );
                    });
                });
        }

        bindRemoveVariantButtons();
    });
</script>

@endsection