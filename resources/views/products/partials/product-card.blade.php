<div class="product-card">

    <div class="product-image-box">

        @if($product->discount_percent)
            <span class="discount-badge">
                -{{ $product->discount_percent }}%
            </span>
        @endif

        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ asset($product->featured_image ?? 'asset/images/no-image.jpg') }}"
                 alt="{{ $product->title }}"
                 class="product-main-image">
        </a>

        @if($product->images->count())
            <div class="product-gallery-grid">
                @foreach($product->images->take(5) as $image)
                    <img src="{{ asset($image->image) }}" alt="{{ $product->title }}">
                @endforeach
            </div>
        @endif

    </div>

    <div class="product-card-content">

        <div class="product-categories">
            {{ $product->categories->pluck('title')->join(', ') }}
        </div>

        <h3 class="product-title">
            <a href="{{ route('products.show', $product->slug) }}">
                {{ $product->title }}
            </a>
        </h3>

        <div class="product-price">
            @if($product->sale_price)
                <span class="regular-price">${{ number_format($product->regular_price, 2) }}</span>
                <span class="sale-price">${{ number_format($product->sale_price, 2) }}</span>
            @else
                <span class="sale-price">${{ number_format($product->regular_price, 2) }}</span>
            @endif
        </div>

        <div class="product-actions">
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="add-cart-btn">
                    Add To Cart
                </button>
            </form>

            <form action="{{ route('favorite.toggle') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="favorite-btn">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </form>
        </div>

    </div>
</div>