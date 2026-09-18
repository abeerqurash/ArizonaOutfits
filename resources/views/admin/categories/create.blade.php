@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="services" id="services">

        <div class="service-wrapper">

            <div class="container">

                <h1>Create Category</h1>


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
                    action="{{ route('admin.categories.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >

                    @csrf


                    {{-- Title --}}

                    <label>Category Title</label>

                    <br>

                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="Title"
                        required
                    >

                    <br><br>


                    {{-- Slug --}}

                    <label>Slug</label>

                    <br>

                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug') }}"
                        placeholder="Leave empty to generate automatically"
                    >

                    <br><br>


                    {{-- Parent Category --}}

                    <label>Parent Category</label>

                    <br>

                    <select name="parent_id">

                        <option value="">
                            No Parent Category
                        </option>

                        @foreach($parents as $parent)

                            <option
                                value="{{ $parent->id }}"
                                {{ old('parent_id') == $parent->id ? 'selected' : '' }}
                            >
                                {{ $parent->title }}
                            </option>

                        @endforeach

                    </select>

                    <br><br>


                    {{-- Category Image --}}

                    <label>Category Image</label>

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


                    {{-- Image Preview --}}

                    <div
                        id="image-preview-wrapper"
                        style="display:none; margin-bottom:20px;"
                    >

                        <strong>Preview:</strong>

                        <br><br>

                        <img
                            id="image-preview"
                            src=""
                            alt="Category image preview"
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
                        value="{{ old('meta_title') }}"
                        placeholder="Meta Title"
                    >

                    <br><br>


                    {{-- Meta Description --}}

                    <label>Meta Description</label>

                    <br>

                    <textarea
                        name="meta_description"
                        placeholder="Meta Description"
                    >{{ old('meta_description') }}</textarea>

                    <br><br>


                    <button type="submit">
                        Save Category
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