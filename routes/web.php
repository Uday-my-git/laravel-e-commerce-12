<?php

// Backend End Side Controller 
use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\DiscountCodeController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\PdfController;
use App\Http\Controllers\admin\PagesController;
use App\Http\Controllers\admin\UserController;

use App\Http\Controllers\admin\ProductSubCategoryConrtroller;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\TempImagecontroller;
use App\Http\Controllers\admin\AiformController;

// Frontend Side Controller 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\PaymentController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Route::get('/', function () {
//    return view('welcome');
// });

// Route::get('/test', function () {
//    orderEmail(49);
// });

Route::get('/stripe', function () {
    return view('front-end/stripe');
});

// Stripe payment
Route::get('/success-stripe', [PaymentController::class, 'success'])->name('front-end.success');
Route::get('/stripe/cancel', [PaymentController::class, 'cancel'])->name('front-end.cancel');
Route::post('/stripe/webhook', [PaymentController::class, 'webhook']);
Route::post('/authorize-net/webhook', [PaymentController::class, 'authorizeWebhook'])->name('front-end.authorizeWebhook');

Route::post('/process-authorizeCharge', [PaymentController::class, 'authorizeCharge'])->name('front-end.authorizeCharge');

/************************************* Front End Route **********************************/ 
Route::get('/', [FrontEndController::class, 'index'])->name('front-end.home');
Route::post('/add-to-wishlist', [FrontEndController::class, 'addToWishlist'])->name('addToWishlist');
Route::get('/add-pages/{slug}', [FrontEndController::class, 'pages'])->name('front-end.pages');
Route::post('/add-pages/send-contact-form', [FrontEndController::class, 'sendContactForm'])->name('front-end.sendContactForm');

/************************************* ShopController Route **********************************/ 
Route::get('/shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class, 'index'])->name('front-end.shop');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('front-end.product');
Route::post('/user-rating/{productId}', [ShopController::class, 'userRating'])->name('front-end.userRating');

/************************************* AuthController Route **********************************/ 
Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('front-end.forgotPassword');
Route::post('/forgot-password-process', [AuthController::class, 'forgotPasswordProcess'])->name('front-end.forgotPasswordProcess');
Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('front-end.resetPassword');
Route::post('/process-reset-password', [AuthController::class, 'processResetPassword'])->name('front-end.processResetPassword');

/************************************* SocialMediaController Route **********************************/ 
Route::get('/auth/google', [SocialMediaController::class, 'redirectToGoogle'])->name('front-end.auth.google');
Route::get('/auth/google/callback', [SocialMediaController::class, 'handleGoogleCallback']);
Route::get('/auth/facebook', [SocialMediaController::class, 'redirectToFacebook'])->name('front-end.auth.facebook');
Route::get('/auth/facebook/callback', [SocialMediaController::class, 'handleFacebookCallback']);
Route::get('/auth/github', [SocialMediaController::class, 'redirectToGithub'])->name('front-end.auth.github');
Route::get('/auth/github/callback', [SocialMediaController::class, 'handleGithubCallback']);

Route::get('/data-deletion', function () {
    return view('front-end.data-deletion');
});


/********************************* Add To Cart Controller *********************************/
Route::get('/cart', [CartController::class, 'cart'])->name('front-end.cart');
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->name('front-end.addToCart');
Route::post('/update-to-cart', [CartController::class, 'updateCart'])->name('front-end.updateCart');
Route::delete('/delete-to-cart', [CartController::class, 'deleteCartItems'])->name('front-end.deleteCartItems');
Route::get('/checkout', [CartController::class, 'checkout'])->name('front-end.checkout');
Route::get('/check-email-validation', [CartController::class, 'checkEmailValidation'])->name('front-end.emailValidation');
Route::post('/process-checkout', [CartController::class, 'processCheckout'])->name('front-end.processCheckout');
Route::get('/get-order-summery', [CartController::class, 'getOrderSummery'])->name('front-end.getOrderSummery');
Route::post('/apply-coupon-code', [CartController::class, 'applyCouponCode'])->name('front-end.applyCouponCode');
Route::post('/remove-coupon-code', [CartController::class, 'removeCouponCode'])->name('front-end.removeCouponCode');

// Route::get('/create-createPaymentIntent', [CartController::class, 'createStripeSession'])->name('front-end.createPaymentIntent');


// Route::get('/create-stripe-session', [CartController::class, 'createStripeSession'])->name('front-end.createStripeSession');
// Route::get('/success-stripe', [CartController::class, 'success'])->name('front-end.success');
// Route::get('/stripe/cancel', [CartController::class, 'cancel'])->name('front-end.cancel');
// Route::post('/stripe/webhook', [CartController::class, 'webhook']);

Route::get('/thanku-page', [CartController::class, 'thankuPage'])->name('front-end.thankuPage');


/******************************* Front-end User Authentication *******************************/ 
Route::group(['prefix' => 'account'], function () {
    // Guest routes
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/user-register', [AuthController::class, 'userRegister'])->name('account.userRegister');
        Route::get('/user-login', [AuthController::class, 'login'])->name('account.login');
        Route::post('/register-process', [AuthController::class, 'registerProcess'])->name('account.registerProcess');
        Route::post('/login-authenticate', [AuthController::class, 'authenticate'])->name('account.authenticate');
    });

    // Authenticated routes
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/profile', [AuthController::class, 'profile'])->name('account.profile');
        Route::post('/profile/update-profile', [AuthController::class, 'updateProfile'])->name('account.updateProfile');
        Route::post('/profile/update-address', [AuthController::class, 'updateAddress'])->name('account.updateAddress');
        Route::get('/my-orders', [AuthController::class, 'orderGet'])->name('front-end.orderGet');
        Route::get('/get-my-orders/{orderId}', [AuthController::class, 'get_orderDetail'])->name('front-end.get_orderDetail');
        Route::get('/wishlist-page', [AuthController::class, 'wishlist'])->name('front-end.wishlist');
        Route::delete('/remove-wishlist-product', [AuthController::class, 'deleteWishlistProduct'])->name('front-end.deleteWishlistProduct');
        Route::get('/change-password', [AuthController::class, 'changePassword'])->name('front-end.changePassword');
        Route::post('/process-change-password', [AuthController::class, 'processChangePassword'])->name('front-end.processChangePassword');

        Route::get('/user-logout', [AuthController::class, 'logout'])->name('account.logout');
    });
});


