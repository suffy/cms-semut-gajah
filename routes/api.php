<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::GET('alamat', 'HomeController@findAlamat');

Route::post('/save-token', 'FCMController@index');

// get wa number
Route::group(['prefix' => 'wa'], function () {
    Route::get('/login/number', 'Api\WaNumberController@login');
    Route::get('/number', 'Api\WaNumberController@home');
});

Route::group(['prefix' => 'pwa'], function () {
    Route::get('/options', 'Api\Pwa\OptionController@get');
    Route::get('/testimonials', 'Api\Pwa\TestimonialController@get');
    Route::post('/testimonials/store', 'Api\Pwa\TestimonialController@store');

    Route::get('/blogs', 'Api\Pwa\BlogController@get');

    Route::get('/landing-page', 'Api\LandingPageController@index');
});


// app
// auth
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function () {
    Route::post('/login', 'Api\UserController@authenticate');
    Route::post('/register', 'Api\UserController@register');
    Route::post('/logout', 'Api\UserController@logout');
    Route::post('/forgot-password', 'Api\UserController@forgotPassword');
});

Route::group([
    'middleware' => ['api', 'extends.session'],

], function () {
    Route::get('/check-server', 'Api\UserController@checkServer');
    Route::get('/mapping-site', 'Api\UserController@sites');
    Route::get('/city', 'Api\UserController@city');

    Route::post('/version', 'Api\AppVersionController@index');
    Route::post('/version/restart', 'Api\AppVersionController@restart');

    // Get Profile User
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', 'Api\UserController@getAuthenticatedUser');
        Route::post('/{id}', 'Api\UserController@updateAuthenticatedUser');
        Route::get('/{id}/detail', 'Api\UserController@getUserById');
    });

    // Get Address User
    Route::group(['prefix' => 'address'], function () {
        Route::get('/', 'Api\AddressController@get');
        Route::post('/', 'Api\AddressController@store');                                    // Belum Kepakai
        Route::post('/{id}', 'Api\AddressController@update');                               // Belum Kepakai
    });
    Route::put('/default-address/{id}', 'Api\AddressController@updateDefaultAddress');  // Belum Kepakai

    // Credits Resources
    Route::get('/credits', 'Api\CreditController@index');
    Route::get('/credits/history/{id}', 'Api\CreditController@history');

    // Point Resources
    Route::get('/point/history', 'Api\PointHistoriesController@index');

    // Subscribe Resources
    Route::group(['prefix' => 'subscribes'], function () {
        Route::get('/', 'Api\SubscribeController@get');
        Route::get('/detail/{id}', 'Api\SubscribeController@detail');
        Route::get('/notifications', 'Api\SubscribeController@notification');
        Route::put('/notifications/{id}', 'Api\SubscribeController@notificationUpdate');
    });
    Route::post('/subscribe', 'Api\SubscribeController@store');
    Route::post('/subscribe/{id}', 'Api\SubscribeController@update');
    Route::delete('/subscribe/{id}', 'Api\SubscribeController@destroy');


    // product category
    Route::get('/product-categories', 'Api\ProductCategoryController@get');

    // Product Resources
    Route::group(['prefix' => 'products'], function () {
        Route::get('/', 'Api\ProductController@get');
        Route::get('/{id}', 'Api\ProductController@detail');
        Route::get('/ratings/{id}', 'Api\ProductController@rating');
        Route::get('/varian/{id}', 'Api\ProductController@varian');
    });
    Route::get('/recomen/products', 'Api\ProductController@recomen');
    Route::get('/recent/products', 'Api\ProductController@getRecent');
    Route::post('/recent/products', 'Api\ProductController@storeRecent');
    Route::get('/redeem/products', 'Api\ProductController@redeem');            // Belum Kepakai

    // banner
    Route::get('/banners', 'Api\BannerController@get');                             // Belum Kepakai

    // voucher
    Route::get('/vouchers', 'Api\VoucherController@get');                           // Belum Kepakai

    // Order Resources
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', 'Api\OrderController@get');
        Route::get('/detail/{id}', 'Api\OrderController@detail');
        Route::get('/status', 'Api\OrderController@checkStatus');
        Route::post('/', 'Api\OrderController@store');
        Route::put('/', 'Api\OrderController@update');                              // Belum Kepakai
        Route::delete('/', 'Api\OrderController@destroy');                          // Belum Kepakai
        Route::post('/cancel/{id}', 'Api\OrderController@cancel');
        Route::post('/complete/{id}', 'Api\OrderController@complete');
        Route::post('/count', 'Api\CheckPromoController@check');
        Route::post('/redeem', 'Api\RedeemPointController@store');                  // Belum Kepakai
        Route::get('/notifications', 'Api\OrderController@notification');
        Route::put('/notifications/{id}', 'Api\OrderController@seenNotification');
        Route::post('/payments/upload/{id}', 'Api\OrderController@upload');         // Belum Kepakai
    });

    // Cek Minimum Transaksi
    Route::get('/minimum-transaction/{site_code}', 'Api\OrderController@minTransaction');

    // feedback
    Route::post('/store/feedback', 'Api\FeedBackController@store');

    // wishlist
    Route::get('/wishlists', 'Api\WishlistController@get');
    Route::post('/wishlist', 'Api\WishlistController@store');
    Route::delete('/wishlist/{id}', 'Api\WishlistController@destroy');

    // qrcode
    Route::get('/qrcode', 'Api\UserController@generateQrcode');                     // Belum Kepakai

    // location
    Route::get('/location', 'Api\LocationController@get');                          // Belum Kepakai

    // chat
    Route::get('/chats', 'Api\ChatController@getChat');
    Route::get('/message/{chatId}', 'Api\ChatController@get');
    Route::get('/message', 'Api\ChatController@unread');
    Route::post('/message', 'Api\ChatController@post');
    Route::post('/message/broadcast', 'Api\ChatController@broadcastMessage');       // Belum Kepakai

    // chat notification
    Route::get('/chats/notifications', 'Api\ChatController@notification');
    Route::put('/chats/notifications/{chatId}', 'Api\ChatController@seenNotification');

    // shopping cart
    Route::get('/shopping-cart', 'Api\ShoppingCartController@get');
    Route::post('/shopping-cart', 'Api\ShoppingCartController@store');
    Route::post('/shopping-cart/validate', 'Api\ShoppingCartController@handlingValidate');
    Route::post('/shopping-cart/{id}', 'Api\ShoppingCartController@update');
    Route::delete('/shopping-cart/{id}', 'Api\ShoppingCartController@destroy');

    // transaction
    Route::get('/transactions', 'Api\TransactionController@get');
    Route::get('/transactions/{id}', 'Api\TransactionController@detail');

    // review
    Route::get('/reviews', 'Api\ReviewController@get');
    Route::post('/review', 'Api\ReviewController@store');

    // Complaint Resources
    Route::group(['prefix' => 'complaint'], function () {
        Route::get('/', 'Api\ComplaintController@get');
        Route::get('/detail/{id}', 'Api\ComplaintController@detail');
        Route::post('/', 'Api\ComplaintController@store');
        Route::post('/reply/{id}', 'Api\ComplaintController@reply');
        Route::post('/close/{id}', 'Api\ComplaintController@close');
        Route::get('/notifications', 'Api\ComplaintController@notification');
        Route::put('/notifications/{id}', 'Api\ComplaintController@seenNotification');
    });

    // promos
    Route::get('/promo', 'Api\PromoController@get');
    Route::get('/promo/detail/{id}', 'Api\PromoController@detail');

    // Otp Resources
    Route::group(['prefix' => 'otp'], function () {
        Route::post('/', 'Api\OtpController@store');
        Route::post('/wa', 'Api\OtpController@storeWa');
        Route::post('/verify', 'Api\OtpController@verify');
        Route::post('/no-auth/sms', 'Api\OtpController@not_authenticated_otp_sms');
        Route::post('/no-auth/wa', 'Api\OtpController@not_authenticated_otp_wa');
        Route::post('/phone-number/sms', 'Api\OtpController@update_phone_sms');
        Route::post('/phone-number/wa', 'Api\OtpController@update_phone_wa');
    });

    // help
    Route::get('/helps/categories', 'Api\HelpController@category');
    Route::get('/helps', 'Api\HelpController@get');

    Route::get('/load-all', 'Api\LoadAllController@get');
    Route::get('/product-all', 'Api\ProductController@allProduct');
    Route::get('/notification-all', 'Api\TotalNotificationController@get');

    Route::get('/broadcast/notification', 'Api\TotalNotificationController@broadcastNotification');
    Route::put('/broadcast/notifications/{id}', 'Api\TotalNotificationController@seenBroadcastNotification');

    Route::get('/delivery', 'Api\DeliveryController@get');

    Route::get('/mission/list', 'Api\MissionController@list');
    Route::get('/mission/{id}', 'Api\MissionController@detail');
    Route::get('/mission-user/{id}', 'Api\MissionController@startMission');

    Route::get('/top-spender/{id}', 'Api\AlertController@topSpender');
    Route::get('/promo/{id}', 'Api\AlertController@promo');
});


// settings
// Route::get('/settings/{key}', 'Api\ProgressJobController@getSettings');


// erp
// auth
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth/erp'

], function () {
    // check customer code erp
    Route::get('/check', 'Api\UserErpController@check');
    // register user erp
    Route::post('/register', 'Api\UserErpController@register');
});
