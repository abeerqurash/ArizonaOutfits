@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Edit Product Category</h1>

                    <form action="{{ route('admin.product-categories.update', $productCategory->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="text" name="title" value="{{ $productCategory->title }}" required>
                        <br><br>

                        <input type="text" name="slug" value="{{ $productCategory->slug }}" required>
                        <br><br>

                        <select name="parent_id">
                            <option value="">No Parent Category</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" {{ $productCategory->parent_id == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>
                        <br><br>

                        <input type="text" name="featured_image" value="{{ $productCategory->featured_image }}"
                            placeholder="Featured Image Path">
                        <br><br>

                        <textarea name="description">{{ $productCategory->description }}</textarea>
                        <br><br>

                        <input type="text" name="meta_title" value="{{ $productCategory->meta_title }}"
                            placeholder="Meta Title">
                        <br><br>

                        <textarea name="meta_description">{{ $productCategory->meta_description }}</textarea>
                        <br><br>

                        <textarea name="meta_keywords">{{ $productCategory->meta_keywords }}</textarea>
                        <br><br>

                        <button type="submit">Update Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection