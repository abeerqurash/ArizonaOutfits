@if($relatedPosts->isNotEmpty())
<section class="blog-posts service-content az-related-articles"><div class="wrapper"><h2 class="fs-48 text-color-dark">Related Articles</h2><div class="parent-wrapper az-responsive-cards" data-card-carousel aria-label="Related articles">@foreach($relatedPosts->take(6) as $relatedPost) @include('partials.post-card',['post'=>$relatedPost]) @endforeach</div></div></section>
<style>@media(min-width:881px){.az-related-articles .az-responsive-cards{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:20px}.az-related-articles .card-parent{min-width:0;width:100%}}</style>
@endif
