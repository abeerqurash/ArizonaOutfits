<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AbeerKhanController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\TermsAndCondtionsController;
use App\Http\Controllers\HomeFormController;
use App\Http\Controllers\BlogFormController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CheckoutController;

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductTagController as AdminProductTagController;
use App\Http\Controllers\Admin\ProductOptionController as AdminProductOptionController;
use App\Http\Controllers\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;

/*
|--------------------------------------------------------------------------
| PRODUCT AND SHOP ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/products',
    [ShopController::class, 'index']
)->name('products.index');

/*
|--------------------------------------------------------------------------
| Product quick-view route
|--------------------------------------------------------------------------
|
| This route must remain above the /product/{slug} route.
|
*/

Route::get(
    '/product/{product}/quick-view',
    [ShopController::class, 'quickView']
)->name('products.quick-view');

Route::get(
    '/product-category/{slug}',
    [ShopController::class, 'category']
)->name('products.category');

Route::get(
    '/sale',
    [ShopController::class, 'sale']
)->name('products.sale');

/*
|--------------------------------------------------------------------------
| Single product route
|--------------------------------------------------------------------------
|
| Keep this after the quick-view route so Laravel does not interpret
| "quick-view" as part of the product slug.
|
*/

Route::get(
    '/product/{slug}',
    [ShopController::class, 'show']
)->name('products.show');

/*
|--------------------------------------------------------------------------
| CART ROUTES
|--------------------------------------------------------------------------
*/

Route::post(
    '/cart/add',
    [CartController::class, 'add']
)->name('cart.add');

Route::get(
    '/cart',
    [CartController::class, 'index']
)->name('cart.index');

Route::post(
    '/cart/update',
    [CartController::class, 'update']
)->name('cart.update');

Route::post(
    '/cart/remove',
    [CartController::class, 'remove']
)->name('cart.remove');

/*
|--------------------------------------------------------------------------
| FAVORITE ROUTES
|--------------------------------------------------------------------------
*/

Route::post(
    '/favorite/toggle',
    [FavoriteController::class, 'toggle']
)->name('favorite.toggle');

Route::get(
    '/favorites',
    [FavoriteController::class, 'index']
)->name('favorites.index');

/*
|--------------------------------------------------------------------------
| CHECKOUT ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/checkout',
    [CheckoutController::class, 'index']
)->name('checkout.index');

Route::post(
    '/checkout/place-order',
    [CheckoutController::class, 'placeOrder']
)->name('checkout.place');

Route::get(
    '/order-thank-you/{order_number}',
    [CheckoutController::class, 'thankYou']
)->name('checkout.thankyou');

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Home
Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home-page');

// About
Route::get(
    '/about',
    [AboutController::class, 'index']
)->name('about-page');

// Abeer Khan
Route::get(
    '/abeerkhan',
    [AbeerKhanController::class, 'index']
)->name('abeerKhan-page');

// Privacy policy
Route::get(
    '/privacy-policy',
    [PrivacyPolicyController::class, 'index']
)->name('privacy-policy-page');

// Terms and conditions
Route::get(
    '/terms-and-conditions',
    [TermsAndCondtionsController::class, 'index']
)->name('terms-and-conditions-page');

// Thank-you page
Route::get('/thank-you', function () {
    return view('thank-you');
})->name('thank-you');

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION ROUTES
|--------------------------------------------------------------------------
*/

Route::post(
    '/home-form-submit',
    [HomeFormController::class, 'submit']
)->name('home.form.submit');

Route::post(
    '/contact-form-submit',
    [ContactFormController::class, 'submit']
)->name('contact.form.submit');

Route::post(
    '/blog-form-submit',
    [BlogFormController::class, 'submit']
)->name('blog.form.submit');

/*
|--------------------------------------------------------------------------
| SERVICES ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('services')->group(function () {
    Route::get(
        '/',
        [ServicesController::class, 'index']
    )->name('services-page.index');

    Route::get(
        '/{slug}',
        [ServicesController::class, 'show']
    )->name('services-show.show');
});

/*
|--------------------------------------------------------------------------
| PROJECT ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('projects')->group(function () {
    Route::get(
        '/',
        [ProjectsController::class, 'index']
    )->name('projects-page.index');

    Route::get(
        '/{slug}',
        [ProjectsController::class, 'show']
    )->name('projects-show.show');
});

/*
|--------------------------------------------------------------------------
| CONTACT ROUTE
|--------------------------------------------------------------------------
*/

Route::get(
    '/contact',
    [ContactController::class, 'index']
)->name('contact-page');

/*
|--------------------------------------------------------------------------
| BLOG ROUTES
|--------------------------------------------------------------------------
*/

// Blog parent page
Route::get(
    '/blogs',
    [BlogController::class, 'index']
)->name('blogs-page');

/*
|--------------------------------------------------------------------------
| CATEGORY ROUTES
|--------------------------------------------------------------------------
*/

// All categories
Route::get(
    '/category',
    [CategoryController::class, 'index']
)->name('categories-page');

// Single category
Route::get(
    '/category/{slug}',
    [CategoryController::class, 'show']
)->name('category-show');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECT
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Product options
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/product-options',
            [AdminProductOptionController::class, 'store']
        )->name('product-options.store');

        Route::post(
            '/product-options/{productOption}/values',
            [
                AdminProductOptionController::class,
                'storeValue',
            ]
        )->name('product-options.values.store');

        /*
        |--------------------------------------------------------------------------
        | Admin resources
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'posts',
            AdminPostController::class
        );

        Route::resource(
            'categories',
            AdminCategoryController::class
        );

        Route::resource(
            'product-categories',
            AdminProductCategoryController::class
        );

        Route::resource(
            'products',
            AdminProductController::class
        );

        Route::resource(
            'product-tags',
            AdminProductTagController::class
        );

        Route::resource(
            'coupons',
            AdminCouponController::class
        );
    });

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| SINGLE BLOG ARTICLE CATCH-ALL ROUTE
|--------------------------------------------------------------------------
|
| This route must always remain at the very bottom because it matches
| almost every single-segment URL.
|
*/

Route::get(
    '/{slug}',
    [BlogController::class, 'show']
)->name('blog-show');