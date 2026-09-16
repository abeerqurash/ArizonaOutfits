@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="services" id="services">

        <div class="service-wrapper">

            <div class="container">

                <h1>Categories</h1>

                <a href="{{ route('admin.categories.create') }}">
                    Add New Category
                </a>

                <br><br>


                @if(session('success'))

                    <p style="color:green;">
                        {{ session('success') }}
                    </p>

                @endif


                <table
                    border="1"
                    cellpadding="10"
                    cellspacing="0"
                >

                    <thead>

                        <tr>

                            <th>Image</th>

                            <th>Title</th>

                            <th>Slug</th>

                            <th>Parent</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($categories as $category)

                            <tr>

                                {{-- Image --}}

                                <td>

                                    @if($category->image)

                                        <img
                                            src="{{ asset('storage/' . $category->image) }}"
                                            alt="{{ $category->title }}"
                                            style="
                                                width:80px;
                                                height:60px;
                                                object-fit:cover;
                                                border-radius:4px;
                                            "
                                        >

                                    @else

                                        <span>No image</span>

                                    @endif

                                </td>


                                {{-- Title --}}

                                <td>
                                    {{ $category->title }}
                                </td>


                                {{-- Slug --}}

                                <td>
                                    {{ $category->slug }}
                                </td>


                                {{-- Parent --}}

                                <td>
                                    {{ $category->parent->title ?? '-' }}
                                </td>


                                {{-- Actions --}}

                                <td>

                                    <a
                                        href="{{ route('admin.categories.edit', $category->id) }}"
                                    >
                                        Edit
                                    </a>


                                    <form
                                        action="{{ route('admin.categories.destroy', $category->id) }}"
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Delete this category?')"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    style="text-align:center;"
                                >
                                    No categories found.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>


                <br>

                {{ $categories->links() }}

            </div>

        </div>

    </div>

</div>

@endsection