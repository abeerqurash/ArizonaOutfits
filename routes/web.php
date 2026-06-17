<?php

use App\Models\Post;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AbeerKhanController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\privacyPolicyController;
use App\Http\Controllers\termsAndCondtionsController;
use App\Http\Controllers\HomeFormController;
use App\Http\Controllers\BlogFormController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CheckoutController;

Route::get('/products', [ShopController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('products.show');
Route::get('/product-category/{slug}', [ShopController::class, 'category'])->name('products.category');
Route::get('/sale', [ShopController::class, 'sale'])->name('products.sale');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('favorite.toggle');
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.place');
Route::get('/order-thank-you/{order_number}', [CheckoutController::class, 'thankYou'])->name('checkout.thankyou');

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home-page');

// About
Route::get('/about', [AboutController::class, 'index'])->name('about-page');

// Abeerkhan
Route::get('/abeerkhan', [AbeerKhanController::class, 'index'])->name('abeerKhan-page');

// About
Route::get('/privacy-policy', [privacyPolicyController::class, 'index'])->name('privacy-policy-page');

// Abeerkhan
Route::get('/terms-and-conditions', [termsAndCondtionsController::class, 'index'])->name('terms-and-conditions-page');

Route::get('/thank-you', function () {
    return view('thank-you');
})->name('thank-you');

Route::post('/home-form-submit', [HomeFormController::class, 'submit'])
    ->name('home.form.submit');

Route::post('/contact-form-submit', [ContactFormController::class, 'submit'])
    ->name('contact.form.submit');

Route::post('/blog-form-submit', [BlogFormController::class, 'submit'])
    ->name('blog.form.submit');

// Services
Route::prefix('services')->group(function () {
    Route::get('/', [ServicesController::class, 'index'])->name('services-page.index');
    Route::get('/{slug}', [ServicesController::class, 'show'])->name('services-show.show');
});

// Projects
Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectsController::class, 'index'])->name('projects-page.index');
    Route::get('/{slug}', [ProjectsController::class, 'show'])->name('projects-show.show');
});

// Contact
Route::get('/contact', [ContactController::class, 'index'])->name('contact-page');

// Blog Parent Page
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs-page');

// All Categories page (no prefix)
Route::get('/category', [CategoryController::class, 'index'])->name('categories-page');

// Single category page (posts in that category)
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category-show');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);
        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('product-categories', \App\Http\Controllers\Admin\ProductCategoryController::class);
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
        Route::resource('product-tags', \App\Http\Controllers\Admin\ProductTagController::class);
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
    });


require __DIR__ . '/auth.php';
// Single Blog Article (catch-all for blog posts)
Route::get('/{slug}', [BlogController::class, 'show'])->name('blog-show');
