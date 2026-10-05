@php($optimizedMedia=app(\App\Services\ResponsiveMediaService::class))
<style>
.home-hero .background-hero{background-image:url('{{ $optimizedMedia->url(asset('asset/media/hero.webp'),1280) }}')}
.about .banner-cover{background-image:url('{{ $optimizedMedia->url(asset('asset/media/about-info.webp'),1280) }}')}
.what-i-do .background-banner{background-image:url('{{ $optimizedMedia->url(asset('asset/media/what-i-can-do.webp'),1280) }}')}
.recent-projects .image-1{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-1.webp'),1280) }}')}
.recent-projects .image-2{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-2.webp'),1280) }}')}
.recent-projects .image-3{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-3.webp'),1280) }}')}
.recent-projects .image-4{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-4.webp'),1280) }}')}
.recent-projects .image-5{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-5.webp'),1280) }}')}
.recent-projects .image-6{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-6.webp'),1280) }}')}
.testiomonials .testimonial-background-image{background-image:url('{{ $optimizedMedia->url(asset('asset/media/olivia-smith.webp'),1280) }}')}
.news-letter .background-newsletter{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-5.webp'),1280) }}')}
.blog-posts .background-image{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-1.webp'),1280) }}')}
.blog-posts .about-background-image.image-1{background-image:url('{{ $optimizedMedia->url(asset('asset/media/portfolio-abeer.png'),1280) }}')}
.blog-posts .about-background-image.image-2{background-image:url('{{ $optimizedMedia->url(asset('asset/media/about-abeer.png'),1280) }}')}
.blog-posts .about-background-image.image-3{background-image:url('{{ $optimizedMedia->url(asset('asset/media/abeer-about.jpeg'),1280) }}')}
@media(max-width:880px){
.home-hero .background-hero{background-image:url('{{ $optimizedMedia->url(asset('asset/media/hero.webp'),640) }}')}
.about .banner-cover{background-image:url('{{ $optimizedMedia->url(asset('asset/media/about-info.webp'),640) }}')}
.what-i-do .background-banner{background-image:url('{{ $optimizedMedia->url(asset('asset/media/what-i-can-do.webp'),640) }}')}
.recent-projects .image-1{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-1.webp'),640) }}')}
.recent-projects .image-2{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-2.webp'),640) }}')}
.recent-projects .image-3{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-3.webp'),640) }}')}
.recent-projects .image-4{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-4.webp'),640) }}')}
.recent-projects .image-5{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-5.webp'),640) }}')}
.recent-projects .image-6{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-6.webp'),640) }}')}
.testiomonials .testimonial-background-image{background-image:url('{{ $optimizedMedia->url(asset('asset/media/olivia-smith.webp'),640) }}')}
.news-letter .background-newsletter{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-5.webp'),640) }}')}
.blog-posts .background-image{background-image:url('{{ $optimizedMedia->url(asset('asset/media/project-1.webp'),640) }}')}
.blog-posts .about-background-image.image-1{background-image:url('{{ $optimizedMedia->url(asset('asset/media/portfolio-abeer.png'),640) }}')}
.blog-posts .about-background-image.image-2{background-image:url('{{ $optimizedMedia->url(asset('asset/media/about-abeer.png'),640) }}')}
.blog-posts .about-background-image.image-3{background-image:url('{{ $optimizedMedia->url(asset('asset/media/abeer-about.jpeg'),640) }}')}
}
</style>
