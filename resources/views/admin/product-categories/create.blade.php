@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Create Product Category</h1>

                    <form action="{{ route('admin.product-categories.store') }}" method="POST">
                        @csrf

                        <input type="text" name="title" placeholder="Title" required>
                        <br><br>

                        <input type="text" name="slug" placeholder="Slug">
                        <br><br>

                        <select name="parent_id">
                            <option value="">No Parent Category</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                            @endforeach
                        </select>
                        <br><br>

                        <input type="text" name="featured_image" placeholder="Featured Image Path">
                        <br><br>

                        <textarea name="description" placeholder="Description"></textarea>
                        <br><br>

                        <input type="text" name="meta_title" placeholder="Meta Title">
                        <br><br>

                        <textarea name="meta_description" placeholder="Meta Description"></textarea>
                        <br><br>

                        <textarea name="meta_keywords" placeholder="Meta Keywords"></textarea>
                        <br><br>

                        <button type="submit">Save Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection