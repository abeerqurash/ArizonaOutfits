@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')

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
                        margin-bottom:20px;
                    ">
                    <div>
                        <h1 style="margin:0 0 5px;">
                            Products
                        </h1>

                        <p style="margin:0;color:#666;">
                            Manage products, prices, stock, attributes and variants.
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.products.create') }}"
                        style="
                            display:inline-block;
                            padding:10px 20px;
                            background:#000;
                            color:#fff;
                            text-decoration:none;
                            border-radius:5px;
                        ">
                        Add Product
                    </a>
                </div>

                @if (session('success'))

                <div
                    style="
                            padding:15px;
                            background:#d4edda;
                            color:#155724;
                            border:1px solid #c3e6cb;
                            border-radius:5px;
                            margin-bottom:20px;
                        ">
                    {{ session('success') }}
                </div>

                @endif

                @if (session('error'))

                <div
                    style="
                            padding:15px;
                            background:#f8d7da;
                            color:#721c24;
                            border:1px solid #f5c6cb;
                            border-radius:5px;
                            margin-bottom:20px;
                        ">
                    {{ session('error') }}
                </div>

                @endif

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

                <div
                    style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        gap:15px;
                        flex-wrap:wrap;
                        margin-bottom:20px;
                    ">
                    <div style="color:#666;">

                        @if (method_exists($products, 'total'))

                        Total products:
                        <strong>
                            {{ $products->total() }}
                        </strong>

                        @else

                        Total products:
                        <strong>
                            {{ $products->count() }}
                        </strong>

                        @endif

                    </div>

                    <form
                        action="{{ route('admin.products.index') }}"
                        method="GET"
                        style="
                            display:flex;
                            align-items:center;
                            gap:10px;
                            flex-wrap:wrap;
                        ">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search title, SKU or slug"
                            style="
                                min-width:220px;
                                padding:9px 12px;
                                border:1px solid #ccc;
                                border-radius:5px;
                            ">

                        <select
                            name="status"
                            style="
                                padding:9px 12px;
                                border:1px solid #ccc;
                                border-radius:5px;
                            ">
                            <option value="">
                                All statuses
                            </option>

                            <option
                                value="active"
                                {{ request('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option
                                value="draft"
                                {{ request('status') === 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>

                            <option
                                value="inactive"
                                {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>

                        <button
                            type="submit"
                            style="
                                padding:9px 16px;
                                background:#000;
                                color:#fff;
                                border:0;
                                border-radius:5px;
                                cursor:pointer;
                            ">
                            Filter
                        </button>

                        @if (request()->filled('search') || request()->filled('status'))

                        <a
                            href="{{ route('admin.products.index') }}"
                            style="
                                    padding:9px 16px;
                                    background:#eee;
                                    color:#222;
                                    text-decoration:none;
                                    border-radius:5px;
                                ">
                            Reset
                        </a>

                        @endif
                    </form>
                </div>

                <div style="overflow-x:auto;">

                    <table
                        border="1"
                        cellpadding="10"
                        cellspacing="0"
                        width="100%"
                        style="
                            border-collapse:collapse;
                            min-width:1500px;
                            background:#fff;
                        ">
                        <thead>

                            <tr style="background:#f5f5f5;">

                                <th width="90">
                                    Image
                                </th>

                                <th>
                                    Product
                                </th>

                                <th>
                                    SKU
                                </th>

                                <th>
                                    Price
                                </th>

                                <th>
                                    Stock
                                </th>

                                <th>
                                    Categories
                                </th>

                                <th>
                                    Tags
                                </th>

                                <th>
                                    Attributes
                                </th>

                                <th>
                                    Variants
                                </th>

                                <th>
                                    Views
                                </th>

                                <th>
                                    Favorites
                                </th>

                                <th>
                                    Status
                                </th>

                                <th width="190">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($products as $product)

                            @php
                            $regularPrice = (float) ($product->regular_price ?? 0);
                            $salePrice = $product->sale_price !== null
                            ? (float) $product->sale_price
                            : null;

                            $hasSalePrice = $salePrice !== null
                            && $salePrice > 0
                            && $salePrice < $regularPrice;

                                $categoryTitles=$product->relationLoaded('categories')
                                ? $product->categories
                                ->pluck('title')
                                ->filter()
                                ->values()
                                : collect();

                                $tagTitles = $product->relationLoaded('tags')
                                ? $product->tags
                                ->pluck('title')
                                ->filter()
                                ->values()
                                : collect();

                                $productOptions = $product->relationLoaded('options')
                                ? $product->options
                                : collect();

                                $variantCount = $product->relationLoaded('variants')
                                ? $product->variants->count()
                                : ($product->variants_count ?? 0);

                                $viewsCount = $product->views_count ?? 0;
                                $favoritesCount = $product->favorites_count ?? 0;
                                $stock = (int) ($product->stock ?? 0);
                                @endphp

                                <tr>

                                    <td style="text-align:center;">

                                        @if (!empty($product->featured_image))

                                        <img
                                            src="{{ asset('storage/' . $product->featured_image) }}"
                                            alt="{{ $product->title }}"
                                            width="70"
                                            height="70"
                                            loading="lazy"
                                            style="
                                                    object-fit:cover;
                                                    border-radius:8px;
                                                    border:1px solid #ddd;
                                                ">

                                        @else

                                        <div
                                            style="
                                                    width:70px;
                                                    height:70px;
                                                    margin:auto;
                                                    background:#eee;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    border-radius:8px;
                                                    color:#777;
                                                ">
                                            N/A
                                        </div>

                                        @endif

                                    </td>

                                    <td style="min-width:220px;">

                                        <strong>
                                            {{ $product->title }}
                                        </strong>

                                        <br>

                                        <small style="color:#777;">
                                            {{ $product->slug }}
                                        </small>

                                        @if (!empty($product->short_description))

                                        <div
                                            style="
                                                    margin-top:8px;
                                                    color:#666;
                                                    font-size:13px;
                                                    line-height:1.4;
                                                ">
                                            {{ \Illuminate\Support\Str::limit(
                                                    strip_tags($product->short_description),
                                                    80
                                                ) }}
                                        </div>

                                        @endif

                                    </td>

                                    <td>

                                        {{ $product->sku ?: '-' }}

                                    </td>

                                    <td style="min-width:120px;">

                                        @if ($hasSalePrice)

                                        <span
                                            style="
                                                    text-decoration:line-through;
                                                    color:#999;
                                                ">
                                            ${{ number_format($regularPrice, 2) }}
                                        </span>

                                        <br>

                                        <strong style="color:#c0392b;">
                                            ${{ number_format($salePrice, 2) }}
                                        </strong>

                                        @else

                                        <strong>
                                            ${{ number_format($regularPrice, 2) }}
                                        </strong>

                                        @endif

                                    </td>

                                    <td style="text-align:center;">

                                        @if ($stock <= 0)

                                            <span
                                            style="
                                                    display:inline-block;
                                                    padding:4px 8px;
                                                    background:#f8d7da;
                                                    color:#721c24;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                            Out of stock
                                            </span>

                                            @elseif ($stock <= 5)

                                                <span
                                                style="
                                                    display:inline-block;
                                                    padding:4px 8px;
                                                    background:#fff3cd;
                                                    color:#856404;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                                {{ $stock }} left
                                                </span>

                                                @else

                                                <span
                                                    style="
                                                    display:inline-block;
                                                    padding:4px 8px;
                                                    background:#d4edda;
                                                    color:#155724;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                                    {{ $stock }}
                                                </span>

                                                @endif

                                    </td>

                                    <td style="min-width:180px;">

                                        @if ($categoryTitles->isNotEmpty())

                                        {{ $categoryTitles->join(', ') }}

                                        @else

                                        -

                                        @endif

                                    </td>

                                    <td style="min-width:150px;">

                                        @if ($tagTitles->isNotEmpty())

                                        {{ $tagTitles->join(', ') }}

                                        @else

                                        -

                                        @endif

                                    </td>

                                    <td style="min-width:180px;">

                                        @if ($productOptions->isNotEmpty())

                                        @foreach ($productOptions as $option)

                                        <span
                                            style="
                                                        display:inline-block;
                                                        padding:3px 7px;
                                                        margin:2px;
                                                        background:#eef2f7;
                                                        border:1px solid #d9e0e8;
                                                        border-radius:4px;
                                                        font-size:12px;
                                                    ">
                                            {{ $option->name }}
                                        </span>

                                        @endforeach

                                        @else

                                        -

                                        @endif

                                    </td>

                                    <td style="text-align:center;">

                                        @if ($variantCount > 0)

                                        <span
                                            style="
                                                    display:inline-block;
                                                    min-width:30px;
                                                    padding:4px 8px;
                                                    background:#e7f1ff;
                                                    color:#084298;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                            {{ $variantCount }}
                                        </span>

                                        @else

                                        <span style="color:#777;">
                                            0
                                        </span>

                                        @endif

                                    </td>

                                    <td style="text-align:center;">

                                        {{ number_format((int) $viewsCount) }}

                                    </td>

                                    <td style="text-align:center;">

                                        {{ number_format((int) $favoritesCount) }}

                                    </td>

                                    <td>

                                        @if ($product->status === 'active')

                                        <span
                                            style="
                                                    display:inline-block;
                                                    padding:5px 9px;
                                                    background:#d4edda;
                                                    color:#155724;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                            Active
                                        </span>

                                        @elseif ($product->status === 'draft')

                                        <span
                                            style="
                                                    display:inline-block;
                                                    padding:5px 9px;
                                                    background:#fff3cd;
                                                    color:#856404;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                            Draft
                                        </span>

                                        @else

                                        <span
                                            style="
                                                    display:inline-block;
                                                    padding:5px 9px;
                                                    background:#f8d7da;
                                                    color:#721c24;
                                                    border-radius:4px;
                                                    font-weight:bold;
                                                ">
                                            Inactive
                                        </span>

                                        @endif

                                    </td>

                                    <td style="min-width:190px;">

                                        <div
                                            style="
                                                display:flex;
                                                align-items:center;
                                                gap:8px;
                                                flex-wrap:wrap;
                                            ">
                                            @if (!empty($product->slug))

                                            <a
                                                href="{{ route('products.show', $product->slug) }}"
                                                target="_blank"
                                                rel="noopener"
                                                style="
                                                        display:inline-block;
                                                        padding:6px 10px;
                                                        background:#e7f1ff;
                                                        color:#084298;
                                                        text-decoration:none;
                                                        border-radius:4px;
                                                    ">
                                                View
                                            </a>

                                            @endif

                                            <a
                                                href="{{ route('admin.products.edit', $product->id) }}"
                                                style="
                                                    display:inline-block;
                                                    padding:6px 10px;
                                                    background:#fff3cd;
                                                    color:#856404;
                                                    text-decoration:none;
                                                    border-radius:4px;
                                                ">
                                                Edit
                                            </a>
                                            <a
                                                href="{{ route('admin.products.inventory.edit', $product) }}"
                                                style="
        display:inline-block;
        padding:6px 10px;
        background:#d1ecf1;
        color:#0c5460;
        text-decoration:none;
        border-radius:4px;
    ">
                                                Inventory
                                            </a>
                                            <form
                                                action="{{ route('admin.products.destroy', $product->id) }}"
                                                method="POST"
                                                style="display:inline;"
                                                onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    style="
                                                        padding:6px 10px;
                                                        background:#f8d7da;
                                                        color:#721c24;
                                                        border:0;
                                                        border-radius:4px;
                                                        cursor:pointer;
                                                    ">
                                                    Delete
                                                </button>

                                            </form>
                                        </div>

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td
                                        colspan="13"
                                        style="
                                            text-align:center;
                                            padding:40px;
                                        ">
                                        <strong>
                                            No products found.
                                        </strong>

                                        <br><br>

                                        <a
                                            href="{{ route('admin.products.create') }}"
                                            style="
                                                display:inline-block;
                                                padding:10px 18px;
                                                background:#000;
                                                color:#fff;
                                                text-decoration:none;
                                                border-radius:5px;
                                            ">
                                            Create First Product
                                        </a>

                                    </td>

                                </tr>

                                @endforelse

                        </tbody>

                    </table>

                </div>

                @if (method_exists($products, 'links'))

                <div style="margin-top:20px;">

                    {{ $products->withQueryString()->links() }}

                </div>

                @endif

            </div>

        </div>
    </div>

</div>

@endsection