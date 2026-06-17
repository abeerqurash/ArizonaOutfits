@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="page-wrapper">

    <div class="services">
        <div class="service-wrapper">

            <div class="container">

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h1>Products</h1>

                    <a href="{{ route('admin.products.create') }}"
                       style="padding:10px 20px;background:#000;color:#fff;text-decoration:none;border-radius:5px;">
                        Add Product
                    </a>
                </div>

                @if(session('success'))
                    <div style="padding:15px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:20px;">
                        {{ session('success') }}
                    </div>
                @endif

                <div style="overflow-x:auto;">

                    <table border="1"
                           cellpadding="10"
                           cellspacing="0"
                           width="100%"
                           style="border-collapse:collapse;">

                        <thead>

                            <tr>

                                <th width="90">Image</th>

                                <th>Title</th>

                                <th>SKU</th>

                                <th>Price</th>

                                <th>Stock</th>

                                <th>Categories</th>

                                <th>Views</th>

                                <th>Favorites</th>

                                <th>Status</th>

                                <th width="150">Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($products as $product)

                                <tr>

                                    <td>

                                        @if($product->featured_image)

                                            <img
                                                src="{{ asset('storage/' . $product->featured_image) }}"
                                                alt="{{ $product->title }}"
                                                width="70"
                                                height="70"
                                                style="object-fit:cover;border-radius:8px;">

                                        @else

                                            <div style="width:70px;height:70px;background:#eee;display:flex;align-items:center;justify-content:center;border-radius:8px;">
                                                N/A
                                            </div>

                                        @endif

                                    </td>

                                    <td>

                                        <strong>
                                            {{ $product->title }}
                                        </strong>

                                        <br>

                                        <small>
                                            {{ $product->slug }}
                                        </small>

                                    </td>

                                    <td>

                                        {{ $product->sku ?? '-' }}

                                    </td>

                                    <td>

                                        @if($product->sale_price)

                                            <span style="text-decoration:line-through;color:#999;">
                                                ${{ number_format($product->regular_price, 2) }}
                                            </span>

                                            <br>

                                            <strong>
                                                ${{ number_format($product->sale_price, 2) }}
                                            </strong>

                                        @else

                                            <strong>
                                                ${{ number_format($product->regular_price, 2) }}
                                            </strong>

                                        @endif

                                    </td>

                                    <td>

                                        {{ $product->stock }}

                                    </td>

                                    <td>

                                        @if($product->categories->count())

                                            {{ $product->categories->pluck('title')->join(', ') }}

                                        @else

                                            -

                                        @endif

                                    </td>

                                    <td>

                                        {{ $product->views_count }}

                                    </td>

                                    <td>

                                        {{ $product->favorites_count }}

                                    </td>

                                    <td>

                                        @if($product->status == 'active')

                                            <span style="color:green;font-weight:bold;">
                                                Active
                                            </span>

                                        @elseif($product->status == 'draft')

                                            <span style="color:orange;font-weight:bold;">
                                                Draft
                                            </span>

                                        @else

                                            <span style="color:red;font-weight:bold;">
                                                Inactive
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                           style="margin-right:10px;">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.products.destroy', $product->id) }}"
                                              method="POST"
                                              style="display:inline;">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    onclick="return confirm('Delete this product?')"
                                                    style="cursor:pointer;">
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="10" style="text-align:center;padding:30px;">

                                        No products found.

                                        <br><br>

                                        <a href="{{ route('admin.products.create') }}">
                                            Create First Product
                                        </a>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div style="margin-top:20px;">

                    {{ $products->links() }}

                </div>

            </div>

        </div>
    </div>

</div>

@endsection