<div class="analytics-card">

    <div class="analytics-card-header">

        <div class="analytics-icon {{ $colour }}">
            <i class="fa-solid {{ $icon }}"></i>
        </div>

        <div>

            <h3>{{ $title }}</h3>

            <span>Top 5 Products</span>

        </div>

    </div>

    <div class="analytics-list">

        @foreach($items as $product)

            @php

                $selling = $product->sale_price && $product->sale_price>0
                    ? $product->sale_price
                    : $product->regular_price;

                $margin = $selling>0
                    ? (($selling-$product->cost_price)/$selling)*100
                    : 0;

            @endphp

            <div class="analytics-row">

                <div>

                    <strong>

                        {{ $product->title }}

                    </strong>

                    <small>

                        {{ $product->stock }} units

                    </small>

                </div>

                <strong>

                    @if($field=='margin')

                        {{ number_format($margin,1) }}%

                    @else

                        £{{ number_format($product->$field,2) }}

                    @endif

                </strong>

            </div>

        @endforeach

    </div>

</div>