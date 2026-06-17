@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Product Categories</h1>

                    <a href="{{ route('admin.product-categories.create') }}">Add Category</a>

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
                                    <a href="{{ route('admin.product-categories.edit', $category->id) }}">Edit</a>

                                    <form action="{{ route('admin.product-categories.destroy', $category->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this category?')">
                                            Delete
                                        </button>
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