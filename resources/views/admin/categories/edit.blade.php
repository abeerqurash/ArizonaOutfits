@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="services" id="services">

        <div class="service-wrapper">

            <div class="container">

                <h1>Edit Category</h1>


                {{-- Validation Errors --}}

                @if ($errors->any())

                    <div style="margin-bottom:20px; color:red;">

                        <strong>Please fix the following errors:</strong>

                        <ul>

                            @foreach ($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                <form
                    action="{{ route('admin.categories.update', $category->id) }}"
                    method="POST"
                    enctype="multipart/form-data"
                >

                    @csrf

                    @method('PUT')


                    {{-- Title --}}

                    <label>Category Title</label>

                    <br>

                    <input
                        type="text"
                        name="title"
                        value="{{ old('title', $category->title) }}"
                        required
                    >

                    <br><br>


                    {{-- Slug --}}

                    <label>Slug</label>

                    <br>

                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug', $category->slug) }}"
                        required
                    >

                    <br><br>


                    {{-- Parent --}}

                    <label>Parent Category</label>

                    <br>

                    <select name="parent_id">

                        <option value="">
                            No Parent Category
                        </option>

                        @foreach($parents as $parent)

                            <option
                                value="{{ $parent->id }}"
                                {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}
                            >
                                {{ $parent->title }}
                            </option>

                        @endforeach

                    </select>

                    <br><br>


                    {{-- Current Image --}}

                    @if($category->image)

                        <div style="margin-bottom:20px;">

                            <strong>Current Image:</strong>

                            <br><br>

                            <img
                                src="{{ asset('storage/' . $category->image) }}"
                                alt="{{ $category->title }}"
                                id="current-category-image"
                                style="
                                    width:200px;
                                    max-width:100%;
                                    height:150px;
                                    object-fit:cover;
                                    border:1px solid #ddd;
                                    border-radius:6px;
                                "
                            >

                            <br><br>

                            <label>

                                <input
                                    type="checkbox"
                                    name="remove_image"
                                    value="1"
                                    {{ old('remove_image') ? 'checked' : '' }}
                                >

                                Remove current image

                            </label>

                        </div>

                    @endif


                    {{-- New Image --}}

                    <label>
                        {{ $category->image ? 'Replace Category Image' : 'Add Category Image' }}
                    </label>

                    <br>

                    <input
                        type="file"
                        name="image"
                        id="category-image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    <br>

                    <small>
                        JPG, JPEG, PNG or WebP. Maximum 5MB.
                    </small>

                    <br><br>


                    {{-- New Image Preview --}}

                    <div
                        id="image-preview-wrapper"
                        style="display:none; margin-bottom:20px;"
                    >

                        <strong>New Image Preview:</strong>

                        <br><br>

                        <img
                            id="image-preview"
                            src=""
                            alt="New category image preview"
                            style="
                                width:200px;
                                max-width:100%;
                                height:150px;
                                object-fit:cover;
                                border:1px solid #ddd;
                                border-radius:6px;
                            "
                        >

                    </div>


                    {{-- Meta Title --}}

                    <label>Meta Title</label>

                    <br>

                    <input
                        type="text"
                        name="meta_title"
                        value="{{ old('meta_title', $category->meta_title) }}"
                    >

                    <br><br>


                    {{-- Meta Description --}}

                    <label>Meta Description</label>

                    <br>

                    <textarea
                        name="meta_description"
                    >{{ old('meta_description', $category->meta_description) }}</textarea>

                    <br><br>


                    {{-- Expert --}}

                    <label>Expert</label>

                    <br>

                    <textarea
                        name="expert"
                    >{{ old('expert', $category->expert) }}</textarea>

                    <br><br>


                    <button type="submit">
                        Update Category
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const imageInput = document.getElementById(
        'category-image'
    );

    const previewWrapper = document.getElementById(
        'image-preview-wrapper'
    );

    const preview = document.getElementById(
        'image-preview'
    );

    if (!imageInput || !previewWrapper || !preview) {
        return;
    }

    imageInput.addEventListener('change', function () {

        const file = this.files[0];

        if (!file) {

            preview.src = '';

            previewWrapper.style.display = 'none';

            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {

            preview.src = event.target.result;

            previewWrapper.style.display = 'block';
        };

        reader.readAsDataURL(file);
    });

});
</script>

@endsection