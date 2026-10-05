<h1>{{ $post->title }}</h1>

<img src="{{ asset('storage/'.$post->feature_image) }}" alt="{{ $post->title }}">

<p>{!! $post->content ?? '' !!}</p>

<hr>

<h3>Related Posts</h3>

@foreach($relatedPosts as $related)

<div>
    <a href="{{ route('blog-show', $related->slug) }}">
        {{ $related->title }}
    </a>
</div>

@endforeach