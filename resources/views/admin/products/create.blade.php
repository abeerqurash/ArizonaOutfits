@extends('layouts.app')

@section('content')
    <div class="page-wrapper">

        <div class="services">
            <div class="service-wrapper">
                <div class="container">
                    <h1>Create Product</h1>
@if ($errors->any())
    <div style="background:#f8d7da;color:#721c24;padding:15px;margin-bottom:20px;">
        <strong>Errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="text" name="title" id="product_title" placeholder="Product Title" required>
                        <br><br>

                        <input type="text" name="slug" id="product_slug" placeholder="Slug">
                        <br><br>

                        <input type="text" name="sku" placeholder="SKU">
                        <br><br>

                        <textarea name="short_description" placeholder="Short Description"></textarea>
                        <br><br>

                        <textarea name="long_description" placeholder="Long Description"></textarea>
                        <br><br>

                        <textarea name="additional_info" placeholder="Additional Info"></textarea>
                        <br><br>

                        <input type="number" step="0.01" name="regular_price" placeholder="Regular Price" required>
                        <br><br>

                        <input type="number" step="0.01" name="sale_price" placeholder="Sale Price">
                        <br><br>

                        <input type="number" name="stock" placeholder="Stock">
                        <br><br>

                        <select name="status">
                            <option value="active">Active</option>
                            <option value="draft">Draft</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <br><br>

                        <input type="file" name="featured_image" accept="image/*">
                        <br><br>

                        <input type="file" name="gallery_images[]" accept="image/*" multiple>
                        <br>
                        <small>Example: asset/images/p1.jpg, asset/images/p2.jpg</small>
                        <br><br>

                        <h3>Categories</h3>
                        @foreach($categories as $category)
                            <label>
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}">
                                {{ $category->title }}
                            </label>
                            <br>
                        @endforeach

                        <br>

                        <h3>Tags</h3>
                        @foreach($tags as $tag)
                            <label>
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}">
                                {{ $tag->title }}
                            </label>
                            <br>
                        @endforeach

                        <br>

                        <h3>Variable Product Variants</h3>

                        <div id="variants-wrapper">
                            <div class="variant-box">
                                <input type="text" name="variants[0][sku]" placeholder="Variant SKU">
                                <input type="text" name="variants[0][color]" placeholder="Color">
                                <input type="text" name="variants[0][size]" placeholder="Size">
                                <input type="text" name="variants[0][design]" placeholder="Design">
                                <input type="number" step="0.01" name="variants[0][regular_price]"
                                    placeholder="Regular Price">
                                <input type="number" step="0.01" name="variants[0][sale_price]" placeholder="Sale Price">
                                <input type="number" name="variants[0][stock]" placeholder="Stock">
                                <input type="file" name="variants[0][image]" accept="image/*">
                            </div>
                        </div>

                        <button type="button" onclick="addVariant()">Add Variant</button>

                        <br><br>

                        <input type="text" name="meta_title" placeholder="Meta Title">
                        <br><br>

                        <textarea name="meta_description" placeholder="Meta Description"></textarea>
                        <br><br>

                        <textarea name="meta_keywords" placeholder="Meta Keywords"></textarea>
                        <br><br>

                        <button type="submit">Save Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        let variantIndex = 1;

        function addVariant() {
            const wrapper = document.getElementById('variants-wrapper');

            wrapper.insertAdjacentHTML('beforeend', `
                    <div class="variant-box" style="margin-top:15px;">
                        <input type="text" name="variants[${variantIndex}][sku]" placeholder="Variant SKU">
                        <input type="text" name="variants[${variantIndex}][color]" placeholder="Color">
                        <input type="text" name="variants[${variantIndex}][size]" placeholder="Size">
                        <input type="text" name="variants[${variantIndex}][design]" placeholder="Design">
                        <input type="number" step="0.01" name="variants[${variantIndex}][regular_price]" placeholder="Regular Price">
                        <input type="number" step="0.01" name="variants[${variantIndex}][sale_price]" placeholder="Sale Price">
                        <input type="number" name="variants[${variantIndex}][stock]" placeholder="Stock">
                        <input type="file" name="variants[${variantIndex}][image]" accept="image/*">
                    </div>
                `);

            variantIndex++;
        }

        document.getElementById('product_title').addEventListener('input', function () {
            let slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');

            document.getElementById('product_slug').value = slug;
        });
    </script>

@endsection