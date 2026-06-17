@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="services">
        <div class="service-wrapper">
            <div class="container">
                <h1>Edit Product</h1>

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

                <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="text" name="title" id="product_title" value="{{ $product->title }}" required>
                    <br><br>

                    <input type="text" name="slug" id="product_slug" value="{{ $product->slug }}" required>
                    <br><br>

                    <input type="text" name="sku" value="{{ $product->sku }}" placeholder="SKU">
                    <br><br>

                    <textarea name="short_description" placeholder="Short Description">{{ $product->short_description }}</textarea>
                    <br><br>

                    <textarea name="long_description" placeholder="Long Description">{{ $product->long_description }}</textarea>
                    <br><br>

                    <textarea name="additional_info" placeholder="Additional Info">{{ $product->additional_info }}</textarea>
                    <br><br>

                    <input type="number" step="0.01" name="regular_price" value="{{ $product->regular_price }}" required>
                    <br><br>

                    <input type="number" step="0.01" name="sale_price" value="{{ $product->sale_price }}">
                    <br><br>

                    <input type="number" name="stock" value="{{ $product->stock }}">
                    <br><br>

                    <select name="status">
                        <option value="active" {{ $product->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="inactive" {{ $product->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <br><br>

                    <h3>Featured Image</h3>

                    @if($product->featured_image)
                        <img src="{{ asset('storage/' . $product->featured_image) }}" width="120" style="border-radius:8px;">
                        <br><br>
                    @endif

                    <input type="file" name="featured_image" accept="image/*">
                    <br><br>

                    <h3>Gallery Images</h3>

                    @if($product->images->count())
                        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;">
                            @foreach($product->images as $image)
                                <img src="{{ asset('storage/' . $image->image) }}" width="90" height="90" style="object-fit:cover;border-radius:8px;">
                            @endforeach
                        </div>
                    @endif

                    <input type="file" name="gallery_images[]" accept="image/*" multiple>
                    <small>Uploading new gallery images will replace old gallery images.</small>
                    <br><br>

                    <h3>Categories</h3>
                    @foreach($categories as $category)
                        <label>
                            <input type="checkbox"
                                   name="categories[]"
                                   value="{{ $category->id }}"
                                   {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                            {{ $category->title }}
                        </label>
                        <br>
                    @endforeach

                    <br>

                    <h3>Tags</h3>
                    @foreach($tags as $tag)
                        <label>
                            <input type="checkbox"
                                   name="tags[]"
                                   value="{{ $tag->id }}"
                                   {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}>
                            {{ $tag->title }}
                        </label>
                        <br>
                    @endforeach

                    <br>

                    <h3>Variants</h3>

                    <div id="variants-wrapper">
                        @forelse($product->variants as $index => $variant)
                            @php
                                $options = is_array($variant->options)
                                    ? $variant->options
                                    : json_decode($variant->options, true);
                            @endphp

                            <div class="variant-box" style="margin-top:15px;">
                                <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}" placeholder="Variant SKU">
                                <input type="text" name="variants[{{ $index }}][color]" value="{{ $options['Color'] ?? '' }}" placeholder="Color">
                                <input type="text" name="variants[{{ $index }}][size]" value="{{ $options['Size'] ?? '' }}" placeholder="Size">
                                <input type="text" name="variants[{{ $index }}][design]" value="{{ $options['Design'] ?? '' }}" placeholder="Design">
                                <input type="number" step="0.01" name="variants[{{ $index }}][regular_price]" value="{{ $variant->regular_price }}" placeholder="Regular Price">
                                <input type="number" step="0.01" name="variants[{{ $index }}][sale_price]" value="{{ $variant->sale_price }}" placeholder="Sale Price">
                                <input type="number" name="variants[{{ $index }}][stock]" value="{{ $variant->stock }}" placeholder="Stock">

                                @if($variant->image)
                                    <br>
                                    <img src="{{ asset('storage/' . $variant->image) }}" width="70" height="70" style="object-fit:cover;border-radius:8px;">
                                    <br>
                                @endif

                                <input type="hidden" name="variants[{{ $index }}][old_image]" value="{{ $variant->image }}">
                                <input type="file" name="variants[{{ $index }}][image]" accept="image/*">
                            </div>
                        @empty
                            <div class="variant-box">
                                <input type="text" name="variants[0][sku]" placeholder="Variant SKU">
                                <input type="text" name="variants[0][color]" placeholder="Color">
                                <input type="text" name="variants[0][size]" placeholder="Size">
                                <input type="text" name="variants[0][design]" placeholder="Design">
                                <input type="number" step="0.01" name="variants[0][regular_price]" placeholder="Regular Price">
                                <input type="number" step="0.01" name="variants[0][sale_price]" placeholder="Sale Price">
                                <input type="number" name="variants[0][stock]" placeholder="Stock">
                                <input type="file" name="variants[0][image]" accept="image/*">
                            </div>
                        @endforelse
                    </div>

                    <button type="button" onclick="addVariant()">Add Variant</button>

                    <br><br>

                    <input type="text" name="meta_title" value="{{ $product->meta_title }}" placeholder="Meta Title">
                    <br><br>

                    <textarea name="meta_description" placeholder="Meta Description">{{ $product->meta_description }}</textarea>
                    <br><br>

                    <textarea name="meta_keywords" placeholder="Meta Keywords">{{ $product->meta_keywords }}</textarea>
                    <br><br>

                    <button type="submit">Update Product</button>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $variantCount = max($product->variants->count(), 1);
@endphp

<script>
let variantIndex = @json($variantCount);

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