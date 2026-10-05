<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php ($needsPhoneInput = str_contains($__env->yieldContent('content'), 'phone-field')); ?>
    <?php ($publicSeo = app(\App\Services\PublicSeoService::class)->values(get_defined_vars(), trim($__env->yieldContent('title')), trim($__env->yieldContent('meta_description')))); ?>
    <title><?php echo e($publicSeo['title']); ?></title>
    <meta name="description" content="<?php echo e($publicSeo['description']); ?>">
    <meta name="robots" content="<?php echo e($publicSeo['robots']); ?>">
    <link rel="canonical" href="<?php echo e($publicSeo['canonical']); ?>">
    <meta property="og:type" content="<?php echo e($publicSeo['type']); ?>">
    <meta property="og:site_name" content="<?php echo e($publicSeo['store']); ?>">
    <meta property="og:title" content="<?php echo e($publicSeo['ogTitle']); ?>">
    <meta property="og:description" content="<?php echo e($publicSeo['ogDescription']); ?>">
    <meta property="og:url" content="<?php echo e($publicSeo['canonical']); ?>">
    <meta name="twitter:card" content="<?php echo e($publicSeo['image'] ? 'summary_large_image' : 'summary'); ?>">
    <meta name="twitter:title" content="<?php echo e($publicSeo['ogTitle']); ?>">
    <meta name="twitter:description" content="<?php echo e($publicSeo['ogDescription']); ?>">
    <?php if($publicSeo['image']): ?>
        <meta property="og:image" content="<?php echo e($publicSeo['image']); ?>">
        <meta name="twitter:image" content="<?php echo e($publicSeo['image']); ?>">
    <?php endif; ?>
    <?php if($publicSeo['graph']): ?>
        <script type="application/ld+json"><?php echo json_encode(['<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>'=>'https://schema.org','@graph'=>$publicSeo['graph']], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?></script>
    <?php endif; ?>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <?php if(request()->routeIs('home-page')): ?>
        <link rel="preload" as="image" href="<?php echo e(app(\App\Services\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),1280)); ?>" fetchpriority="high">
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('head-seo'); ?>

    <?php if(request()->routeIs('home-page')): ?>
        <?php echo $__env->make('partials.home-critical-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <link rel="stylesheet" href="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/css/style.css')); ?>" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/css/style.css')); ?>"></noscript>
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/css/style.css')); ?>">
    <?php endif; ?>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" media="print" onload="this.media='all'"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    <?php if($needsPhoneInput): ?><link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css" media="print" onload="this.media='all'"
    ><?php endif; ?>

    <link rel="stylesheet" href="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/css/card-carousel.css')); ?>">
    <style>:root{--body-display:#606779}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}</style>
    <?php if(request()->routeIs('home-page')): ?>
    <style>.home-hero .background-hero{background-image:url('<?php echo e(app(\App\Services\ResponsiveMediaService::class)->url(asset('asset/media/hero.webp'),1280)); ?>')}</style>
    <?php endif; ?>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"><?php if($needsPhoneInput): ?><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css"><?php endif; ?></noscript>
    <?php echo $__env->yieldPushContent('page-styles'); ?>

</head>

<body
    data-currency-symbol="<?php echo e(app(\App\Services\StoreSettingsService::class)->symbol()); ?>"
    id="<?php echo e(Route::currentRouteName()
            ? str_replace(
                '.',
                '-',
                Route::currentRouteName()
            )
            : 'page'); ?>"
    class="<?php echo e(Route::currentRouteName()
            ? str_replace(
                '.',
                ' ',
                Route::currentRouteName()
            )
            : ''); ?>"
>

    <?php echo $__env->make('partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if($needsPhoneInput): ?><script
        src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js" defer></script><?php endif; ?>

    
    <?php if(request()->routeIs('home-page')): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/main.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'admin.*',
            'dashboard'
        )
    ): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/dashboard.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(request()->routeIs('about-page')): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/about.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'blogs-page',
            'blog-show',
            'categories-page',
            'category-show'
        )
    ): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/blogs.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(request()->routeIs('contact-page')): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/contact.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'services-page.index',
            'services-show.show'
        )
    ): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/services.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'projects-page.index',
            'projects-show.show'
        )
    ): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/project-main.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'privacy-policy-page',
            'terms-and-conditions-page'
        )
        || (isset($page) && in_array($page->slug, ['privacy-policy', 'terms-and-conditions'], true))
    ): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/policy-condtions.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(request()->routeIs('thank-you')): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/thankyou.js')); ?>" defer></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'products.*',
            'cart.*',
            'favorites.*',
            'favorite.*',
            'checkout.*'
        )
    ): ?>
        <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/product.js')); ?>" defer></script>
    <?php endif; ?>

    <script src="<?php echo e(app(\App\Services\PublicAssetService::class)->url('asset/js/card-carousel.js')); ?>" defer></script>
    <?php echo $__env->yieldPushContent('page-scripts'); ?>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../replacement-files/_staged/resources/views\layouts\app.blade.php ENDPATH**/ ?>