/************************************* Admin Middleware For Grouping Route **********************************/ 
Route::group(['prefix' => 'admin'], function () {

    Route::group(['middleware' => 'admin.guest'], function () {
       Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
       Route::get('/new-register', [AdminLoginController::class, 'newRegister'])->name('admin.newRegister');
       // Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');

        Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])
            ->middleware(['admin.guest', 'throttle:admin-login'])
            ->name('admin.authenticate')
        ;

    });

    Route::group(['middleware' => 'admin.auth'], function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');

        /*********************************** Category Route ***********************************/
        Route::get('/categories/listing', [CategoryController::class, 'listing'])->name('categories.listing');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories/insert', [CategoryController::class, 'insert'])->name('categories.insert');
        Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/delete/{id}', [CategoryController::class, 'delete'])->name('categories.delete');

        /*********************************** Sub-category route ***********************************/
        Route::get('/sub-category/listing', [SubCategoryController::class, 'listing'])->name('sub_category.listing');
        Route::get('/sub-category/create', [SubCategoryController::class, 'create'])->name('sub_category.create');
        Route::post('/sub-category/store', [SubCategoryController::class, 'store'])->name('sub_category.store');
        Route::get('/sub-category/edit/{id}', [SubCategoryController::class, 'edit'])->name('sub_category.edit');
        Route::put('/sub-category/update/{id}', [SubCategoryController::class, 'update'])->name('sub_category.update');
        Route::delete('/sub-category/delete/{id}', [SubCategoryController::class, 'delete'])->name('sub_category.delete');

        /*********************************** Brand route ***********************************/
        Route::get('/brand/listing', [BrandController::class, 'listing'])->name('brand.listing');
        Route::get('/brand/create', [BrandController::class, 'create'])->name('brand.create');
        Route::post('/brand/store', [BrandController::class, 'store'])->name('brand.store');
        Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
        Route::put('/brand/update/{id}', [BrandController::class, 'update'])->name('brand.update');
        Route::delete('/brand/delete/{id}', [BrandController::class, 'delete'])->name('brand.delete');
        Route::delete('/brand/delete-all', [BrandController::class, 'deleteAllCheckbox'])->name('brand.deleteAll');

        /*********************************** Product route ***********************************/
        Route::get('/product/listing', [ProductController::class, 'listing'])->name('product.listing');
        Route::get('/product/determinePerPage', [ProductController::class, 'determinePerPage'])->name('product.determinePerPage');
        Route::get('/product/calculateVisibleLinks', [ProductController::class, 'calculateVisibleLinks'])->name('product.calculateVisibleLinks');
        Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
        Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
        Route::delete('/product/delete/{id}', [ProductController::class, 'deleteProducts'])->name('product.delete');       
        Route::get('/product/get-related-product', [ProductController::class, 'getRelatedProduct'])->name('product.getRelatedProduct');
        Route::get('/product/product-ratings', [ProductController::class, 'productRatings'])->name('product.productRatings');
        Route::get('/product/product-ratings-status', [ProductController::class, 'changeStatustRating'])->name('product.changeStatustRating');
        Route::delete('/product/product-ratings-status/{id}', [ProductController::class, 'deleteUserRating'])->name('product.deleteUserRating');
 
        /*********************************** ShippingController route ***********************************/
        Route::get('/shipping-listing', [ShippingController::class, 'listing'])->name('shipping.listing');
        Route::post('/shipping/store', [ShippingController::class, 'store'])->name('shipping.store');
        Route::get('/shipping/edit/{id}', [ShippingController::class, 'edit'])->name('shipping.edit');
        Route::put('/shipping/update/{id}', [ShippingController::class, 'update'])->name('shipping.update');
        Route::delete('/shipping/delete/{id}', [ShippingController::class, 'delete'])->name('shipping.delete');

        /*********************************** DiscountCodeController route ***********************************/
        Route::get('/coupon/listing', [DiscountCodeController::class, 'listing'])->name('coupon.listing');
        Route::get('/coupon/create', [DiscountCodeController::class, 'create'])->name('coupon.create');
        Route::post('/coupon/store', [DiscountCodeController::class, 'store'])->name('coupon.store');
        Route::get('/coupon/edit/{id}', [DiscountCodeController::class, 'edit'])->name('coupon.edit');
        Route::put('/coupon/update/{id}', [DiscountCodeController::class, 'update'])->name('coupon.update');
        Route::delete('/coupon/destroy/{id}', [DiscountCodeController::class, 'destroy'])->name('coupon.destroy');
        Route::delete('/couponDeleteAll', [DiscountCodeController::class, 'deleteAll'])->name('coupon.deleteAll');

        /*********************************** OrderController route ***********************************/
        Route::get('/orders/listing', [OrderController::class, 'listing'])->name('orders.listing');
        Route::get('/orders/order-details/{orderId}', [OrderController::class, 'orderDetail'])->name('orders.orderDetail');
        Route::post('/orders/change-order-status/{orderId}', [OrderController::class, 'changeOrderStatus'])->name('changeOrderStatus');
        Route::post('/orders/send-email/{orderId}', [OrderController::class, 'sendEmailInvoice'])->name('sendEmailInvoice');
        
        /*********************************** UserController route ***********************************/
        Route::get('/user/listing', [UserController::class, 'listing'])->name('users.listing');
        Route::get('/user/userData/{id}', [UserController::class, 'viewUserData'])->name('users.data');
        Route::get('/user/changeStatus', [UserController::class, 'changeStatus'])->name('users.changeStatus');
        Route::get('/user/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/user/store', [UserController::class, 'store'])->name('users.store');
        Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/user/update/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/user/destroy/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        /*********************************** PagesController route ***********************************/
        Route::get('/pages/listing', [PagesController::class, 'listing'])->name('pages.listing');
        Route::get('/pages/create', [PagesController::class, 'create'])->name('pages.create');
        Route::post('/pages/store', [PagesController::class, 'store'])->name('pages.store');
        Route::get('/pages/edit/{id}', [PagesController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/update/{id}', [PagesController::class, 'update'])->name('pages.update');
        Route::delete('/pages/delete/{id}', [PagesController::class, 'delete'])->name('pages.delete');

        /*********************************** SettingController route ***********************************/
        Route::get('/chage-password-form', [SettingController::class, 'chagePasswordForm'])->name('admin.chagePasswordForm');
        Route::post('/process/chage-password-form', [SettingController::class, 'chagePasswordFormProcess'])->name('admin.chagePasswordFormProcess');

        /*********************************** PdfController route ***********************************/
        Route::get('/pdf/{orderId}/download-pdf', [PdfController::class, 'downloadPDF'])->name('pdf.downloadPDF');
        Route::get('/listing-pdf/{orderId}', [PdfController::class, 'listingPDF'])->name('pdf.listingPDF');

        /*********************************** Product sub category route ***********************************/
        Route::get('/prodcut-sub-category', [ProductSubCategoryConrtroller::class, 'subCategory'])->name('prodcutSubCategory');
        
        /*********************************** ProductImageController image update route ***********************************/
        Route::post('/prodcut-image/update', [ProductImageController::class, 'update'])->name('prodcut-image.update');
        Route::delete('/delete-image/destroy', [ProductImageController::class, 'deleteProdcutImg'])->name('prodcut-image.delete');

        /*********************************** Generate thumb images *******************************/
        Route::post('/upload-temp-images', [TempImagecontroller::class, 'create'])->name('temp-images-create');

        /************************************ Genereate slug value ************************************/
        Route::get('/getSlug', function (Request $request) {
            $slug = '';

            if (!empty($request->title)) {
                $slug  = Str::slug($request->title);
            }
            return response()->json(['status' => true, 'slug' => $slug]);

        })->name('getSlug');
        
    });
});
