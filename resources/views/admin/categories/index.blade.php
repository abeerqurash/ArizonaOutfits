@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Categories</h1>

                <a href="{{ route('admin.categories.create') }}">Add New Category</a>

                @if(session('success'))
                <p>{{ session('success') }}</p>
                @endif

                <table border="1" cellpadding="10">
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Parent</th>
                        <th>Actions</th>
                    </tr>

                    @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->title }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->parent->title ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.categories.edit', $category->id) }}">Edit</a>

                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this category?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </table>

                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>

@endsection