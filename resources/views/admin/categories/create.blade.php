@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Create Category</h1>

                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf

                    <input type="text" name="title" placeholder="Title" required>
                    <br><br>

                    <input type="text" name="slug" placeholder="Slug">
                    <br><br>

                    <select name="parent_id">
                        <option value="">No Parent Category</option>

                        @foreach($parents as $parent)
                        <option value="{{ $parent->id }}">
                            {{ $parent->title }}
                        </option>
                        @endforeach
                    </select>
                    <br><br>

                    <input type="text" name="meta_title" placeholder="Meta Title">
                    <br><br>

                    <textarea name="meta_description" placeholder="Meta Description"></textarea>
                    <br><br>

                    <textarea name="expert" placeholder="Expert"></textarea>
                    <br><br>

                    <button type="submit">Save Category</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection