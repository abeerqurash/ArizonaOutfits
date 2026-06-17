@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Product Tags</h1>

                    <a href="{{ route('admin.product-tags.create') }}">Add Tag</a>

                    @if(session('success'))
                        <p>{{ session('success') }}</p>
                    @endif

                    <table border="1" cellpadding="10">
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Actions</th>
                        </tr>

                        @foreach($tags as $tag)
                            <tr>
                                <td>{{ $tag->title }}</td>
                                <td>{{ $tag->slug }}</td>
                                <td>
                                    <a href="{{ route('admin.product-tags.edit', $tag->id) }}">Edit</a>

                                    <form action="{{ route('admin.product-tags.destroy', $tag->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this tag?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    {{ $tags->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection