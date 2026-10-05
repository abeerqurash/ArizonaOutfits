@php
    $author = $post->author;

    $authorImageUrl = null;

    if ($author && $author->profile_image) {
        $authorImageUrl = \Illuminate\Support\Str::startsWith($author->profile_image, ['http://', 'https://'])
            ? $author->profile_image
            : asset('storage/' . ltrim($author->profile_image, '/'));
    }
@endphp

<div class="post-categories">
    <div class="categories-and-title">

        {{-- Dynamic article author --}}
        <div class="author-card">
            <div
                class="author-avator"
                @if($authorImageUrl)
                    style="background-image: url('{{ $authorImageUrl }}');"
                @endif
            >
                @if(!$authorImageUrl)
                    <i class="fa-regular fa-user"></i>
                @endif
            </div>

            <div class="author-information">
                <div class="author-name-title">
                    <div class="author-subtitle fs-12 text-color-dark letter-space-4px">
                        AUTHOR
                    </div>

                    <div class="author-name fs-18 text-color-dark">
                        ArizonaOutfits
                    </div>

                    @if($author && $author->author_title)
                        <div class="fs-12 text-color-body letter-space-3px text-uppercase">
                            {{ $author->author_title }}
                        </div>
                    @endif
                </div>

                @if($author && collect([
                    $author->facebook_url,
                    $author->instagram_url,
                    $author->x_url,
                    $author->linkedin_url,
                    $author->youtube_url,
                ])->filter()->isNotEmpty())
                    <div class="social-wrapper">
                        <div class="social-media">

                            @if($author->facebook_url)
                                <a href="{{ $author->facebook_url }}"
                                   class="icon-and-title text-decoration-none all-border"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="Facebook">
                                    <i class="fa-brands fa-facebook-f text-color-dark"></i>
                                </a>
                            @endif

                            @if($author->instagram_url)
                                <a href="{{ $author->instagram_url }}"
                                   class="icon-and-title text-decoration-none all-border"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="Instagram">
                                    <i class="fa-brands fa-instagram text-color-dark"></i>
                                </a>
                            @endif

                            @if($author->x_url)
                                <a href="{{ $author->x_url }}"
                                   class="icon-and-title text-decoration-none all-border"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="X">
                                    <i class="fa-brands fa-x-twitter text-color-dark"></i>
                                </a>
                            @endif

                            @if($author->linkedin_url)
                                <a href="{{ $author->linkedin_url }}"
                                   class="icon-and-title text-decoration-none all-border"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="LinkedIn">
                                    <i class="fa-brands fa-linkedin text-color-dark"></i>
                                </a>
                            @endif

                            @if($author->youtube_url)
                                <a href="{{ $author->youtube_url }}"
                                   class="icon-and-title text-decoration-none all-border"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="YouTube">
                                    <i class="fa-brands fa-youtube text-color-dark"></i>
                                </a>
                            @endif

                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Latest published articles --}}
        <div class="categories-list">
            <div class="list-wrapper">

                <div class="list-item">
                    <div class="list-item-inner-2 fs-14 text-color-dark text-uppercase letter-space-4px">
                        Latest Articles
                    </div>
                </div>

                <div class="related-posts">
                    @forelse($latestPosts as $latestPost)
                        @php
                            $latestDate = $latestPost->status === \App\Models\Post::STATUS_SCHEDULED
                                ? $latestPost->scheduled_at
                                : $latestPost->published_at;

                            $latestDate = $latestDate ?: $latestPost->created_at;
                        @endphp

                        <article class="related-item arizona-latest-article">
                            <a href="{{ route('blog-show', $latestPost->slug) }}"
                               class="arizona-latest-image-link"
                               aria-label="Read {{ $latestPost->title }}">
                                <div
                                    class="arizona-latest-image"
                                    @if($latestPost->feature_image_url)
                                        style="background-image: url('{{ $latestPost->feature_image_url }}');"
                                    @endif
                                ></div>
                            </a>

                            <div class="arizona-latest-content">
                                <a href="{{ route('blog-show', $latestPost->slug) }}"
                                   class="arizona-latest-title text-decoration-none text-color-dark">
                                    {{ $latestPost->title }}
                                </a>

                                <div class="arizona-latest-meta">
                                    <span>
                                        {{ $latestDate?->format('M d, Y') }}
                                    </span>

                                    <a href="{{ route('blog-show', $latestPost->slug) }}"
                                       class="arizona-latest-read-more text-decoration-none">
                                        Read More
                                        <i class="fa-solid fa-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="related-item">
                            <div class="list-item-text">No latest articles found.</div>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>
</div>

<style>
.arizona-latest-article{
    display:grid;
    grid-template-columns:86px minmax(0,1fr);
    gap:12px;
    align-items:stretch;
    padding:12px 0;
    transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.arizona-latest-article:hover {
    transform: scale(1.03);
    background-color: var(--pin-stripe);
    padding: 12px;
}

.arizona-latest-image-link{
    display:block;
    min-width:0;
}

.arizona-latest-image{
    width:86px;
    height:86px;
    background-position:center;
    background-repeat:no-repeat;
    background-size:cover;
    background-color:#f1f1f1;
    transition: 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.arizona-latest-article:hover .arizona-latest-image{
    transform: scale(1.09);
}

.arizona-latest-image-link:hover .arizona-latest-image {
    transform: scale(1.1);
}

.arizona-latest-content{
    display:flex;
    min-width:0;
    flex-direction:column;
    justify-content:space-between;
    gap:10px;
}

.arizona-latest-title{
    display:-webkit-box;
    overflow:hidden;
    font-size:14px;
    font-weight:600;
    line-height:1.45;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:2;
}

.arizona-latest-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    color: #777;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.arizona-latest-read-more{
    display:inline-flex;
    align-items:center;
    gap:5px;
    color:inherit;
    white-space:nowrap;
    transition: 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.arizona-latest-article:hover .arizona-latest-read-more{
    gap: 20px;
}

.author-avator{
    display:grid;
    place-items:center;
    background-position:center;
    background-repeat:no-repeat;
    background-size:cover;
}

.related-posts {
    padding-left: 25px;
}

@media(max-width:480px){
    .arizona-latest-article{
        grid-template-columns:72px minmax(0,1fr);
    }

    .arizona-latest-image{
        width:72px;
        height:72px;
    }

    .arizona-latest-meta{
        align-items:flex-start;
        flex-direction:column;
    }
}
</style>
