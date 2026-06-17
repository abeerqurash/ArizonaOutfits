@extends('layouts.app')

@section('title', 'Favorites')

@section('content')

<div class="container">

    <h1>Favorites</h1>

    <div class="products-grid">
        @forelse($favorites as $favorite)
            @include('products.partials.product-card', ['product' => $favorite->product])
        @empty
            <p>No favorite products yet.</p>
        @endforelse
    </div>

</div>

@endsection