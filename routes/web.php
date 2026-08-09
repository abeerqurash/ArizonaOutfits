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
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\SupplierContactController;
use App\Http\Controllers\Admin\SupplierDocumentController;
use App\Http\Controllers\Admin\SupplierRatingController;
use App\Http\Controllers\Admin\SupplierPurchaseOrderDeliveryController;
use App\Http\Controllers\Admin\SupplierProductController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ReorderDashboardController;
use App\Http\Controllers\Admin\StockValuationController;
use App\Http\Controllers\Admin\InventoryReportController;
use App\Http\Controllers\Admin\InventoryAdjustmentController;
use App\Http\Controllers\Admin\InventoryAlertController;
use App\Http\Controllers\Payment\StripePaymentController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Http\Controllers\Admin\InventoryHistoryController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\PurchaseOrderDraftController;
use App\Http\Controllers\Admin\PurchaseOrderReceivingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminEmailTemplateController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AdminNavigationMenuController;
use App\Http\Controllers\CmsPageController;

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductTagController as AdminProductTagController;
use App\Http\Controllers\Admin\ProductOptionController as AdminProductOptionController;
use App\Http\Controllers\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EcommerceSettingController as AdminEcommerceSettingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;


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
| CART AND COUPON ROUTES
|--------------------------------------------------------------------------
*/

Route::get(
    '/cart',
    [CartController::class, 'index']
)->name('cart.index');

Route::post(
    '/cart/add',
    [CartController::class, 'add']
)->name('cart.add');

Route::post(
    '/cart/update',
    [CartController::class, 'update']
)->name('cart.update');

Route::post(
    '/cart/remove',
    [CartController::class, 'remove']
)->name('cart.remove');

Route::post(
    '/cart/coupon',
    [CartController::class, 'applyCoupon']
)->name('cart.coupon.apply');

Route::delete(
    '/cart/coupon',
    [CartController::class, 'removeCoupon']
)->name('cart.coupon.remove');


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
| PUBLIC PRODUCT REVIEW ROUTE
|--------------------------------------------------------------------------
|
| Guests and logged-in customers can both submit reviews.
|
*/

Route::post(
    '/product/{product}/reviews',
    [ReviewController::class, 'store']
)->name('reviews.store');



/*
|--------------------------------------------------------------------------
| FAVORITE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post(
        '/favorite/toggle',
        [FavoriteController::class, 'toggle']
    )->name('favorite.toggle');

    Route::get(
        '/favorites',
        [FavoriteController::class, 'index']
    )->name('favorites.index');
});
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
    '/checkout/shipping-quote',
    [CheckoutController::class, 'shippingQuote']
)->name('checkout.shipping-quote');

/*
|--------------------------------------------------------------------------
| Direct Bank Transfer
|--------------------------------------------------------------------------
*/

Route::post(
    '/checkout/place-order',
    [CheckoutController::class, 'placeOrder']
)->name('checkout.place');

/*
|--------------------------------------------------------------------------
| Stripe
|--------------------------------------------------------------------------
*/

Route::post(
    '/stripe/create-intent',
    [StripePaymentController::class, 'createIntent']
)->name('checkout.stripe.intent');

Route::get(
    '/stripe/return',
    [StripePaymentController::class, 'paymentReturn']
)->name('checkout.stripe.return');

Route::post(
    '/stripe/webhook',
    [StripeWebhookController::class, 'handle']
)->name('checkout.stripe.webhook');

