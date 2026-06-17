@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Edit Category</h1>

                <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="text" name="title" value="{{ $category->title }}" required>
                    <br><br>

                    <input type="text" name="slug" value="{{ $category->slug }}" required>
                    <br><br>

                    <select name="parent_id">
                        <option value="">No Parent Category</option>

                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
                            {{ $parent->title }}
                        </option>
                        @endforeach
                    </select>
                    <br><br>

                    <input type="text" name="meta_title" value="{{ $category->meta_title }}">
                    <br><br>

                    <textarea name="meta_description">{{ $category->meta_description }}</textarea>
                    <br><br>

                    <textarea name="expert">{{ $category->expert }}</textarea>
                    <br><br>

                    <button type="submit">Update Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection