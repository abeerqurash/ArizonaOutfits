@php
$isCategory = isset($category) && !isset($post);
$item = $post ?? $category;

$backgroundImage = $isCategory
    ? ($item->image_url ?: asset('asset/media/hero.webp'))
    : ($item->feature_image_url ?: asset('asset/media/hero.webp'));

$itemUrl = $isCategory
    ? route('category-show', $item->slug)
    : route('blog-show', $item->slug);
@endphp

<div class="card-parent">
    <div class="card-image">
        <div class="background-image" style="background-image: url('{{ $backgroundImage }}');">
            <div class="image-overlay"></div>
            <div class="post-link">
                <a href="{{ $itemUrl }}" class="moving-circle">
                    {{ $isCategory ? 'View' : 'Read' }}
                </a>
            </div>
        </div>
    </div>

    <div class="card-information">
        <div class="card-heading-description">
            <h3>
                <a href="{{ $itemUrl }}"
                   class="post-card-description heading fs-18 text-color-dark">
                    {{ $item->title }}
                </a>
            </h3>

            @if($item->expert)
                <p class="experts fs-16 text-color-body">{{ $item->expert }}</p>
            @endif
        </div>

        @if(!$isCategory && $item->categories->count())
            <div class="post-category fs-12 text-color-body letter-space-4px text-uppercase">
                @foreach($item->categories as $categoryLink)
                    <a href="{{ route('category-show', $categoryLink->slug) }}"
                       class="text-decoration-none text-color-body cursor-pointer">
                        {{ $categoryLink->title }}
                    </a>@if(!$loop->last), @endif
                @endforeach
            </div>
        @endif

        @if(!$isCategory)
            <div class="post-date fs-12 letter-space-4px text-color-body text-uppercase">
                {{ $item->created_at->format('m.d.y') }}
            </div>
        @endif

        <div class="post-card-circle"></div>
    </div>
</div>