/*
|--------------------------------------------------------------------------
| Thank You
|--------------------------------------------------------------------------
*/

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
        '/account',
        [CustomerDashboardController::class, 'index']
    )->name('customer.dashboard');

    Route::get(
        '/account/orders',
        [CustomerDashboardController::class, 'orders']
    )->name('customer.orders.index');

    Route::get(
        '/account/orders/{order}',
        [CustomerDashboardController::class, 'show']
    )->whereNumber('order')->name('customer.orders.show');

    Route::get(
        '/account/orders/{order}/invoice',
        [CustomerDashboardController::class, 'invoice']
    )->whereNumber('order')->name('customer.orders.invoice');

    Route::get(
        '/account/orders/{order}/invoice/download',
        [CustomerDashboardController::class, 'downloadInvoice']
    )->whereNumber('order')->name('customer.orders.invoice.download');

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
    return auth()->user()?->is_admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('customer.dashboard');
})
    ->middleware('auth')
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'admin',
    'admin.audit',
    'throttle:admin',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

        Route::get(
            '/dashboard',
            [AdminDashboardController::class, 'index']
        )->name('dashboard');

        Route::get(
            '/dashboard/filter',
            [AdminDashboardController::class, 'filter']
        )->name('dashboard.filter');

        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])
            ->name('analytics.index');
        Route::get('/analytics/export', [AdminAnalyticsController::class, 'export'])
            ->name('analytics.export');

        Route::post(
            '/logout',
            function (\Illuminate\Http\Request $request) {
                \Illuminate\Support\Facades\Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login');
            }
        )->name('logout');
        /*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/

        Route::get(
            '/orders',
            [AdminOrderController::class, 'index']
        )->name('orders.index');

        Route::patch(
            '/orders/bulk-update',
            [AdminOrderController::class, 'bulkUpdate']
        )->name('orders.bulk-update');

        Route::delete(
            '/orders/bulk-delete',
            [AdminOrderController::class, 'bulkDelete']
        )->name('orders.bulk-delete');

        Route::post(
            '/orders/bulk-export',
            [AdminOrderController::class, 'bulkExport']
        )->name('orders.bulk-export');

        /*
|--------------------------------------------------------------------------
| Order notes
|--------------------------------------------------------------------------
*/

        Route::post(
            '/orders/{order}/notes',
            [AdminOrderController::class, 'storeNote']
        )->name('orders.notes.store');

        Route::delete(
            '/orders/{order}/notes/{note}',
            [AdminOrderController::class, 'destroyNote']
        )->name('orders.notes.destroy');

        /*
|--------------------------------------------------------------------------
| Individual order
|--------------------------------------------------------------------------
*/
        Route::get(
            '/orders/{order}/shipping-label',
            [AdminOrderController::class, 'shippingLabel']
        )->name('orders.shipping-label');

        Route::get(
            '/orders/{order}/shipping-label/download',
            [AdminOrderController::class, 'downloadShippingLabel']
        )->name('orders.shipping-label.download');
        Route::get(
            '/orders/{order}',
            [AdminOrderController::class, 'show']
        )->name('orders.show');

        Route::get(
            '/orders/{order}/invoice',
            [AdminOrderController::class, 'invoice']
        )->name('orders.invoice');
        Route::post(
            '/orders/{order}/email-invoice',
            [AdminOrderController::class, 'emailInvoice']
        )->name('orders.email-invoice');
        Route::get(
            '/orders/{order}/invoice/download',
            [AdminOrderController::class, 'downloadInvoice']
        )->name('orders.invoice.download');

        Route::get(
            '/orders/{order}/packing-slip',
            [AdminOrderController::class, 'packingSlip']
        )->name('orders.packing-slip');

        Route::get(
            '/orders/{order}/packing-slip/download',
            [AdminOrderController::class, 'downloadPackingSlip']
        )->name('orders.packing-slip.download');

        Route::put(
            '/orders/{order}',
            [AdminOrderController::class, 'update']
        )->name('orders.update');

        Route::delete(
            '/orders/{order}',
            [AdminOrderController::class, 'destroy']
        )->name('orders.destroy');
        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/customers',
            [AdminCustomerController::class, 'index']
        )->name('customers.index');

        Route::get(
            '/customers/{customer}',
            [AdminCustomerController::class, 'show']
        )->name('customers.show');

        Route::put(
            '/customers/{customer}',
            [AdminCustomerController::class, 'update']
        )->name('customers.update');

        Route::delete(
            '/customers/{customer}',
            [AdminCustomerController::class, 'destroy']
        )->name('customers.destroy');

        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/reviews',
            [AdminReviewController::class, 'index']
        )->name('reviews.index');

        Route::put(
            '/reviews/{review}',
            [AdminReviewController::class, 'update']
        )->name('reviews.update');

        Route::delete(
            '/reviews/{review}',
            [AdminReviewController::class, 'destroy']
        )->name('reviews.destroy');

        /*
        |--------------------------------------------------------------------------
        | E-commerce settings
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/ecommerce-settings',
            [AdminEcommerceSettingController::class, 'edit']
        )->name('settings.edit');

        Route::put(
            '/ecommerce-settings',
            [AdminEcommerceSettingController::class, 'update']
        )->name('settings.update');

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
        Route::get(
            '/stock-valuation',
            [StockValuationController::class, 'index']
        )->name('stock-valuation.index');

        Route::get(
            '/stock-valuation/export/csv',
            [StockValuationController::class, 'exportCsv']
        )->name('stock-valuation.export.csv');

        Route::get(
            '/stock-valuation/export/excel',
            [
                StockValuationController::class,
                'exportExcel',
            ]
        )->name('stock-valuation.export.excel');

        Route::get(
            '/stock-valuation/export/pdf',
            [
                StockValuationController::class,
                'exportPdf',
            ]
        )->name('stock-valuation.export.pdf');
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
        )->except([
            'show',
        ]);

        Route::resource(
            'product-tags',
            AdminProductTagController::class
        );

        Route::resource(
            'coupons',
            AdminCouponController::class
        );

        Route::get(
            '/inventory-alerts',
            [InventoryAlertController::class, 'index']
        )->name('inventory-alerts.index');

        Route::patch(
            '/inventory-alerts/{inventoryAlert}/resolve',
            [InventoryAlertController::class, 'resolve']
        )->name('inventory-alerts.resolve');


        Route::get(
            '/inventory-history',
            [InventoryHistoryController::class, 'index']
        )->name('inventory-history.index');
        Route::get(
            '/products/{product}/inventory',
            [InventoryAdjustmentController::class, 'edit']
        )->name('products.inventory.edit');

        Route::post(
            '/products/{product}/inventory',
            [InventoryAdjustmentController::class, 'update']
        )->name('products.inventory.update');
        Route::get(
            '/inventory-reports',
            [InventoryReportController::class, 'index']
        )->name('inventory-reports.index');
        Route::get(
            '/inventory-reports/export',
            [InventoryReportController::class, 'export']
        )->name('inventory-reports.export');
        Route::get(
            '/reorder-dashboard',
            [
                ReorderDashboardController::class,
                'index',
            ]
        )->name('reorder-dashboard.index');
        Route::get(
            '/reorder-dashboard/export/csv',
            [
                ReorderDashboardController::class,
                'exportCsv',
            ]
        )->name('reorder-dashboard.export.csv');
        Route::get(
            '/reorder-dashboard/export/excel',
            [
                ReorderDashboardController::class,
                'exportExcel',
            ]
        )->name('reorder-dashboard.export.excel');
        Route::post(
            '/purchase-orders/create-from-reorder',
            [
                PurchaseOrderController::class,
                'createFromReorder',
            ]
        )->name('purchase-orders.create-from-reorder');
        Route::post(
            '/purchase-orders',
            [
                PurchaseOrderController::class,
                'store',
            ]
        )->name('purchase-orders.store');
        Route::get(
            '/purchase-orders',
            [
                PurchaseOrderController::class,
                'index',
            ]
        )->name('purchase-orders.index');

        Route::patch(
            '/purchase-orders/{purchaseOrder}/mark-ordered',
            [
                PurchaseOrderController::class,
                'markOrdered',
            ]
        )->name('purchase-orders.mark-ordered');

        Route::patch(
            '/purchase-orders/{purchaseOrder}/cancel',
            [
                PurchaseOrderController::class,
                'cancel',
            ]
        )->name('purchase-orders.cancel');
        Route::post(
            '/purchase-orders/{purchaseOrder}/receive',
            [
                PurchaseOrderReceivingController::class,
                'store',
            ]
        )->name('purchase-orders.receive');

        Route::get(
            '/purchase-orders/{purchaseOrder}/receiving',
            [
                PurchaseOrderReceivingController::class,
                'index',
            ]
        )->name('purchase-orders.receiving.index');

        Route::post(
            '/purchase-orders/{purchaseOrder}/receipts/{receipt}/items/{receiptItem}/correct',
            [
                PurchaseOrderReceivingController::class,
                'correct',
            ]
        )->name('purchase-orders.receipts.correct');

        Route::post(
            '/purchase-orders/{purchaseOrder}/supplier-returns',
            [
                PurchaseOrderReceivingController::class,
                'storeReturn',
            ]
        )->name('purchase-orders.supplier-returns.store');

        Route::patch(
            '/purchase-orders/{purchaseOrder}/supplier-returns/{supplierReturn}/complete',
            [
                PurchaseOrderReceivingController::class,
                'completeReturn',
            ]
        )->name('purchase-orders.supplier-returns.complete');

        Route::patch(
            '/purchase-orders/{purchaseOrder}/supplier-returns/{supplierReturn}/cancel',
            [
                PurchaseOrderReceivingController::class,
                'cancelReturn',
            ]
        )->name('purchase-orders.supplier-returns.cancel');
        Route::get(
            '/purchase-orders/{purchaseOrder}/pdf',
            [
                PurchaseOrderController::class,
                'downloadPdf',
            ]
        )->name('purchase-orders.pdf');
        Route::get(
            '/purchase-orders/{purchaseOrder}/excel',
            [
                PurchaseOrderController::class,
                'downloadExcel',
            ]
        )->name('purchase-orders.excel');
        Route::get(
            '/purchase-orders/create',
            [
                PurchaseOrderController::class,
                'create',
            ]
        )->name('purchase-orders.create');

        Route::get(
            '/purchase-orders/{purchaseOrder}/edit',
            [
                PurchaseOrderDraftController::class,
                'edit',
            ]
        )->name('purchase-orders.draft.edit');

        Route::put(
            '/purchase-orders/{purchaseOrder}',
            [
                PurchaseOrderDraftController::class,
                'update',
            ]
        )->name('purchase-orders.draft.update');

        Route::get(
            '/purchase-orders/{purchaseOrder}',
            [
                PurchaseOrderController::class,
                'show',
            ]
        )->name('purchase-orders.show');

        Route::get(
            '/suppliers',
            [
                SupplierController::class,
                'index',
            ]
        )->name('suppliers.index');

        Route::get(
            '/suppliers/create',
            [
                SupplierController::class,
                'create',
            ]
        )->name('suppliers.create');

        Route::post(
            '/suppliers',
            [
                SupplierController::class,
                'store',
            ]
        )->name('suppliers.store');

        Route::get(
            '/suppliers/{supplier}/edit',
            [
                SupplierController::class,
                'edit',
            ]
        )->name('suppliers.edit');

        Route::put(
            '/suppliers/{supplier}',
            [
                SupplierController::class,
                'update',
            ]
        )->name('suppliers.update');

        Route::delete(
            '/suppliers/{supplier}',
            [
                SupplierController::class,
                'destroy',
            ]
        )->name('suppliers.destroy');

        Route::get(
            '/suppliers/{supplier}',
            [
                SupplierController::class,
                'show',
            ]
        )->name('suppliers.show');

        Route::get(
            '/suppliers/{supplier}/products',
            [
                SupplierProductController::class,
                'index',
            ]
        )->name('suppliers.products.index');

        Route::post(
            '/suppliers/{supplier}/products',
            [
                SupplierProductController::class,
                'store',
            ]
        )->name('suppliers.products.store');

        Route::put(
            '/suppliers/{supplier}/products/{supplierProduct}',
            [
                SupplierProductController::class,
                'update',
            ]
        )->name('suppliers.products.update');

        Route::delete(
            '/suppliers/{supplier}/products/{supplierProduct}',
            [
                SupplierProductController::class,
                'destroy',
            ]
        )->name('suppliers.products.destroy');

        Route::get(
            '/suppliers/{supplier}/product-pricing',
            [
                SupplierProductController::class,
                'pricing',
            ]
        )->name('suppliers.products.pricing');

        Route::post(
            '/suppliers/{supplier}/contacts',
            [
                SupplierContactController::class,
                'store',
            ]
        )->name('suppliers.contacts.store');

        Route::put(
            '/suppliers/{supplier}/contacts/{contact}',
            [
                SupplierContactController::class,
                'update',
            ]
        )->name('suppliers.contacts.update');

        Route::delete(
            '/suppliers/{supplier}/contacts/{contact}',
            [
                SupplierContactController::class,
                'destroy',
            ]
        )->name('suppliers.contacts.destroy');

        Route::post(
            '/suppliers/{supplier}/documents',
            [
                SupplierDocumentController::class,
                'store',
            ]
        )->name('suppliers.documents.store');

        Route::get(
            '/suppliers/{supplier}/documents/{document}/download',
            [
                SupplierDocumentController::class,
                'download',
            ]
        )->name('suppliers.documents.download');

        Route::delete(
            '/suppliers/{supplier}/documents/{document}',
            [
                SupplierDocumentController::class,
                'destroy',
            ]
        )->name('suppliers.documents.destroy');

        Route::post(
            '/suppliers/{supplier}/ratings',
            [
                SupplierRatingController::class,
                'store',
            ]
        )->name('suppliers.ratings.store');

        Route::put(
            '/suppliers/{supplier}/ratings/{rating}',
            [
                SupplierRatingController::class,
                'update',
            ]
        )->name('suppliers.ratings.update');

        Route::delete(
            '/suppliers/{supplier}/ratings/{rating}',
            [
                SupplierRatingController::class,
                'destroy',
            ]
        )->name('suppliers.ratings.destroy');

        Route::post(
            '/suppliers/{supplier}/purchase-orders/{purchaseOrder}/email',
            [
                SupplierPurchaseOrderDeliveryController::class,
                'store',
            ]
        )->name('suppliers.purchase-orders.email');

        Route::get(
            '/admin-users',
            [AdminUserController::class, 'index']
        )->name('admin-users.index');

        Route::post(
            '/admin-users',
            [AdminUserController::class, 'store']
        )->name('admin-users.store');

        Route::get(
            '/admin-users/{adminUser}/edit',
            [AdminUserController::class, 'edit']
        )->name('admin-users.edit');

        Route::put(
            '/admin-users/{adminUser}',
            [AdminUserController::class, 'update']
        )->name('admin-users.update');

        Route::delete(
            '/admin-users/{adminUser}',
            [AdminUserController::class, 'destroy']
        )->name('admin-users.destroy');

        Route::get(
            '/admin-roles',
            [AdminRoleController::class, 'index']
        )->name('admin-roles.index');

        Route::post(
            '/admin-roles',
            [AdminRoleController::class, 'store']
        )->name('admin-roles.store');

        Route::put(
            '/admin-roles/{adminRole}',
            [AdminRoleController::class, 'update']
        )->name('admin-roles.update');

        Route::delete(
            '/admin-roles/{adminRole}',
            [AdminRoleController::class, 'destroy']
        )->name('admin-roles.destroy');

        Route::get(
            '/audit-logs',
            [AdminAuditLogController::class, 'index']
        )->name('audit-logs.index');

        Route::get(
            '/audit-logs/export',
            [AdminAuditLogController::class, 'export']
        )->name('audit-logs.export');

        Route::get(
            '/notifications',
            [AdminNotificationController::class, 'index']
        )->name('notifications.index');

        Route::post(
            '/notifications/send',
            [AdminNotificationController::class, 'send']
        )->name('notifications.send');

        Route::patch(
            '/notifications/read-all',
            [AdminNotificationController::class, 'markAllAsRead']
        )->name('notifications.read-all');

        Route::delete(
            '/notifications/read',
            [AdminNotificationController::class, 'clearRead']
        )->name('notifications.clear-read');

        Route::patch(
            '/notifications/{notification}/read',
            [AdminNotificationController::class, 'markAsRead']
        )->name('notifications.read');

        Route::delete(
            '/notifications/{notification}',
            [AdminNotificationController::class, 'destroy']
        )->name('notifications.destroy');

        Route::get('/backups', [AdminBackupController::class, 'index'])
            ->name('backups.index');
        Route::post('/backups', [AdminBackupController::class, 'store'])
            ->name('backups.store');
        Route::post('/backups/cleanup', [AdminBackupController::class, 'cleanup'])
            ->name('backups.cleanup');
        Route::get('/backups/{backup}/download', [AdminBackupController::class, 'download'])
            ->name('backups.download');
        Route::delete('/backups/{backup}', [AdminBackupController::class, 'destroy'])
            ->name('backups.destroy');

        Route::get('/email-templates', [AdminEmailTemplateController::class, 'index'])
            ->name('email-templates.index');
        Route::get('/email-templates/{emailTemplate}/edit', [AdminEmailTemplateController::class, 'edit'])
            ->name('email-templates.edit');
        Route::put('/email-templates/{emailTemplate}', [AdminEmailTemplateController::class, 'update'])
            ->name('email-templates.update');
        Route::get('/email-templates/{emailTemplate}/preview', [AdminEmailTemplateController::class, 'preview'])
            ->name('email-templates.preview');
        Route::post('/email-templates/{emailTemplate}/test', [AdminEmailTemplateController::class, 'sendTest'])
            ->name('email-templates.test');

        Route::get('/pages/custom', function () {
            return redirect()->route('admin.pages.create');
        })->name('pages.custom');

        Route::resource('pages', AdminPageController::class)->except('show');

        Route::get('/navigation-menus',[AdminNavigationMenuController::class,'index'])->name('navigation-menus.index');
        Route::post('/navigation-menus/{menu}/items',[AdminNavigationMenuController::class,'storeItem'])->name('navigation-menus.items.store');
        Route::put('/navigation-menu-items/{item}',[AdminNavigationMenuController::class,'updateItem'])->name('navigation-menus.items.update');
        Route::delete('/navigation-menu-items/{item}',[AdminNavigationMenuController::class,'destroyItem'])->name('navigation-menus.items.destroy');
        Route::put('/navigation-menus/{menu}/reorder',[AdminNavigationMenuController::class,'reorder'])->name('navigation-menus.reorder');
    });

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

Route::get('/page/{slug}', [CmsPageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('pages.show');

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
