@extends('layouts.app')

@section('title', 'Products')

@section('content')

<section class="products-page">

    <div class="container">

        <ul class="bread-crumbs list-style-none">
            <li><a href="{{ route('home-page') }}">Home</a></li>
            <li>Products</li>
        </ul>

        <div class="shop-layout">

            <aside class="shop-sidebar">

                <form method="GET" action="{{ route('products.index') }}">

                    <input type="text" name="search" placeholder="Search products"
                           value="{{ request('search') }}">

                    <h4>Categories</h4>

                    @foreach($categories as $cat)
                        <label>
                            <input type="radio" name="category" value="{{ $cat->slug }}"
                                   {{ request('category') === $cat->slug ? 'checked' : '' }}>
                            {{ $cat->title }}
                        </label>

                        @foreach($cat->children as $child)
                            <label style="padding-left: 15px;">
                                <input type="radio" name="category" value="{{ $child->slug }}"
                                       {{ request('category') === $child->slug ? 'checked' : '' }}>
                                {{ $child->title }}
                            </label>
                        @endforeach
                    @endforeach

                    <h4>Price</h4>

                    <input type="number" name="min_price" placeholder="Min"
                           value="{{ request('min_price') }}">

                    <input type="number" name="max_price" placeholder="Max"
                           value="{{ request('max_price') }}">

                    <button type="submit">Filter</button>

                </form>

            </aside>

            <div class="shop-content">

                <div class="shop-topbar">
                    <div>
                        Showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                        of {{ $products->total() }} products
                    </div>

                    <form method="GET">
                        @foreach(request()->except('sort') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach

                        <select name="sort" onchange="this.form.submit()">
                            <option value="">Latest</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Popularity</option>
                            <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating</option>
                        </select>
                    </form>
                </div>

                <div class="products-grid">
                    @foreach($products as $product)
                        @include('products.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                {{ $products->links() }}

            </div>

        </div>

    </div>

</section>

@endsection