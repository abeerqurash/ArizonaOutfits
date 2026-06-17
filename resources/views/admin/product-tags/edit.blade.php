@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Edit Product Tag</h1>

                    <form action="{{ route('admin.product-tags.update', $productTag->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="text" name="title" value="{{ $productTag->title }}" required>
                        <br><br>

                        <input type="text" name="slug" value="{{ $productTag->slug }}" required>
                        <br><br>

                        <button type="submit">Update Tag</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection