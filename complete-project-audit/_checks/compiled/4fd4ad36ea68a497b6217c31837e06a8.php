<?php
    $headerCart = (array) session('cart', []);

    $headerCartCount = collect($headerCart)->sum(function ($item) {
        return max(0, (int) ($item['quantity'] ?? 0));
    });

    $headerCartSubtotal = collect($headerCart)->sum(function ($item) {
        return (float) ($item['price'] ?? 0)
            * max(1, (int) ($item['quantity'] ?? 1));
    });

    $headerFavoriteCount = 0;

    if (auth('web')->check()) {
        $headerFavoriteCount = \App\Models\Favorite::query()
            ->where('user_id', auth('web')->id())
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->count();
    }
?>
<?php
    $configuredMenuItems=$navigationMenuItems??collect();
    $menuBehaviors=$configuredMenuItems->map(fn($item)=>$item->commerce_behavior)->filter();
?>
<?php $__empty_1 = true; $__currentLoopData = $configuredMenuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menuItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<a href="<?php echo e($menuItem->resolved_url); ?>" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header header-commerce-nav-link" <?php if($menuItem->commerce_behavior==='cart_sidebar'): ?> data-header-cart-trigger <?php endif; ?> <?php if($menuItem->open_in_new_tab && !$menuItem->commerce_behavior): ?> target="_blank" rel="noopener" <?php endif; ?>>
<div class="button-text text-uppercase letter-space-3px header-commerce-nav-text">
<?php if($menuItem->commerce_behavior==='favorites'): ?><i class="fa-regular fa-heart" aria-hidden="true"></i><?php endif; ?>
<?php if(in_array($menuItem->commerce_behavior,['cart_page','cart_sidebar'],true)): ?><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><?php endif; ?>
<span><?php echo e($menuItem->display_label); ?></span>
<?php if($menuItem->commerce_behavior==='favorites' && auth('web')->check()): ?><span data-header-favorite-count class="header-commerce-nav-count <?php echo e($headerFavoriteCount<1?'is-empty':''); ?>"><?php echo e($headerFavoriteCount); ?></span><?php endif; ?>
<?php if(in_array($menuItem->commerce_behavior,['cart_page','cart_sidebar'],true)): ?><span data-header-cart-count class="header-commerce-nav-count <?php echo e($headerCartCount<1?'is-empty':''); ?>"><?php echo e($headerCartCount); ?></span><?php endif; ?>
</div></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<?php $__currentLoopData = ['about-page'=>'About Us','blogs-page'=>'Blogs','products.index'=>'Shop','contact-page'=>'Contact']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<a href="<?php echo e(route($name)); ?>" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px"><?php echo e($label); ?></div></a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php if(!$menuBehaviors->contains('favorites')): ?>
<a href="<?php echo e(route(auth('web')->check()?'favorites.index':'login')); ?>" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header header-commerce-nav-link"><div class="button-text text-uppercase letter-space-3px header-commerce-nav-text"><i class="fa-regular fa-heart" aria-hidden="true"></i><span>Favorites</span><?php if(auth()->guard('web')->check()): ?><span data-header-favorite-count class="header-commerce-nav-count <?php echo e($headerFavoriteCount<1?'is-empty':''); ?>"><?php echo e($headerFavoriteCount); ?></span><?php endif; ?></div></a>
<?php endif; ?>
<?php if(!$menuBehaviors->contains('cart_page') && !$menuBehaviors->contains('cart_sidebar')): ?>
<a href="<?php echo e(route('cart.index')); ?>" data-header-cart-trigger class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header header-commerce-nav-link"><div class="button-text text-uppercase letter-space-3px header-commerce-nav-text"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><span>Cart</span><span data-header-cart-count class="header-commerce-nav-count <?php echo e($headerCartCount<1?'is-empty':''); ?>"><?php echo e($headerCartCount); ?></span></div></a>
<?php endif; ?>
<?php if(!$menuBehaviors->contains('account')): ?>
<a href="<?php echo e(route(auth('web')->check()?'customer.dashboard':'login')); ?>" class="btn-style-3 fs-12 text-color-white justify-self-start nav-links-header"><div class="button-text text-uppercase letter-space-3px"><?php echo e(auth('web')->check()?'My Account':'Login'); ?></div></a>
<?php endif; ?>


<div
    class="header-cart-drawer"
    data-cart-drawer
    aria-hidden="true"
>
    <button
        type="button"
        class="header-cart-drawer-backdrop"
        data-cart-drawer-close
        aria-label="Close shopping cart"
        tabindex="-1"
    ></button>

    <aside
        class="header-cart-drawer-panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="header-cart-drawer-title"
    >
        <header class="header-cart-drawer-header">
            <div>
                <span class="header-cart-drawer-eyebrow text-uppercase letter-space-3px">
                    Your Bag
                </span>

                <h2
                    id="header-cart-drawer-title"
                    class="header-cart-drawer-title"
                >
                    Shopping Cart
                </h2>
            </div>

            <button
                type="button"
                class="header-cart-drawer-close"
                data-cart-drawer-close
                aria-label="Close shopping cart"
            >
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="header-cart-drawer-body" data-cart-drawer-body>
            <?php $__empty_1 = true; $__currentLoopData = $headerCart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cartKey => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $drawerTitle = $item['title'] ?? 'Product';
                    $drawerSlug = $item['slug'] ?? null;
                    $drawerPrice = (float) ($item['price'] ?? 0);
                    $drawerRegularPrice = (float) ($item['regular_price'] ?? $drawerPrice);
                    $drawerQuantity = max(1, (int) ($item['quantity'] ?? 1));
                    $drawerStock = max(0, (int) ($item['stock'] ?? 0));
                    $drawerOptions = is_array($item['options'] ?? null)
                        ? $item['options']
                        : [];

                    $drawerImage = $item['image'] ?? 'asset/images/no-image.jpg';

                    if (
                        !str_starts_with($drawerImage, 'http://')
                        && !str_starts_with($drawerImage, 'https://')
                    ) {
                        $normalizedDrawerImage = ltrim($drawerImage, '/');

                        if (str_starts_with($normalizedDrawerImage, 'storage/')) {
                            $drawerImage = asset($normalizedDrawerImage);
                        } elseif (file_exists(public_path($normalizedDrawerImage))) {
                            $drawerImage = asset($normalizedDrawerImage);
                        } else {
                            $drawerImage = asset('storage/' . $normalizedDrawerImage);
                        }
                    }

                    $drawerProductUrl = $drawerSlug
                        ? route('products.show', $drawerSlug)
                        : route('products.index');

                    $drawerHasDiscount =
                        $drawerRegularPrice > 0
                        && $drawerPrice > 0
                        && $drawerPrice < $drawerRegularPrice;
                ?>

                <article
                    class="header-cart-drawer-item"
                    data-cart-drawer-item
                    data-cart-key="<?php echo e($cartKey); ?>"
                >
                    <a
                        href="<?php echo e($drawerProductUrl); ?>"
                        class="header-cart-drawer-image"
                        aria-label="View <?php echo e($drawerTitle); ?>"
                    >
                        <img
                            src="<?php echo e($drawerImage); ?>"
                            alt="<?php echo e($drawerTitle); ?>"
                            width="112"
                            height="132"
                            loading="lazy"
                        >
                    </a>

                    <div class="header-cart-drawer-item-content">
                        <div class="header-cart-drawer-item-top">
                            <div>
                                <h3 class="header-cart-drawer-item-title">
                                    <a href="<?php echo e($drawerProductUrl); ?>">
                                        <?php echo e($drawerTitle); ?>

                                    </a>
                                </h3>

                                <?php if(!empty($item['sku'])): ?>
                                    <div class="header-cart-drawer-sku">
                                        SKU: <?php echo e($item['sku']); ?>

                                    </div>
                                <?php endif; ?>
                            </div>

                            <form
                                action="<?php echo e(route('cart.remove')); ?>"
                                method="POST"
                                class="header-cart-drawer-remove-form"
                            >
                                <?php echo csrf_field(); ?>

                                <input
                                    type="hidden"
                                    name="cart_key"
                                    value="<?php echo e($cartKey); ?>"
                                >

                                <button
                                    type="submit"
                                    class="header-cart-drawer-remove btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                    data-drawer-remove
                                    data-cart-key="<?php echo e($cartKey); ?>"
                                    data-ajax="true"
                                    aria-label="Remove <?php echo e($drawerTitle); ?> from cart"
                                >
                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                        Remove
                                    </div>
                                </button>
                            </form>
                        </div>

                        <?php if(!empty($drawerOptions)): ?>
                            <div class="header-cart-drawer-options">
                                <?php $__currentLoopData = $drawerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $drawerOptionName =
                                            $option['option_name']
                                            ?? $option['name']
                                            ?? 'Option';

                                        $drawerOptionValue =
                                            $option['value_label']
                                            ?? $option['value']
                                            ?? '';
                                    ?>

                                    <?php if($drawerOptionValue !== ''): ?>
                                        <span>
                                            <strong><?php echo e($drawerOptionName); ?>:</strong>
                                            <?php echo e($drawerOptionValue); ?>

                                        </span>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                        <div class="header-cart-drawer-meta">
                            <div
                                class="header-cart-drawer-quantity"
                                data-drawer-quantity
                            >
                                <button
                                    type="button"
                                    class="header-cart-drawer-quantity-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                    data-drawer-quantity-minus
                                    aria-label="Decrease quantity of <?php echo e($drawerTitle); ?>"
                                    <?php if($drawerQuantity <= 1): echo 'disabled'; endif; ?>
                                >
                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">−</div>
                                </button>

                                <input
                                    type="number"
                                    class="header-cart-drawer-quantity-input fs-16 text-uppercase letter-space-4px"
                                    value="<?php echo e($drawerQuantity); ?>"
                                    min="1"
                                    <?php if($drawerStock > 0): ?>
                                        max="<?php echo e($drawerStock); ?>"
                                    <?php endif; ?>
                                    inputmode="numeric"
                                    data-drawer-quantity-input
                                    data-cart-key="<?php echo e($cartKey); ?>"
                                    data-update-url="<?php echo e(route('cart.update')); ?>"
                                    aria-label="Quantity of <?php echo e($drawerTitle); ?>"
                                >

                                <button
                                    type="button"
                                    class="header-cart-drawer-quantity-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                    data-drawer-quantity-plus
                                    aria-label="Increase quantity of <?php echo e($drawerTitle); ?>"
                                    <?php if(
                                        $drawerStock < 1
                                        || $drawerQuantity >= $drawerStock
                                    ): echo 'disabled'; endif; ?>
                                >
                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">+</div>
                                </button>
                            </div>

                            <?php if($drawerStock > 0): ?>
                                <span class="is-in-stock">
                                    In Stock
                                </span>
                            <?php else: ?>
                                <span class="is-out-of-stock">
                                    Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="header-cart-drawer-price">
                            <strong>
                                $<?php echo e(number_format($drawerPrice, 2)); ?>

                            </strong>

                            <?php if($drawerHasDiscount): ?>
                                <del>
                                    $<?php echo e(number_format($drawerRegularPrice, 2)); ?>

                                </del>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="header-cart-drawer-empty">
                    <span class="header-cart-drawer-empty-icon">
                        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                    </span>

                    <h3>Your cart is empty</h3>

                    <p>
                        Add a product and it will appear here.
                    </p>

                    <a
                        href="<?php echo e(route('products.index')); ?>"
                        class="header-cart-drawer-shop btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                    >
                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                            Continue Shopping
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if($headerCartCount > 0): ?>
            <footer class="header-cart-drawer-footer">
                <div class="header-cart-drawer-summary">
                    <span>
                        Subtotal
                    </span>

                    <strong data-header-cart-subtotal>
                        $<?php echo e(number_format($headerCartSubtotal, 2)); ?>

                    </strong>
                </div>

                <p class="header-cart-drawer-note">
                    Shipping, discounts and taxes are calculated during checkout.
                </p>

                <div class="header-cart-drawer-actions">
                    <a
                        href="<?php echo e(route('cart.index')); ?>"
                        class="header-cart-drawer-button secondary btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                    >
                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                            View Cart
                        </div>
                    </a>

                    <a
                        href="<?php echo e(route('checkout.index')); ?>"
                        class="header-cart-drawer-button primary btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                    >
                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                            Checkout
                        </div>
                    </a>
                </div>
            </footer>
        <?php endif; ?>
    </aside>
</div>

<style>
    /*
    |--------------------------------------------------------------------------
    | Header Cart + Favourites
    |--------------------------------------------------------------------------
    | Use the SAME nav-link/button system as Contact/Login.
    | Default: plain menu item.
    | Hover/focus: existing btn-style-3 hover treatment.
    */

    .header-commerce-nav-link {
        position: relative;
    }

    .header-commerce-nav-text {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }

    .header-commerce-nav-text > i {
        font-size: 12px;
        line-height: 1;
        flex: 0 0 auto;
    }

    .header-commerce-nav-count {
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #ffffff;
        color: #111111;
        font-size: 8px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: 0;
    }

    .header-commerce-nav-count.is-empty {
        display: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Header Cart Drawer
    |--------------------------------------------------------------------------
    */

    html.header-cart-drawer-open,
    body.header-cart-drawer-open {
        overflow: hidden;
    }

    .header-cart-drawer {
        position: fixed;
        inset: 0;
        z-index: 100050;
        visibility: hidden;
        pointer-events: none;
    }

    .header-cart-drawer.is-open {
        visibility: visible;
        pointer-events: auto;
    }

    .header-cart-drawer-backdrop {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        padding: 0;
        border: 0;
        background: rgba(9, 13, 22, 0.62);
        opacity: 0;
        cursor: pointer;
        transition: opacity 220ms ease;
    }

    .header-cart-drawer.is-open .header-cart-drawer-backdrop {
        opacity: 1;
    }

    .header-cart-drawer-panel {
        position: absolute;
        top: 0;
        right: 0;
        width: min(50vw, 760px);
        min-width: 520px;
        height: 100dvh;
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        background: #ffffff;
        color: #172033;
        box-shadow: -20px 0 60px rgba(9, 13, 22, 0.18);
        transform: translateX(100%);
        transition: transform 260ms ease;
        overflow: hidden;
    }

    .header-cart-drawer.is-open .header-cart-drawer-panel {
        transform: translateX(0);
    }

    .header-cart-drawer-header {
        min-height: 92px;
        padding: 22px 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        border-bottom: 1px solid #e5eaf1;
    }

    .header-cart-drawer-eyebrow {
        display: block;
        margin-bottom: 5px;
        color: #635bff;
        font-size: 9px;
        font-weight: 800;
    }

    .header-cart-drawer-title {
        margin: 0;
        color: #172033;
        font-size: clamp(24px, 2vw, 32px);
        line-height: 1.1;
        font-weight: 800;
    }

    .header-cart-drawer-close {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5eaf1;
        border-radius: 10px;
        background: #f7f8fb;
        color: #172033;
        font-size: 17px;
        cursor: pointer;
        transition: background 180ms ease, transform 180ms ease;
    }

    .header-cart-drawer-close:hover,
    .header-cart-drawer-close:focus-visible {
        background: #eef1f6;
        transform: translateY(-1px);
        outline: none;
    }

    .header-cart-drawer-body {
        min-height: 0;
        padding: 8px 26px 24px;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    .header-cart-drawer-item {
        display: grid;
        grid-template-columns: 112px minmax(0, 1fr);
        gap: 18px;
        padding: 20px 0;
        border-bottom: 1px solid #e5eaf1;
    }

    .header-cart-drawer-image {
        display: block;
        width: 112px;
        height: 132px;
        overflow: hidden;
        border-radius: 10px;
        background: #f3f5f8;
    }

    .header-cart-drawer-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .header-cart-drawer-item-content {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .header-cart-drawer-item-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .header-cart-drawer-item-title {
        margin: 0 0 5px;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 800;
    }

    .header-cart-drawer-item-title a {
        color: #172033;
        text-decoration: none;
    }

    .header-cart-drawer-sku {
        color: #6b7280;
        font-size: 10px;
        line-height: 1.4;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }

    .header-cart-drawer-remove-form {
        margin: 0;
    }

    .header-cart-drawer-remove {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5eaf1;
        border-radius: 8px;
        background: #ffffff;
        color: #be123c;
        cursor: pointer;
    }

    .header-cart-drawer-remove:hover,
    .header-cart-drawer-remove:focus-visible {
        background: #fff1f2;
        outline: none;
    }

    .header-cart-drawer-options {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .header-cart-drawer-options span {
        padding: 5px 8px;
        border: 1px solid #e5eaf1;
        border-radius: 999px;
        background: #f7f8fb;
        color: #4b5563;
        font-size: 10px;
        line-height: 1.2;
    }

    .header-cart-drawer-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        color: #6b7280;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .header-cart-drawer-quantity {
        display: grid;
        grid-template-columns: 44px 54px 44px;
        align-items: stretch;
        min-height: 44px;
        overflow: hidden;
        border: 1px solid #dfe3eb;
        background: #ffffff;
    }

    .header-cart-drawer-quantity-button {
        position: relative;
        width: 44px;
        min-width: 44px;
        height: 44px;
        min-height: 44px;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: #080d20;
        color: #ffffff;
        overflow: hidden;
        cursor: pointer;
    }

    .header-cart-drawer-quantity-button .button-text {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .header-cart-drawer-quantity-button:disabled {
        background: #858994;
        opacity: .55;
        cursor: not-allowed;
    }

    .header-cart-drawer-quantity-input {
        width: 54px;
        height: 44px;
        min-height: 44px;
        padding: 0 4px;
        border: 0;
        border-left: 8px solid #ffffff;
        border-right: 8px solid #ffffff;
        background: #f2f5fb;
        color: #080d20;
        font-weight: 700;
        text-align: center;
        outline: none;
        appearance: textfield;
        -moz-appearance: textfield;
    }

    .header-cart-drawer-quantity-input::-webkit-inner-spin-button,
    .header-cart-drawer-quantity-input::-webkit-outer-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }

    .header-cart-drawer-remove {
        position: relative;
        width: auto;
        min-width: 84px;
        height: 36px;
        padding: 0 12px;
        border: 0;
        border-radius: 0;
        background: #080d20;
        color: #ffffff;
        overflow: hidden;
    }

    .header-cart-drawer-remove .button-text {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        pointer-events: none;
    }

    .header-cart-drawer-item.is-updating,
    .header-cart-drawer-item.is-removing {
        opacity: .58;
        pointer-events: none;
    }

    .header-cart-drawer-meta .is-in-stock {
        color: #047857;
    }

    .header-cart-drawer-meta .is-out-of-stock {
        color: #be123c;
    }

    .header-cart-drawer-price {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: auto;
        font-size: 14px;
    }

    .header-cart-drawer-price strong {
        color: #172033;
    }

    .header-cart-drawer-price del {
        color: #8a94a6;
    }

    .header-cart-drawer-empty {
        min-height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        text-align: center;
    }

    .header-cart-drawer-empty-icon {
        width: 62px;
        height: 62px;
        margin-bottom: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f0efff;
        color: #635bff;
        font-size: 22px;
    }

    .header-cart-drawer-empty h3 {
        margin: 0 0 8px;
        font-size: 20px;
    }

    .header-cart-drawer-empty p {
        margin: 0 0 20px;
        color: #6b7280;
        font-size: 13px;
    }

    .header-cart-drawer-shop,
    .header-cart-drawer-button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        text-decoration: none;
    }

    .header-cart-drawer-shop,
    .header-cart-drawer-button.primary {
        background: #172033;
        color: #ffffff;
    }

    .header-cart-drawer-button.secondary {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #172033;
    }

    .header-cart-drawer-shop,
    .header-cart-drawer-button,
    .header-cart-drawer-button.secondary,
    .header-cart-drawer-button.primary {
        position: relative;
        min-height: 48px;
        border: 0;
        border-radius: 0;
        background: #080d20;
        color: #ffffff;
        overflow: hidden;
    }

    .header-cart-drawer-shop .button-text,
    .header-cart-drawer-button .button-text {
        width: 100%;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .header-cart-drawer-footer {
        padding: 20px 26px 24px;
        border-top: 1px solid #e5eaf1;
        background: #ffffff;
    }

    .header-cart-drawer-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 7px;
        font-size: 15px;
    }

    .header-cart-drawer-summary strong {
        font-size: 18px;
    }

    .header-cart-drawer-note {
        margin: 0 0 16px;
        color: #6b7280;
        font-size: 11px;
        line-height: 1.5;
    }

    .header-cart-drawer-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    @media (max-width: 900px) {
        .header-cart-drawer-panel {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 520px) {
        .header-cart-drawer-header {
            min-height: 78px;
            padding: 18px;
        }

        .header-cart-drawer-body {
            padding: 4px 18px 20px;
        }

        .header-cart-drawer-item {
            grid-template-columns: 88px minmax(0, 1fr);
            gap: 13px;
            padding: 16px 0;
        }

        .header-cart-drawer-image {
            width: 88px;
            height: 108px;
        }

        .header-cart-drawer-footer {
            padding: 16px 18px 18px;
        }

        .header-cart-drawer-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .header-cart-drawer-backdrop,
        .header-cart-drawer-panel {
            transition: none;
        }
    }

</style>


<script>
(() => {
    "use strict";

    const drawer = document.querySelector("[data-cart-drawer]");
    const triggers = document.querySelectorAll("[data-header-cart-trigger]");

    if (!drawer || !triggers.length) {
        return;
    }

    const panel = drawer.querySelector(".header-cart-drawer-panel");
    const closeButtons = drawer.querySelectorAll("[data-cart-drawer-close]");
    let lastFocusedElement = null;

    function openCartDrawer() {
        lastFocusedElement = document.activeElement;

        drawer.classList.add("is-open");
        drawer.setAttribute("aria-hidden", "false");

        document.documentElement.classList.add("header-cart-drawer-open");
        document.body.classList.add("header-cart-drawer-open");

        window.requestAnimationFrame(function () {
            drawer.querySelector(".header-cart-drawer-close")?.focus();
        });
    }

    function closeCartDrawer() {
        drawer.classList.remove("is-open");
        drawer.setAttribute("aria-hidden", "true");

        document.documentElement.classList.remove("header-cart-drawer-open");
        document.body.classList.remove("header-cart-drawer-open");

        if (
            lastFocusedElement
            && typeof lastFocusedElement.focus === "function"
        ) {
            lastFocusedElement.focus();
        }
    }

    function bindDrawerMovingButtons() {
        drawer.querySelectorAll(
            ".header-cart-drawer-quantity-button, " +
            ".header-cart-drawer-remove, " +
            ".header-cart-drawer-shop, " +
            ".header-cart-drawer-button"
        ).forEach(function (button) {
            if (button.dataset.drawerMotionBound === "true") {
                return;
            }

            const buttonText = button.querySelector(".button-text");

            if (!buttonText) {
                return;
            }

            button.dataset.drawerMotionBound = "true";

            button.addEventListener("mousemove", function (event) {
                if (button.disabled) {
                    return;
                }

                const rect = button.getBoundingClientRect();
                const x = event.clientX - rect.left - rect.width / 2;
                const y = event.clientY - rect.top - rect.height / 2;

                buttonText.style.transform =
                    "translate3d(" +
                    (x / 5) +
                    "px, " +
                    (y / 5) +
                    "px, 0) scale(1)";
            });

            button.addEventListener("mouseleave", function () {
                buttonText.style.transform =
                    "translate3d(0px, 0px, 0px) scale(1)";
            });
        });
    }

    bindDrawerMovingButtons();

    triggers.forEach(function(trigger) { trigger.addEventListener("click", function (event) {
        if (
            event.button !== 0
            || event.ctrlKey
            || event.metaKey
            || event.shiftKey
            || event.altKey
        ) {
            return;
        }

        event.preventDefault();
        openCartDrawer();
    }); });

    closeButtons.forEach(function (button) {
        button.addEventListener("click", closeCartDrawer);
    });

    document.addEventListener("keydown", function (event) {
        if (
            event.key === "Escape"
            && drawer.classList.contains("is-open")
        ) {
            closeCartDrawer();
        }
    });

    drawer.addEventListener("keydown", function (event) {
        if (
            event.key !== "Tab"
            || !drawer.classList.contains("is-open")
            || !panel
        ) {
            return;
        }

        const focusable = Array.from(
            panel.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), ' +
                'select:not([disabled]), textarea:not([disabled]), ' +
                '[tabindex]:not([tabindex="-1"])'
            )
        ).filter(function (element) {
            return element.offsetParent !== null;
        });

        if (!focusable.length) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (
            !event.shiftKey
            && document.activeElement === last
        ) {
            event.preventDefault();
            first.focus();
        }
    });
})();
</script>
<?php /**PATH C:\xampp\htdocs\ArizonaOutfits\complete-project-audit\_checks/../../resources/views\partials\header-menu-links.blade.php ENDPATH**/ ?>