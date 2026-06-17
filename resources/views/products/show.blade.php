@extends('layouts.app')

@section('title', $product->meta_title ?? $product->title)
@section('meta_description', $product->meta_description ?? '')

@section('content')

<section class="single-product-page">

    <div class="container">

        <ul class="bread-crumbs list-style-none">
            <li><a href="{{ route('home-page') }}">Home</a></li>
            <li><a href="{{ route('products.index') }}">Products</a></li>

            @if($product->categories->count())
                <li>
                    <a href="{{ route('products.category', $product->categories->first()->slug) }}">
                        {{ $product->categories->first()->title }}
                    </a>
                </li>
            @endif

            <li>{{ $product->title }}</li>
        </ul>

        <div class="single-product-main">

            <div class="single-product-images">
                <img src="{{ asset($product->featured_image ?? 'asset/images/no-image.jpg') }}"
                     alt="{{ $product->title }}"
                     class="single-featured-image">

                <div class="single-gallery">
                    @foreach($product->images as $image)
                        <img src="{{ asset($image->image) }}" alt="{{ $product->title }}">
                    @endforeach
                </div>
            </div>

            <div class="single-product-info">

                @if($product->discount_percent)
                    <div class="discount-badge">
                        -{{ $product->discount_percent }}%
                    </div>
                @endif

                <h1>{{ $product->title }}</h1>

                <div class="product-price">
                    @if($product->sale_price)
                        <span class="regular-price">${{ number_format($product->regular_price, 2) }}</span>
                        <span class="sale-price">${{ number_format($product->sale_price, 2) }}</span>
                    @else
                        <span class="sale-price">${{ number_format($product->regular_price, 2) }}</span>
                    @endif
                </div>

                <p>{{ $product->short_description }}</p>

                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf

                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="quantity-box">
                        <input type="number" name="quantity" value="1" min="1">
                    </div>

                    <button type="submit">Add To Cart</button>
                </form>

                <form action="{{ route('favorite.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit">Add To Favorite</button>
                </form>

                <a href="{{ route('checkout.index') }}">Buy Now</a>

                <div class="product-meta">
                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    <p><strong>Categories:</strong> {{ $product->categories->pluck('title')->join(', ') }}</p>
                    <p><strong>Tags:</strong> {{ $product->tags->pluck('title')->join(', ') }}</p>
                </div>

            </div>

        </div>

        <div class="product-tabs">
            <h3>Description</h3>
            {!! $product->long_description !!}

            <h3>Additional Info</h3>
            {!! $product->additional_info !!}

            <h3>Reviews</h3>
            @foreach($product->reviews as $review)
                <div>
                    <strong>{{ $review->name }}</strong>
                    <p>{{ $review->review }}</p>
                </div>
            @endforeach
        </div>

        <h2>Related Products</h2>

        <div class="products-grid">
            @foreach($relatedProducts as $product)
                @include('products.partials.product-card', ['product' => $product])
            @endforeach
        </div>

    </div>

</section>

@endsection