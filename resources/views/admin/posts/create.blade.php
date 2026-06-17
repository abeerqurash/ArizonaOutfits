@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Create Post</h1>

                <form action="{{ route('admin.posts.store') }}" method="POST">
                    @csrf

                    <input type="text" name="title" placeholder="Title" required>
                    <br><br>

                    <input type="text" name="slug" placeholder="Slug">
                    <br><br>

                    <textarea name="expert" placeholder="Short expert/excerpt"></textarea>
                    <br><br>

                    <input type="text" name="feature_image" placeholder="Feature Image Path">
                    <br><br>

                    <input type="text" name="template" placeholder="Template name / slug">
                    <br><br>

                    <input type="text" name="meta_title" placeholder="Meta Title">
                    <br><br>

                    <textarea name="meta_description" placeholder="Meta Description"></textarea>
                    <br><br>

                    <label>Categories</label>
                    <br>

                    @foreach($categories as $category)
                    <label>
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}">
                        {{ $category->title }}
                    </label>
                    <br>
                    @endforeach

                    <br>

                    <button type="submit">Save Post</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection