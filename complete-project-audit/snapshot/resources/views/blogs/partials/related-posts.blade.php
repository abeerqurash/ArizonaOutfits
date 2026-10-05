<div class="categories-list">
    <div class="list-wrapper">

        <div class="list-item">
            <div class="list-item-inner-2 fs-14 text-color-dark text-uppercase letter-space-4px">
                More from IDEOSTREAM
            </div>
        </div>

        <div class="related-posts">

            @forelse($relatedPosts as $related)

                <div class="related-item">


                    <a href="{{ route('blog-show', $related->slug) }}"
                        class="list-item menu-item fs-16 text-color-body">

                        <div class="list-item-text">
                            {{ $related->title }}
                        </div>

                        <i class="fa-solid fa-arrow-right-long list-item-icon"></i>
                    </a>


                </div>

            @empty

                <div class="related-item">
                    <div class="list-item-text">
                        No related posts found.
                    </div>
                </div>

            @endforelse

        </div>

    </div>
</div>