@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services" id="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Posts</h1>

                <a href="{{ route('admin.posts.create') }}">Add New Post</a>

                @if(session('success'))
                <p>{{ session('success') }}</p>
                @endif

                <table border="1" cellpadding="10">
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Categories</th>
                        <th>Actions</th>
                    </tr>

                    @foreach($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->slug }}</td>
                        <td>
                            {{ $post->categories->pluck('title')->join(', ') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.posts.edit', $post->id) }}">Edit</a>

                            <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this post?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </table>

                {{ $posts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection