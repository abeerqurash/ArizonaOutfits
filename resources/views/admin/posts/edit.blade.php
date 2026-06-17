@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Edit Post</h1>

                <form action="{{ route('admin.posts.update', $post->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="text" name="title" value="{{ $post->title }}" required>
                    <br><br>

                    <input type="text" name="slug" value="{{ $post->slug }}" required>
                    <br><br>

                    <textarea name="expert">{{ $post->expert }}</textarea>
                    <br><br>

                    <input type="text" name="feature_image" value="{{ $post->feature_image }}">
                    <br><br>

                    <input type="text" name="template" value="{{ $post->template }}">
                    <br><br>

                    <input type="text" name="meta_title" value="{{ $post->meta_title }}">
                    <br><br>

                    <textarea name="meta_description">{{ $post->meta_description }}</textarea>
                    <br><br>

                    <label>Categories</label>
                    <br>

                    @foreach($categories as $category)
                    <label>
                        <input type="checkbox"
                            name="categories[]"
                            value="{{ $category->id }}"
                            {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                        {{ $category->title }}
                    </label>
                    <br>
                    @endforeach

                    <br>

                    <button type="submit">Update Post</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection