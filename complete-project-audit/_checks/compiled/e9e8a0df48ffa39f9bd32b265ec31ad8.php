<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php
        /*
        |--------------------------------------------------------------------------
        | Central public SEO values
        |--------------------------------------------------------------------------
        |
        | Normal public pages continue using their Blade title/description
        | sections. Individual blog articles use the Post database SEO fields.
        |
        */

        $isBlogPost = request()->routeIs('blog-show') && isset($post);

        $seoTitle = $isBlogPost
            ? ($post->meta_title ?: $post->title)
            : ($title ?? 'Arizona Outfits');

        $seoDescription = $isBlogPost
            ? ($post->meta_description ?: ($post->excerpt ?: ''))
            : ($meta_description ?? '');

        $seoRobots = $isBlogPost
            ? (($post->robots_index ? 'index' : 'noindex') . ', ' . ($post->robots_follow ? 'follow' : 'nofollow'))
            : ($robots ?? 'index, follow');

        $seoCanonical = $isBlogPost
            ? ($post->canonical_url ?: url()->current())
            : null;

        $seoOgTitle = $isBlogPost
            ? ($post->og_title ?: $seoTitle)
            : null;

        $seoOgDescription = $isBlogPost
            ? ($post->og_description ?: $seoDescription)
            : null;

        $seoOgImage = $isBlogPost
            ? ($post->og_image_url ?: $post->feature_image_url)
            : null;
    ?>

    <?php if($isBlogPost): ?>

        <title><?php echo e($seoTitle); ?></title>

        <meta
            name="description"
            content="<?php echo e($seoDescription); ?>"
        >

    <?php else: ?>

        <title>
            <?php echo $__env->yieldContent(
                'title',
                $seoTitle
            ); ?>
        </title>

        <?php if (! empty(trim($__env->yieldContent('meta_description')))): ?>
            <meta
                name="description"
                content="<?php echo $__env->yieldContent('meta_description'); ?>"
            >
        <?php elseif(!empty($seoDescription)): ?>
            <meta
                name="description"
                content="<?php echo e($seoDescription); ?>"
            >
        <?php endif; ?>

    <?php endif; ?>

    <meta
        name="robots"
        content="<?php echo e($seoRobots); ?>"
    >

    <?php if($isBlogPost): ?>

        <link
            rel="canonical"
            href="<?php echo e($seoCanonical); ?>"
        >

        <meta
            property="og:type"
            content="article"
        >

        <meta
            property="og:title"
            content="<?php echo e($seoOgTitle); ?>"
        >

        <meta
            property="og:description"
            content="<?php echo e($seoOgDescription); ?>"
        >

        <meta
            property="og:url"
            content="<?php echo e($seoCanonical); ?>"
        >

        <?php if($seoOgImage): ?>
            <meta
                property="og:image"
                content="<?php echo e($seoOgImage); ?>"
            >

            <meta
                name="twitter:card"
                content="summary_large_image"
            >
        <?php else: ?>
            <meta
                name="twitter:card"
                content="summary"
            >
        <?php endif; ?>

        <meta
            name="twitter:title"
            content="<?php echo e($seoOgTitle); ?>"
        >

        <meta
            name="twitter:description"
            content="<?php echo e($seoOgDescription); ?>"
        >

        <?php if($seoOgImage): ?>
            <meta
                name="twitter:image"
                content="<?php echo e($seoOgImage); ?>"
            >
        <?php endif; ?>

    <?php endif; ?>

    <?php echo $__env->yieldPushContent('head-seo'); ?>

    <link
        rel="stylesheet"
        href="<?php echo e(asset('asset/css/style.css')); ?>"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css"
    >

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

    <script
        src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"
    ></script>

    <script
        src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"
    ></script>

    
    <?php if(request()->routeIs('home-page')): ?>
        <script src="<?php echo e(asset('asset/js/main.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'admin.*',
            'dashboard'
        )
    ): ?>
        <script src="<?php echo e(asset('asset/js/dashboard.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(request()->routeIs('about-page')): ?>
        <script src="<?php echo e(asset('asset/js/about.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'blogs-page',
            'blog-show',
            'categories-page',
            'category-show'
        )
    ): ?>
        <script src="<?php echo e(asset('asset/js/blogs.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(request()->routeIs('contact-page')): ?>
        <script src="<?php echo e(asset('asset/js/contact.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'services-page.index',
            'services-show.show'
        )
    ): ?>
        <script src="<?php echo e(asset('asset/js/services.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'projects-page.index',
            'projects-show.show'
        )
    ): ?>
        <script src="<?php echo e(asset('asset/js/project-main.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(
        request()->routeIs(
            'privacy-policy-page',
            'terms-and-conditions-page'
        )
        || (isset($page) && in_array($page->slug, ['privacy-policy', 'terms-and-conditions'], true))
    ): ?>
        <script src="<?php echo e(asset('asset/js/policy-condtions.js')); ?>"></script>
    <?php endif; ?>

    
    <?php if(request()->routeIs('thank-you')): ?>
        <script src="<?php echo e(asset('asset/js/thankyou.js')); ?>"></script>
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
        <script src="<?php echo e(asset('asset/js/product.js')); ?>"></script>
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('page-scripts'); ?>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\layouts\app.blade.php ENDPATH**/ ?>