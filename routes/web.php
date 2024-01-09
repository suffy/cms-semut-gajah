<?php

use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

// SEMUT GAJAH
Route::get('/', 'Auth\LoginController@showLoginForm');
Route::post('/save-token', 'Auth\LoginController@saveToken')->name('save-token');
Route::get('/page-index', 'PageController@index');
Route::get('/products', 'PageController@allProducts');
Route::get('/product-filter', 'PageController@productFilter');
Route::get('/product-category-filter', 'PageController@productCategoryFilter');
Route::get('/products/category/{slug_category}', 'PageController@products');
Route::get('/products/{slug}', 'PageController@productDetail');
Route::get('/cart', 'PageController@cart');
Route::get('/checkout', 'PageController@checkout');
Route::get('/contact', 'PageController@contact');
Route::get('/check-ongkir', 'PageController@checkOngkir');
Route::get('/stat-counter', 'HomeController@statCounter');
Route::post('/send-message', 'HomeController@sendMessage');

Route::get('/blog', 'PageController@blog')->name('blog');
Route::get('/blog/{blog}', 'PageController@blogDetail');

Route::post('/find-voucher', 'PageController@findVoucher');

Route::get('clear-cache', function () {
    \Artisan::call('cache:clear');
    dd("Cache is cleared");
});

Route::get('db-migrate-and-seed', function () {
    \Artisan::call('migrate:fresh --seed');
    dd("DB Migrate and Seed");
});

// running jobs
Route::get('subscribe-daily', function () {
    \Artisan::call('subscribe:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running subscribe job successfully");
});

Route::get('site-daily', function () {
    \Artisan::call('site:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running site job successfully");
});

Route::get('salesman-daily', function () {
    \Artisan::call('salesman:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running salesman job successfully");
});

Route::get('remind-checkout', function () {
    \Artisan::call('remindCheckout:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running salesman job successfully");
});

Route::get('customer-daily', function () {
    \Artisan::call('customer:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running customer job successfully");
});


Route::get('custom-customer/{code}', function ($code) {
    \Artisan::call('customer:custom ' . $code);

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running customer job successfully");
});

Route::get('custom-convert/{code}', function ($code) {
    \Artisan::call('custom:convert ' . $code);

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running customer job successfully");
});

Route::get('convert-daily', function () {
    \Artisan::call('convert:daily ');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running customer job successfully");
});

Route::get('reminder-update/{version}', function ($version) {
    \Artisan::call('reminder:update ' . $version);

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running customer job successfully");
});

Route::get('customer-binaan-daily', function () {
    \Artisan::call('customerbinaan:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running customerbinaan job successfully");
});

Route::get('product-daily', function () {
    \Artisan::call('product:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running product job successfully");
});

Route::get('product-site-daily', function () {
    \Artisan::call('productSite:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running product job successfully");
});

Route::get('stock-daily', function () {
    \Artisan::call('stock:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running stock job successfully");
});

Route::get('complete-complaint-daily', function () {
    \Artisan::call('completecomplaint:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running stock job successfully");
});

Route::get('cod-daily', function () {
    \Artisan::call('cod:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running stock job successfully");
});

Route::get('notification-verification', function () {
    \Artisan::call('notifVerif:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running stock job successfully");
});

Route::get('image-check', function () {
    \Artisan::call('image:check');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running image check job successfully");
});

Route::get('master-approval', function () {
    \Artisan::call('register:daily');

    return response()->json([
        'status' => 'success'
    ]);
    // dd("Running image check job successfully");
});


Route::get('top-spender/{id}/{customer_code}', 'Admin\TopSpenderController@topSpenderList');

// manager route
Route::group(['middleware' => 'App\Http\Middleware\ManagerMiddleware'], function () {

    Route::group(['prefix' => 'manager'], function () {

        // Dashboard
        Route::get('/', 'Admin\HomeController@index');
        Route::get('/all-mapping-site', 'Admin\HomeController@ajaxMappingSite');
        Route::get('/all-customer', 'Admin\HomeController@ajaxCustomer');
        Route::get('/all-product', 'Admin\HomeController@ajaxProduct');
        Route::get('/customers-old', 'Admin\UserController@pageCustomer');
        Route::get('/customer-detail/{id}', 'Admin\UserController@pageCustomerDetail');
        Route::get('/change-status', 'Admin\UserController@changeStatus');

        // Profile
        Route::get('/profile', 'Admin\ProfileController@index');
        Route::put('/profile/update', 'Admin\ProfileController@update');
        Route::put('/profile/update-password', 'Admin\ProfileController@updatePassword');

        // Access
        Route::get('/access', 'Admin\AccessController@index');
        Route::post('/access', 'Admin\AccessController@store');
        Route::put('/access/update', 'Admin\AccessController@update');
        Route::get('/access-detail/{id}', 'Admin\AccessController@show');
        Route::put('/access/{id}', 'Admin\AccessController@updateUser');
        Route::delete('/access/{id}', 'Admin\AccessController@destroy');
        Route::get('/access-import', 'Admin\AccessController@accessImport');
        Route::post('/access/upload-excel', 'Admin\AccessController@uploadExcel');
        Route::post('/access/update-mapping-site', 'Admin\AccessController@updateMappingSite');

        // Sales
        Route::get('/salesmen', 'Admin\SalesController@index');
        Route::get('/salesmen/fetch_data', 'Admin\SalesController@fetch_data');

        // Customers
        Route::get('/customers', 'Admin\CustomerController@index');
        Route::get('/customers/all-salesman/{id}', 'Admin\CustomerController@ajaxSalesman');
        Route::get('/customers/code-approval/{id}', 'Admin\CustomerController@ajaxCode');
        Route::get('/customers/all-mapping-site', 'Admin\CustomerController@ajaxMappingSite');
        Route::post('/customer', 'Admin\CustomerController@store');
        Route::put('/customer/{id}', 'Admin\CustomerController@update');
        Route::delete('/customer/{id}', 'Admin\CustomerController@destroy');
        Route::get('/customers-import', 'Admin\CustomerController@customerImport');
        Route::post('/customers/upload-excel', 'Admin\CustomerController@uploadExcel');
        Route::get('/customers-export', 'Admin\CustomerController@exportExcel');
        Route::post('update-credit-limit/{id}', 'Admin\CustomerController@updateCreditLimit');
        Route::post('/update-mapping-site', 'Admin\CustomerController@updateMappingSite');
        Route::post('/update-salesman', 'Admin\CustomerController@updateSalesman');
        Route::post('/update-salesman-erp', 'Admin\CustomerController@updateSalesmanErp');
        Route::get('/customers/point-history/{id}', 'Admin\CustomerController@pointHistory');

        // Customers recap
        Route::get('/customers/recaps', 'Admin\RecapCustomerController@index');
        Route::get('/customers/recaps/detail/{id}', 'Admin\RecapCustomerController@detail');
        Route::get('/customers/recaps/fetch_data', 'Admin\RecapCustomerController@fetchDataRecap');
        Route::get('/customers/all-products', 'Admin\RecapCustomerController@ajaxProduct')->name('manager.all-products');
        Route::get('/customers/all-brands', 'Admin\RecapCustomerController@ajaxBrand');
        Route::get('/customers/all-groups', 'Admin\RecapCustomerController@ajaxGroup');

        // Customer Approval
        Route::get('/customers/approval', 'Admin\CustomerController@registerApproval');
        Route::get('/approval-detail/{id}', 'Admin\CustomerController@detailApproval');
        Route::post('/approval/update/', 'Admin\CustomerController@updateApproval');
        Route::post('/approve/sitecodeAjax', 'Admin\CustomerController@ajaxSitecode');
        Route::post('/approval/photo-edit', 'Admin\CustomerController@approvalPhotoEdit');
        Route::post('/approval/sendmessage/', 'Admin\CustomerController@sendMessage');

        //Orders
        Route::get('/orders', 'Admin\OrderController@index');
        Route::get('/orders-old', 'Admin\OrderController@indexOld');
        Route::get('/order-detail/{id}', 'Admin\OrderController@orderDetail');
        Route::post('/warehouse-assign/{id}', 'Admin\OrderController@warehouseAssign');
        Route::post('update-detail-order', 'Admin\OrderController@updateStatus')->name('transactions');
        Route::post('update-resi-order', 'Admin\OrderController@updateResi')->name('transactions');
        Route::get('report-sales', 'Admin\OrderController@reportSales');
        Route::get('report-sales/item/excel', 'Admin\OrderController@reportSalesExcel');
        Route::get('report-sales/transaksi/excel', 'Admin\OrderController@reportSalesTransaksiExcel');
        Route::get('report-sales/item', 'Admin\OrderController@reportSalesItem');
        Route::get('report-sales/transaksi', 'Admin\OrderController@reportSalesTransaksi');
        Route::get('report-statistik', 'Admin\OrderController@reportStatistik');

        Route::get('invoice/{order_id}', 'Admin\OrderController@invoice');
        Route::get('delivery/{order_id}', 'Admin\OrderController@delivery');

        // Pages
        Route::get('/dashboard', 'Admin\PageController@pageDashboard');
        Route::get('/users', 'Admin\PageController@pageUsers');
        Route::get('/contacts', 'Admin\MessageController@pageContact');
        Route::get('/complaints', 'Admin\MessageController@pageComplaint');
        Route::resource('posts', 'Admin\PostController', ['as' => 'admin']);
        Route::post('post-status/{id}', 'Admin\PostController@changeStatus');
        Route::resource('images', 'Admin\ImageController', ['as' => 'admin']);
        Route::get('/image-lists', 'Admin\ImageController@getImages');
        Route::post('/store-image', 'Admin\ImageController@storeImageAjax');
        Route::resource('post-categories', 'Admin\PostCategoryController', ['as' => 'admin']);
        Route::resource('banners', 'Admin\BannerController', ['as' => 'admin']);
        Route::post('banner-status/{id}', 'Admin\BannerController@changeStatus');
        Route::resource('social-medias', 'Admin\SocialMediaController', ['as' => 'admin']);
        Route::resource('partner-logo', 'Admin\PartnerLogoController', ['as' => 'admin']);
        Route::resource('categories', 'Admin\CategoryController', ['as' => 'admin']);
        // Route::resource('options', 'Admin\OptionController', ['as' => 'admin']);
        Route::resource('faqs', 'Admin\FAQController', ['as' => 'admin']);
        Route::resource('menus', 'Admin\MenuController', ['as' => 'admin']);
        Route::post('menu-status/{id}', 'Admin\MenuController@changeStatus');
        Route::resource('features', 'Admin\FeatureController', ['as' => 'admin']);
        Route::post('feature-status/{id}', 'Admin\FeatureController@changeStatus');


        Route::get('category-status/{id}', 'Admin\CategoryController@updateStatus');
        Route::get('product-status/{id}', 'Admin\ProductController@updateStatus');
        Route::get('product-avail-status/{id}', 'Admin\ProductController@updateAvailStatus');
        Route::post('update-product-recommendation', 'Admin\ProductController@updateProductRecommendation');
        Route::get('product-rating/', 'Admin\ProductController@productRating');

        //Pages
        Route::resource('pages', 'Admin\PageController', ['as' => 'admin']);

        // Products
        Route::resource('products', 'Admin\ProductController', ['as' => 'admin']);
        Route::get('products/get-image-name/{id}', 'Admin\ProductController@getImageName', ['as' => 'admin']);
        Route::post('products/image/{id}', 'Admin\ProductController@uploadImage', ['as' => 'admin']);
        Route::post('product-multiple-upload', 'Admin\ProductController@multipleUpload', ['as' => 'admin']);
        Route::delete('delete-product-image/{id}', 'Admin\ProductController@imageDestroy', ['as' => 'admin']);
        Route::post('store-product-stock', 'Admin\ProductController@storeProductStock', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Admin\ProductController@editProductStock', ['as' => 'admin']);
        Route::post('duplicate-product/{id}', 'Admin\ProductController@duplicateProduct', ['as' => 'admin']);
        Route::get('delete-product-stock/{id}', 'Admin\ProductController@deleteProductStock', ['as' => 'admin']);
        Route::post('update-product-price-buy/{id}', 'Admin\ProductController@updateProductPriceBuy', ['as' => 'admin']);
        Route::post('update-product-price-sell/{id}', 'Admin\ProductController@updateProductP riceSell', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Admin\ProductController@updateProductStock', ['as' => 'admin']);
        Route::get('product-import', 'Admin\ProductController@pageImportProduct', ['as' => 'admin']);
        Route::post('product-upload-excel', 'Admin\ProductController@uploadExcel', ['as' => 'admin']);
        Route::get('product/availability', 'Admin\ProductController@availability', ['as' => 'admin']);
        Route::get('product/availability/{site_code}', 'Admin\ProductController@siteCode', ['as' => 'admin']);

        Route::get('product/notif', 'Admin\ProductNotifPriceController@index');
        Route::post('product/notif', 'Admin\ProductNotifPriceController@send');

        // voucher
        Route::resource('vouchers', 'Admin\VoucherController', ['as' => 'admin']);

        // promo
        Route::get('promo', 'Admin\PromoController@index', ['as' => 'admin']);
        Route::get('promo/detail/{id}', 'Admin\PromoController@detail', ['as' => 'admin']);
        Route::get('promo/edit/{id}', 'Admin\PromoController@edit', ['as' => 'admin']);
        Route::get('promo/create', 'Admin\PromoController@create', ['as' => 'admin']);
        Route::get('promo/set-list-all', 'Admin\PromoController@ajaxSetListAll');
        Route::get('promo/set-list-sub-group', 'Admin\PromoController@ajaxSetListSubGroup');
        Route::get('promo/list-product', 'Admin\PromoController@ajaxBrand');
        Route::get('promo/list-sub-group', 'Admin\PromoController@ajaxGroup');
        Route::get('promo/sub-group-product', 'Admin\PromoController@ajaxSubGroupProduct');
        Route::get('promo/all-product', 'Admin\PromoController@ajaxProduct');
        Route::post('promo/satuan-product', 'Admin\PromoController@productSatuan');
        Route::post('promo/store', 'Admin\PromoController@store');
        Route::post('promo/update/{id}', 'Admin\PromoController@update');
        Route::post('promo/banner', 'Admin\PromoController@updateBanner');
        Route::get('promo-status/{id}', 'Admin\PromoController@updateStatus');
        Route::get('promo/{id}', 'Admin\PromoController@destroy');
        Route::get('special-promo', 'Admin\PromoController@specialIndex');
        Route::post('special-promo/create', 'Admin\PromoController@specialStore');
        Route::post('special-promo/update', 'Admin\PromoController@specialUpdate');
        Route::post('special-promo/delete/{id}', 'Admin\PromoController@specialDelete');
        Route::get('special-promo-status/{id}', 'Admin\PromoController@updateStatusSpecial');
        Route::get('promo/priority/top', 'Admin\PromoController@priorityTop');
        Route::get('promo/priority/bottom', 'Admin\PromoController@priorityBottom');
        Route::post('promo/priority/top/store', 'Admin\PromoController@priorityTopStore');
        Route::post('promo/priority/bottom/store', 'Admin\PromoController@priorityBottomStore');
        Route::get('banner/priority', 'Admin\BannerController@priority');
        Route::post('banner/priority/store', 'Admin\BannerController@priorityStore');
        // Route::get('ajax-search-brand', 'Admin\PromoController@ajaxBrand');

        //Penawaran
        Route::get('product-offers', 'Admin\OffersController@index');
        Route::post('store-offers', 'Admin\OffersController@store');
        Route::post('update-offers', 'Admin\OffersController@update');
        Route::get('delete-offers/{id}', 'Admin\OffersController@destroy');

        // Category Offers Item
        Route::post('offers-store-product', 'Admin\OffersController@storeOffers');
        Route::get('offers-product-item/{id}', 'Admin\OffersController@listOffers');
        Route::post('offers-item-remove', 'Admin\OffersController@removeOffersItem');
        Route::get('offers-detail/{id}', 'Admin\OffersController@detail_offers');

        Route::resource('locations', 'Admin\LocationController', ['as' => 'admin']);

        Route::get('ajax-search-product', 'Admin\ProductController@searchListProduct');
        // User
        Route::post('/store-user', 'Admin\AdminController@storeUser');
        Route::post('/update-user', 'Admin\AdminController@updateUser');
        Route::get('/delete-user/{id}', 'Admin\AdminController@deleteUser');

        // Report
        Route::get('/report', 'Admin\ReportController@report');

        // Logs
        Route::get('logs', 'LogController@export');

        // SiteID & Site Name
        Route::get('/mapping-site/fetch_data', 'Admin\MappingSiteController@fetch_data');
        Route::resource('mapping-site', 'Admin\MappingSiteController', ['as' => 'admin']);

        // Messages
        Route::get('/messages', 'Admin\MessageController@index');
        Route::delete('/messages/{id}', 'Admin\MessageController@destroy');
        Route::post('/messages/{id}', 'Admin\MessageController@update');

        // Complaints
        Route::get('/complaints', 'Admin\ComplaintController@index');
        Route::get('/complaint/{id}', 'Admin\ComplaintController@show');
        Route::delete('/complaint/{id}', 'Admin\ComplaintController@destroy');
        Route::post('/complaint/{id}', 'Admin\ComplaintController@update');
        Route::post('/complaint/confirm/{id}', 'Admin\ComplaintController@confirm');
        Route::post('/complaint/reject/{id}', 'Admin\ComplaintController@reject');
        Route::post('/complaint/sendStuff/{id}', 'Admin\ComplaintController@sendStuff');
        Route::post('/complaint/send/{id}', 'Admin\ComplaintController@store');

        // Feedbacks
        Route::get('/feedbacks', 'Admin\FeedbackController@index');

        Route::get('/testimonials', 'Admin\Pwa\TestimonialController@index');
        Route::post('/testimonials/store', 'Admin\Pwa\TestimonialController@store');
        // Route::post('/testimonials/update/{id}', 'Admin\Pwa\TestimonialController@update');
        Route::post('/testimonials/accept/{id}', 'Admin\Pwa\TestimonialController@accept');
        Route::post('/testimonials/delete/{id}', 'Admin\Pwa\TestimonialController@delete');
        Route::get('/testimonials/fetch_data', 'Admin\Pwa\TestimonialController@fetch_data');

        Route::get('/options', 'Admin\Pwa\OptionsController@index');
        Route::get('/options/fetch_data', 'Admin\Pwa\OptionsController@fetch_data');
        Route::post('/options/update/{id}', 'Admin\Pwa\OptionsController@update');

        Route::get('/blogs', 'Admin\Pwa\BlogController@index');
        Route::get('/blogs/status/{id}', 'Admin\Pwa\BlogController@updateStatus');
        Route::get('/blogs/fetch_data', 'Admin\Pwa\BlogController@fetch_data');
        Route::post('/blogs/store', 'Admin\Pwa\BlogController@store');
        Route::post('/blogs/update/{id}', 'Admin\Pwa\BlogController@update');
        Route::post('/blogs/delete/{id}', 'Admin\Pwa\BlogController@delete');


        // Jobs
        Route::get('/jobs', 'Admin\JobController@index');

        // help category
        Route::resource('help-categories', 'Admin\HelpCategoryController', ['as' => 'admin']);

        // help
        Route::resource('helps', 'Admin\HelpController', ['as' => 'admin']);

        Route::get('/broadcast', 'Admin\BroadcastWAController@index');
        Route::get('/broadcast/create', 'Admin\BroadcastWAController@create');
        Route::post('/broadcast/store', 'Admin\BroadcastWAController@store');
        Route::get('/broadcast/edit/{id}', 'Admin\BroadcastWAController@edit');
        Route::post('/broadcast/update', 'Admin\BroadcastWAController@update');
        Route::get('/broadcast/delete/{id}', 'Admin\BroadcastWAController@delete');
        Route::get('/broadcast/detail/{id}', 'Admin\BroadcastWAController@detail');

        Route::get('/redeem-point', 'Admin\RedeemPointController@index');
        Route::get('/redeem-point/create', 'Admin\RedeemPointController@create');
        Route::post('/redeem-point/store', 'Admin\RedeemPointController@store');
        Route::get('/redeem-point/edit/{id}', 'Admin\RedeemPointController@edit');
        Route::post('/redeem-point/update', 'Admin\RedeemPointController@update');
        Route::get('/redeem-point/delete/{id}', 'Admin\RedeemPointController@delete');

        // top spender
        Route::get('/top-spender', 'Admin\TopSpenderController@index');
        Route::get('/top-spender/create', 'Admin\TopSpenderController@create');
        Route::post('/top-spender/store', 'Admin\TopSpenderController@store');
        Route::get('/top-spender/edit/{id}', 'Admin\TopSpenderController@edit');
        Route::post('/top-spender/update', 'Admin\TopSpenderController@update');
        Route::post('/top-spender/delete', 'Admin\TopSpenderController@delete');
        Route::get('/top-spender/list/{id}', 'Admin\TopSpenderController@list');

        // mission
        Route::get('/missions', 'Admin\MissionController@index');
        Route::get('/missions/create', 'Admin\MissionController@create');
        Route::post('/missions/store', 'Admin\MissionController@store');
        Route::post('/missions/delete', 'Admin\MissionController@delete');
        Route::post('/missions/deletetask', 'Admin\MissionController@deletetask');
        Route::get('/missions/edit/{id}', 'Admin\MissionController@edit');
        Route::post('/missions/update', 'Admin\MissionController@update');
        Route::get('/misi-status/{id}', 'Admin\MissionController@updateStatus');
        Route::get('/missions/statistik', 'Admin\MissionController@statistik');

        Route::get('/alert', 'Admin\AlertController@index');
        Route::get('/alert/promo', 'Admin\AlertController@ajaxPromo');
        Route::get('/alert/top-spender', 'Admin\AlertController@ajaxTopSpender');
        Route::post('/alert/store', 'Admin\AlertController@store');
    });
});

// superadmin route
Route::group(['middleware' => 'App\Http\Middleware\SuperAdminMiddleware'], function () {

    Route::group(['prefix' => 'superadmin'], function () {

        // Dashboard
        Route::get('/', 'Admin\HomeController@index');
        Route::get('/all-mapping-site', 'Admin\HomeController@ajaxMappingSite');
        Route::get('/all-customer', 'Admin\HomeController@ajaxCustomer');
        Route::get('/all-product', 'Admin\HomeController@ajaxProduct');
        Route::get('/customers-old', 'Admin\UserController@pageCustomer');
        Route::get('/customer-detail/{id}', 'Admin\UserController@pageCustomerDetail');
        Route::get('/change-status', 'Admin\UserController@changeStatus');

        // Access
        Route::get('/access', 'Admin\AccessController@index');
        Route::post('/access', 'Admin\AccessController@store');
        Route::put('/access/update', 'Admin\AccessController@update');
        Route::get('/access-detail/{id}', 'Admin\AccessController@show');
        Route::put('/access/{id}', 'Admin\AccessController@updateUser');
        Route::delete('/access/{id}', 'Admin\AccessController@destroy');
        Route::get('/access-import', 'Admin\AccessController@accessImport');
        Route::post('/access/upload-excel', 'Admin\AccessController@uploadExcel');
        Route::post('/access/update-mapping-site', 'Admin\AccessController@updateMappingSite');

        // Profile
        Route::get('/profile', 'Admin\ProfileController@index');
        Route::put('/profile/update', 'Admin\ProfileController@update');
        Route::put('/profile/update-password', 'Admin\ProfileController@updatePassword');

        // App Version
        Route::get('/app-version', 'Admin\AppVersionController@index');
        Route::get('/app-version/require-update/{id}', 'Admin\AppVersionController@requireUpdate');
        Route::get('/app-version/optional-update/{id}', 'Admin\AppVersionController@optionalUpdate');
        Route::get('/app-version/maintenance/{id}', 'Admin\AppVersionController@maintenance');
        Route::post('/app-version/store', 'Admin\AppVersionController@store');
        Route::post('/app-version/update', 'Admin\AppVersionController@update');
        Route::post('/app-version/delete', 'Admin\AppVersionController@delete');

        // Sales
        Route::get('/salesmen', 'Admin\SalesController@index');
        Route::get('/salesmen/fetch_data', 'Admin\SalesController@fetch_data');

        // Customers
        Route::get('/customers', 'Admin\CustomerController@index');
        Route::get('/customers/all-salesman/{id}', 'Admin\CustomerController@ajaxSalesman');
        Route::get('/customers/code-approval/{id}', 'Admin\CustomerController@ajaxCode');
        Route::get('/customers/all-mapping-site', 'Admin\CustomerController@ajaxMappingSite');
        Route::post('/customer', 'Admin\CustomerController@store');
        Route::put('/customer/{id}', 'Admin\CustomerController@update');
        Route::delete('/customer/{id}', 'Admin\CustomerController@destroy');
        Route::get('/customers-import', 'Admin\CustomerController@customerImport');
        Route::get('/customers-export', 'Admin\CustomerController@exportExcel');
        Route::post('/customers/upload-excel', 'Admin\CustomerController@uploadExcel');
        Route::post('update-credit-limit/{id}', 'Admin\CustomerController@updateCreditLimit');
        Route::post('/update-mapping-site', 'Admin\CustomerController@updateMappingSite');
        Route::post('/update-salesman', 'Admin\CustomerController@updateSalesman');
        Route::post('/update-salesman-erp', 'Admin\CustomerController@updateSalesmanErp');

        // Customers recap
        Route::get('/customers/recaps', 'Admin\RecapCustomerController@index');
        Route::get('/customers/recaps/detail/{id}', 'Admin\RecapCustomerController@detail');
        Route::get('/customers/recaps/fetch_data', 'Admin\RecapCustomerController@fetchDataRecap');
        Route::get('/customers/all-products', 'Admin\RecapCustomerController@ajaxProduct');
        Route::get('/customers/all-brands', 'Admin\RecapCustomerController@ajaxBrand');
        Route::get('/customers/all-groups', 'Admin\RecapCustomerController@ajaxGroup');

        // Customer Approval
        Route::get('/customers/approval', 'Admin\CustomerController@registerApproval');
        Route::get('/approval-detail/{id}', 'Admin\CustomerController@detailApproval');
        Route::post('/approval/update/', 'Admin\CustomerController@updateApproval');
        Route::post('/approve/sitecodeAjax', 'Admin\CustomerController@ajaxSitecode');
        Route::post('/approval/photo-edit', 'Admin\CustomerController@approvalPhotoEdit');
        Route::post('/approval/sendmessage/', 'Admin\CustomerController@sendMessage');

        //Orders
        Route::get('/orders', 'Admin\OrderController@index');
        Route::get('/orders-old', 'Admin\OrderController@indexOld');
        Route::get('/order-detail/{id}', 'Admin\OrderController@orderDetail');
        Route::post('/warehouse-assign/{id}', 'Admin\OrderController@warehouseAssign');
        Route::post('update-detail-order', 'Admin\OrderController@updateStatus')->name('transactions');
        Route::post('update-resi-order', 'Admin\OrderController@updateResi')->name('transactions');
        Route::get('report-sales', 'Admin\OrderController@reportSales');
        Route::get('report-sales/item/excel', 'Admin\OrderController@reportSalesExcel');
        Route::get('report-sales/transaksi/excel', 'Admin\OrderController@reportSalesTransaksiExcel');
        Route::get('report-sales/item', 'Admin\OrderController@reportSalesItem');
        Route::get('report-sales/transaksi', 'Admin\OrderController@reportSalesTransaksi');
        // Route::get('report/item', 'Admin\OrderController@reportSalesItem');
        // Route::get('report/transaksi', 'Admin\OrderController@reportSalesTransaksi');
        Route::get('report-statistik', 'Admin\OrderController@reportStatistik');

        Route::get('invoice/{order_id}', 'Admin\OrderController@invoice');
        Route::get('delivery/{order_id}', 'Admin\OrderController@delivery');

        // Pages
        Route::get('/dashboard', 'Admin\PageController@pageDashboard');
        Route::get('/users', 'Admin\PageController@pageUsers');
        Route::get('/contacts', 'Admin\MessageController@pageContact');
        Route::get('/complaints', 'Admin\MessageController@pageComplaint');
        Route::resource('posts', 'Admin\PostController', ['as' => 'admin']);
        Route::post('post-status/{id}', 'Admin\PostController@changeStatus');
        Route::resource('images', 'Admin\ImageController', ['as' => 'admin']);
        Route::get('/image-lists', 'Admin\ImageController@getImages');
        Route::post('/store-image', 'Admin\ImageController@storeImageAjax');
        Route::resource('post-categories', 'Admin\PostCategoryController', ['as' => 'admin']);
        Route::resource('banners', 'Admin\BannerController', ['as' => 'admin']);
        Route::post('banner-status/{id}', 'Admin\BannerController@changeStatus');
        Route::resource('social-medias', 'Admin\SocialMediaController', ['as' => 'admin']);
        Route::resource('partner-logo', 'Admin\PartnerLogoController', ['as' => 'admin']);
        Route::resource('categories', 'Admin\CategoryController', ['as' => 'admin']);
        // Route::resource('options', 'Admin\OptionController', ['as' => 'admin']);
        Route::resource('faqs', 'Admin\FAQController', ['as' => 'admin']);
        Route::resource('menus', 'Admin\MenuController', ['as' => 'admin']);
        Route::post('menu-status/{id}', 'Admin\MenuController@changeStatus');
        Route::resource('features', 'Admin\FeatureController', ['as' => 'admin']);
        Route::post('feature-status/{id}', 'Admin\FeatureController@changeStatus');


        Route::get('category-status/{id}', 'Admin\CategoryController@updateStatus');
        Route::get('product-status/{id}', 'Admin\ProductController@updateStatus');
        Route::get('product-avail-status/{id}', 'Admin\ProductController@updateAvailStatus');
        Route::post('update-product-recommendation', 'Admin\ProductController@updateProductRecommendation');
        Route::get('product-rating/', 'Admin\ProductController@productRating');

        //Pages
        Route::resource('pages', 'Admin\PageController', ['as' => 'admin']);

        // Products
        Route::resource('products', 'Admin\ProductController', ['as' => 'admin']);
        Route::post('product-multiple-upload', 'Admin\ProductController@multipleUpload', ['as' => 'admin']);
        Route::delete('delete-product-image/{id}', 'Admin\ProductController@imageDestroy', ['as' => 'admin']);
        Route::post('store-product-stock', 'Admin\ProductController@storeProductStock', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Admin\ProductController@editProductStock', ['as' => 'admin']);
        Route::post('duplicate-product/{id}', 'Admin\ProductController@duplicateProduct', ['as' => 'admin']);
        Route::get('delete-product-stock/{id}', 'Admin\ProductController@deleteProductStock', ['as' => 'admin']);
        Route::post('update-product-price-buy/{id}', 'Admin\ProductController@updateProductPriceBuy', ['as' => 'admin']);
        Route::post('update-product-price-sell/{id}', 'Admin\ProductController@updateProductPriceSell', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Admin\ProductController@updateProductStock', ['as' => 'admin']);
        Route::get('product-import', 'Admin\ProductController@pageImportProduct', ['as' => 'admin']);
        Route::post('product-upload-excel', 'Admin\ProductController@uploadExcel', ['as' => 'admin']);
        Route::get('product/availability', 'Admin\ProductController@availability', ['as' => 'admin']);
        Route::get('product/availability/{site_code}', 'Admin\ProductController@siteCode', ['as' => 'admin']);

        Route::resource('vouchers', 'Admin\VoucherController', ['as' => 'admin']);

        // promo
        Route::get('promo', 'Admin\PromoController@index', ['as' => 'admin']);
        Route::get('promo/detail/{id}', 'Admin\PromoController@detail', ['as' => 'admin']);
        Route::get('promo/edit/{id}', 'Admin\PromoController@edit', ['as' => 'admin']);
        Route::get('promo/create', 'Admin\PromoController@create', ['as' => 'admin']);
        Route::get('promo/set-list-all', 'Admin\PromoController@ajaxSetListAll');
        Route::get('promo/set-list-sub-group', 'Admin\PromoController@ajaxSetListSubGroup');
        Route::get('promo/list-product', 'Admin\PromoController@ajaxBrand');
        Route::get('promo/list-sub-group', 'Admin\PromoController@ajaxGroup');
        Route::get('promo/sub-group-product', 'Admin\PromoController@ajaxSubGroupProduct');
        Route::get('promo/all-product', 'Admin\PromoController@ajaxProduct');
        Route::post('promo/satuan-product', 'Admin\PromoController@productSatuan');
        Route::post('promo/store', 'Admin\PromoController@store');
        Route::post('promo/update/{id}', 'Admin\PromoController@update');
        Route::post('promo/banner', 'Admin\PromoController@updateBanner');
        Route::get('promo-status/{id}', 'Admin\PromoController@updateStatus');
        Route::get('promo/{id}', 'Admin\PromoController@destroy');
        Route::get('special-promo', 'Admin\PromoController@specialIndex');
        Route::post('special-promo/create', 'Admin\PromoController@specialStore');
        Route::post('special-promo/update', 'Admin\PromoController@specialUpdate');
        Route::post('special-promo/delete/{id}', 'Admin\PromoController@specialDelete');
        Route::get('special-promo-status/{id}', 'Admin\PromoController@updateStatusSpecial');
        Route::get('promo/priority/top', 'Admin\PromoController@priorityTop');
        Route::get('promo/priority/bottom', 'Admin\PromoController@priorityBottom');
        Route::post('promo/priority/top/store', 'Admin\PromoController@priorityTopStore');
        Route::post('promo/priority/bottom/store', 'Admin\PromoController@priorityBottomStore');
        Route::get('banner/priority', 'Admin\BannerController@priority');
        Route::post('banner/priority/store', 'Admin\BannerController@priorityStore');

        //Penawaran
        Route::get('product-offers', 'Admin\OffersController@index');
        Route::post('store-offers', 'Admin\OffersController@store');
        Route::post('update-offers', 'Admin\OffersController@update');
        Route::get('delete-offers/{id}', 'Admin\OffersController@destroy');

        // Category Offers Item
        Route::post('offers-store-product', 'Admin\OffersController@storeOffers');
        Route::get('offers-product-item/{id}', 'Admin\OffersController@listOffers');
        Route::post('offers-item-remove', 'Admin\OffersController@removeOffersItem');
        Route::get('offers-detail/{id}', 'Admin\OffersController@detail_offers');

        Route::resource('locations', 'Admin\LocationController', ['as' => 'admin']);

        Route::get('ajax-search-product', 'Admin\ProductController@searchListProduct');

        // User
        Route::post('/store-user', 'Admin\AdminController@storeUser');
        Route::post('/update-user', 'Admin\AdminController@updateUser');
        Route::get('/delete-user/{id}', 'Admin\AdminController@deleteUser');

        // Report
        Route::get('/report', 'Admin\ReportController@report');

        // Feedbacks
        Route::get('/feedbacks', 'Admin\FeedbackController@index');

        // Logs
        Route::get('logs', 'LogController@export');

        // Jobs
        Route::get('/jobs', 'Admin\JobController@index');

        // help category
        Route::resource('help-categories', 'Admin\HelpCategoryController', ['as' => 'admin']);

        // help
        Route::resource('helps', 'Admin\HelpController', ['as' => 'admin']);

        // SiteID & Site Name
        Route::get('/mapping-site/fetch_data', 'Admin\MappingSiteController@fetch_data');
        Route::resource('mapping-site', 'Admin\MappingSiteController', ['as' => 'admin']);

        // Messages
        Route::get('/messages', 'Admin\MessageController@index');
        Route::delete('/messages/{id}', 'Admin\MessageController@destroy');
        Route::post('/messages/{id}', 'Admin\MessageController@update');

        Route::get('/complaints', 'Admin\ComplaintController@index');
        Route::get('/complaint/{id}', 'Admin\ComplaintController@show');
        Route::delete('/complaint/{id}', 'Admin\ComplaintController@destroy');
        Route::post('/complaint/{id}', 'Admin\ComplaintController@update');
        Route::post('/complaint/confirm/{id}', 'Admin\ComplaintController@confirm');
        Route::post('/complaint/reject/{id}', 'Admin\ComplaintController@reject');
        Route::post('/complaint/sendStuff/{id}', 'Admin\ComplaintController@sendStuff');
        Route::post('/complaint/send/{id}', 'Admin\ComplaintController@store');

        Route::get('/broadcast', 'Admin\BroadcastWAController@index');
        Route::get('/broadcast/create', 'Admin\BroadcastWAController@create');
        Route::post('/broadcast/store', 'Admin\BroadcastWAController@store');
        Route::get('/broadcast/edit/{id}', 'Admin\BroadcastWAController@edit');
        Route::post('/broadcast/update', 'Admin\BroadcastWAController@update');
        Route::get('/broadcast/delete/{id}', 'Admin\BroadcastWAController@delete');
        Route::get('/broadcast/detail/{id}', 'Admin\BroadcastWAController@detail');

        Route::get('/redeem-point', 'Admin\RedeemPointController@index');
        Route::get('/redeem-point/create', 'Admin\RedeemPointController@create');
        Route::post('/redeem-point/store', 'Admin\RedeemPointController@store');
        Route::get('/redeem-point/edit/{id}', 'Admin\RedeemPointController@edit');
        Route::post('/redeem-point/update', 'Admin\RedeemPointController@update');
        Route::get('/redeem-point/delete/{id}', 'Admin\RedeemPointController@delete');

        // top spender
        Route::get('/top-spender', 'Admin\TopSpenderController@index');
        Route::get('/top-spender/create', 'Admin\TopSpenderController@create');
        Route::post('/top-spender/store', 'Admin\TopSpenderController@store');
        Route::get('/top-spender/edit/{id}', 'Admin\TopSpenderController@edit');
        Route::post('/top-spender/update', 'Admin\TopSpenderController@update');
        Route::post('/top-spender/delete', 'Admin\TopSpenderController@delete');
        Route::get('/top-spender/list/{id}', 'Admin\TopSpenderController@list');

        Route::get('/alert', 'Admin\AlertController@index');
        Route::get('/alert/promo', 'Admin\AlertController@ajaxPromo');
        Route::get('/alert/top-spender', 'Admin\AlertController@ajaxTopSpender');
        Route::post('/alert/store', 'Admin\AlertController@store');
    });
});

// admin route
Route::group(['middleware' => 'App\Http\Middleware\AdminMiddleware'], function () {

    Route::group(['prefix' => 'admin'], function () {

        // Dashboard
        Route::get('/', 'Admin\HomeController@index');
        Route::get('/all-mapping-site', 'Admin\HomeController@ajaxMappingSite');
        Route::get('/customers-old', 'Admin\UserController@pageCustomer');
        Route::get('/customer-detail/{id}', 'Admin\UserController@pageCustomerDetail');
        Route::get('/change-status', 'Admin\UserController@changeStatus');

        // Category
        Route::resource('categories', 'Admin\CategoryController', ['as' => 'admin']);
        Route::get('category-status/{id}', 'Admin\CategoryController@updateStatus');

        // Banner
        Route::resource('banners', 'Admin\BannerController', ['as' => 'admin']);
        Route::post('banner-status/{id}', 'Admin\BannerController@changeStatus');

        // Profile
        Route::get('/profile', 'Admin\ProfileController@index');
        Route::put('/profile/update', 'Admin\ProfileController@update');
        Route::put('/profile/update-password', 'Admin\ProfileController@updatePassword');

        // Access
        Route::get('/access', 'Admin\AccessController@index');
        Route::put('/access/update', 'Admin\AccessController@update');

        // Sales
        Route::get('/salesmen', 'Admin\SalesController@index');
        Route::get('/salesmen/fetch_data', 'Admin\SalesController@fetch_data');

        // Customers
        Route::get('/customers', 'Admin\CustomerController@index');

        // Customer Approval
        Route::get('/customers/approval', 'Admin\CustomerController@registerApproval');
        Route::get('/approval-detail/{id}', 'Admin\CustomerController@detailApproval');
        Route::post('/approval/update/', 'Admin\CustomerController@updateApproval');
        Route::post('/approve/sitecodeAjax', 'Admin\CustomerController@ajaxSitecode');
        Route::post('/approval/photo-edit', 'Admin\CustomerController@approvalPhotoEdit');
        Route::post('/approval/sendmessage/', 'Admin\CustomerController@sendMessage');

        //Orders
        Route::get('/orders', 'Admin\OrderController@index');
        Route::get('/orders-old', 'Admin\OrderController@indexOld');
        Route::get('/order-detail/{id}', 'Admin\OrderController@orderDetail');
        Route::post('/warehouse-assign/{id}', 'Admin\OrderController@warehouseAssign');
        Route::post('update-detail-order', 'Admin\OrderController@updateStatus')->name('transactions');
        Route::post('update-resi-order', 'Admin\OrderController@updateResi')->name('transactions');
        Route::get('report-sales', 'Admin\OrderController@reportSales');
        Route::get('report-sales/item', 'Admin\OrderController@reportSalesItem');
        Route::get('report-sales/transaksi', 'Admin\OrderController@reportSalesTransaksi');
        Route::get('report-statistik', 'Admin\OrderController@reportStatistik');

        Route::get('invoice/{order_id}', 'Admin\OrderController@invoice');
        Route::get('delivery/{order_id}', 'Admin\OrderController@delivery');

        // Products
        Route::resource('products', 'Admin\ProductController', ['as' => 'admin']);
        Route::get('products/get-image-name/{id}', 'Admin\ProductController@getImageName', ['as' => 'admin']);
        Route::post('product-multiple-upload', 'Admin\ProductController@multipleUpload', ['as' => 'admin']);
        Route::delete('delete-product-image/{id}', 'Admin\ProductController@imageDestroy', ['as' => 'admin']);
        Route::post('store-product-stock', 'Admin\ProductController@storeProductStock', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Admin\ProductController@editProductStock', ['as' => 'admin']);
        Route::post('duplicate-product/{id}', 'Admin\ProductController@duplicateProduct', ['as' => 'admin']);
        Route::get('delete-product-stock/{id}', 'Admin\ProductController@deleteProductStock', ['as' => 'admin']);
        Route::post('update-product-price-buy/{id}', 'Admin\ProductController@updateProductPriceBuy', ['as' => 'admin']);
        Route::post('update-product-price-sell/{id}', 'Admin\ProductController@updateProductPriceSell', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Admin\ProductController@updateProductStock', ['as' => 'admin']);
        Route::get('product-import', 'Admin\ProductController@pageImportProduct', ['as' => 'admin']);
        Route::post('product-upload-excel', 'Admin\ProductController@uploadExcel', ['as' => 'admin']);
        Route::get('product/availability', 'Admin\ProductController@availability', ['as' => 'admin']);
        Route::get('product/availability/{site_code}', 'Admin\ProductController@siteCode', ['as' => 'admin']);

        // promo
        Route::get('promo', 'Admin\PromoController@index', ['as' => 'admin']);
        Route::get('promo/detail/{id}', 'Admin\PromoController@detail', ['as' => 'admin']);
        Route::get('promo/edit/{id}', 'Admin\PromoController@edit', ['as' => 'admin']);
        Route::get('promo/create', 'Admin\PromoController@create', ['as' => 'admin']);
        Route::get('promo/set-list-all', 'Admin\PromoController@ajaxSetListAll');
        Route::get('promo/set-list-sub-group', 'Admin\PromoController@ajaxSetListSubGroup');
        Route::get('promo/list-product', 'Admin\PromoController@ajaxBrand');
        Route::get('promo/list-sub-group', 'Admin\PromoController@ajaxGroup');
        Route::get('promo/sub-group-product', 'Admin\PromoController@ajaxSubGroupProduct');
        Route::get('promo/all-product', 'Admin\PromoController@ajaxProduct');
        Route::post('promo/satuan-product', 'Admin\PromoController@productSatuan');
        Route::post('promo/store', 'Admin\PromoController@store');
        Route::post('promo/update/{id}', 'Admin\PromoController@update');
        Route::post('promo/banner', 'Admin\PromoController@updateBanner');
        Route::get('promo-status/{id}', 'Admin\PromoController@updateStatus');
        Route::get('promo/{id}', 'Admin\PromoController@destroy');
        Route::get('special-promo', 'Admin\PromoController@specialIndex');
        Route::post('special-promo/create', 'Admin\PromoController@specialStore');
        Route::post('special-promo/update', 'Admin\PromoController@specialUpdate');
        Route::post('special-promo/delete/{id}', 'Admin\PromoController@specialDelete');
        Route::get('special-promo-status/{id}', 'Admin\PromoController@updateStatusSpecial');
        Route::get('promo/priority/top', 'Admin\PromoController@priorityTop');
        Route::get('promo/priority/bottom', 'Admin\PromoController@priorityBottom');
        Route::post('promo/priority/top/store', 'Admin\PromoController@priorityTopStore');
        Route::post('promo/priority/bottom/store', 'Admin\PromoController@priorityBottomStore');
        Route::get('banner/priority', 'Admin\BannerController@priority');
        Route::post('banner/priority/store', 'Admin\BannerController@priorityStore');

        Route::get('product-status/{id}', 'Admin\ProductController@updateStatus');
        Route::get('product-avail-status/{id}', 'Admin\ProductController@updateAvailStatus');

        // Logs
        Route::get('logs', 'LogController@export');

        // Messages
        Route::get('/messages', 'Admin\MessageController@index');
        Route::delete('/messages/{id}', 'Admin\MessageController@destroy');
        Route::post('/messages/{id}', 'Admin\MessageController@update');

        Route::get('/complaints', 'Admin\MessageController@pageComplaint');
        Route::get('/complaints', 'Admin\ComplaintController@index');
        Route::get('/complaint/{id}', 'Admin\ComplaintController@show');
        Route::delete('/complaint/{id}', 'Admin\ComplaintController@destroy');
        Route::post('/complaint/{id}', 'Admin\ComplaintController@update');
        Route::post('/complaint/confirm/{id}', 'Admin\ComplaintController@confirm');
        Route::post('/complaint/reject/{id}', 'Admin\ComplaintController@reject');
        Route::post('/complaint/sendStuff/{id}', 'Admin\ComplaintController@sendStuff');
        Route::post('/complaint/send/{id}', 'Admin\ComplaintController@store');

        // top spender
        Route::get('/top-spender', 'Admin\TopSpenderController@index');
        Route::get('/top-spender/create', 'Admin\TopSpenderController@create');
        Route::post('/top-spender/store', 'Admin\TopSpenderController@store');
        Route::get('/top-spender/edit/{id}', 'Admin\TopSpenderController@edit');
        Route::post('/top-spender/update', 'Admin\TopSpenderController@update');
        Route::post('/top-spender/delete', 'Admin\TopSpenderController@delete');
        Route::get('/top-spender/list/{id}', 'Admin\TopSpenderController@list');
    });
});

// distributor route
Route::group(['middleware' => 'App\Http\Middleware\DistributorMiddleware'], function () {

    Route::group(['prefix' => 'distributor'], function () {

        // Dashboard
        Route::get('/', 'Admin\HomeController@index');
        Route::get('/customers-old', 'Admin\UserController@pageCustomer');
        Route::get('/customer-detail/{id}', 'Admin\UserController@pageCustomerDetail');
        Route::get('/change-status', 'Admin\UserController@changeStatus');

        // Profile
        Route::get('/profile', 'Admin\ProfileController@index');
        Route::put('/profile/update', 'Admin\ProfileController@update');
        Route::put('/profile/update-password', 'Admin\ProfileController@updatePassword');

        // Sales
        Route::get('/salesmen', 'Distributor\SalesController@index');
        Route::get('/salesmen/fetch_data', 'Distributor\SalesController@fetch_data');

        // Customers
        Route::get('/customers', 'Distributor\CustomerController@index');
        Route::get('/customers/all-salesman/{id}', 'Admin\CustomerController@ajaxSalesman');
        Route::get('/customers/code-approval/{id}', 'Admin\CustomerController@ajaxCode');
        Route::get('/customers/all-mapping-site', 'Admin\CustomerController@ajaxMappingSite');
        Route::post('/customer', 'Distributor\CustomerController@store');
        Route::put('/customer/{id}', 'Distributor\CustomerController@update');
        Route::delete('/customer/{id}', 'Distributor\CustomerController@destroy');
        Route::get('/customers-import', 'Distributor\CustomerController@customerImport');
        Route::get('/customers-export', 'Distributor\CustomerController@exportExcel');
        Route::post('/customers/upload-excel', 'Distributor\CustomerController@uploadExcel');
        Route::post('update-credit-limit/{id}', 'Distributor\CustomerController@updateCreditLimit');
        Route::post('update-mapping-site/{id}', 'Distributor\CustomerController@updateMappingSite');

        // Customers recap
        Route::get('/customers/recaps', 'Admin\RecapCustomerController@index');
        Route::get('/customers/recaps/detail/{id}', 'Admin\RecapCustomerController@detail');
        Route::get('/customers/recaps/fetch_data', 'Admin\RecapCustomerController@fetchDataRecap');
        Route::get('/customers/all-products', 'Admin\RecapCustomerController@ajaxProduct');
        Route::get('/customers/all-brands', 'Admin\RecapCustomerController@ajaxBrand');
        Route::get('/customers/all-groups', 'Admin\RecapCustomerController@ajaxGroup');

        // Customer Approval
        Route::get('/customers/approval', 'Admin\CustomerController@registerApproval');
        Route::get('/approval-detail/{id}', 'Admin\CustomerController@detailApproval');
        Route::post('/approval/update/', 'Admin\CustomerController@updateApproval');
        Route::post('/approve/sitecodeAjax', 'Admin\CustomerController@ajaxSitecode');
        Route::post('/approval/photo-edit', 'Admin\CustomerController@approvalPhotoEdit');
        Route::post('/approval/sendmessage/', 'Admin\CustomerController@sendMessage');

        //Orders
        Route::get('/orders', 'Distributor\OrderController@index');
        Route::get('/orders-old', 'Distributor\OrderController@indexOld');
        Route::get('/order-detail/{id}', 'Distributor\OrderController@orderDetail');
        Route::post('/warehouse-assign/{id}', 'Distributor\OrderController@warehouseAssign');
        Route::post('update-detail-order', 'Distributor\OrderController@updateStatus')->name('transactions');
        Route::post('update-resi-order', 'Distributor\OrderController@updateResi')->name('transactions');
        Route::get('report-sales', 'Distributor\OrderController@reportSales');
        Route::get('report-sales/item/excel', 'Distributor\OrderController@reportSalesExcel');
        Route::get('report-sales/transaksi/excel', 'Distributor\OrderController@reportSalesTransaksiExcel');
        Route::get('report-sales/item', 'Distributor\OrderController@reportSalesItem');
        Route::get('report-sales/transaksi', 'Distributor\OrderController@reportSalesTransaksi');
        Route::get('report-statistik', 'Distributor\OrderController@reportStatistik');

        Route::get('invoice/{order_id}', 'Admin\OrderController@invoice');
        Route::get('delivery/{order_id}', 'Admin\OrderController@delivery');

        // Pages
        Route::get('/dashboard', 'Admin\PageController@pageDashboard');
        Route::get('/users', 'Admin\PageController@pageUsers');
        Route::get('/contacts', 'Admin\MessageController@pageContact');
        Route::get('/complaints', 'Distributor\MessageController@pageComplaint');
        Route::resource('posts', 'Admin\PostController', ['as' => 'admin']);
        Route::post('post-status/{id}', 'Admin\PostController@changeStatus');
        Route::resource('images', 'Admin\ImageController', ['as' => 'admin']);
        Route::get('/image-lists', 'Admin\ImageController@getImages');
        Route::post('/store-image', 'Admin\ImageController@storeImageAjax');
        Route::resource('post-categories', 'Admin\PostCategoryController', ['as' => 'admin']);
        Route::resource('banners', 'Admin\BannerController', ['as' => 'admin']);
        Route::post('banner-status/{id}', 'Admin\BannerController@changeStatus');
        Route::resource('social-medias', 'Admin\SocialMediaController', ['as' => 'admin']);
        Route::resource('partner-logo', 'Admin\PartnerLogoController', ['as' => 'admin']);
        Route::resource('categories', 'Admin\CategoryController', ['as' => 'admin']);
        // Route::resource('options', 'Admin\OptionController', ['as' => 'admin']);
        Route::resource('faqs', 'Admin\FAQController', ['as' => 'admin']);
        Route::resource('menus', 'Admin\MenuController', ['as' => 'admin']);
        Route::post('menu-status/{id}', 'Admin\MenuController@changeStatus');
        Route::resource('features', 'Admin\FeatureController', ['as' => 'admin']);
        Route::post('feature-status/{id}', 'Admin\FeatureController@changeStatus');


        Route::get('category-status/{id}', 'Admin\CategoryController@updateStatus');
        Route::get('product-status/{id}', 'Distributor\ProductController@updateStatus');
        Route::get('product-avail-status/{id}', 'Distributor\ProductController@updateAvailStatus');
        Route::post('update-product-recommendation', 'Distributor\ProductController@updateProductRecommendation');
        Route::get('product-rating/', 'Distributor\ProductController@productRating');

        //Pages
        Route::resource('pages', 'Admin\PageController', ['as' => 'admin']);

        // Products
        Route::resource('products', 'Distributor\ProductController', ['as' => 'admin']);
        Route::post('product-multiple-upload', 'Distributor\ProductController@multipleUpload', ['as' => 'admin']);
        Route::delete('delete-product-image/{id}', 'Distributor\ProductController@imageDestroy', ['as' => 'admin']);
        Route::post('store-product-stock', 'Distributor\ProductController@storeProductStock', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Distributor\ProductController@editProductStock', ['as' => 'admin']);
        Route::post('duplicate-product/{id}', 'Distributor\ProductController@duplicateProduct', ['as' => 'admin']);
        Route::get('delete-product-stock/{id}', 'Distributor\ProductController@deleteProductStock', ['as' => 'admin']);
        Route::post('update-product-price-buy/{id}', 'Distributor\ProductController@updateProductPriceBuy', ['as' => 'admin']);
        Route::post('update-product-price-sell/{id}', 'Distributor\ProductController@updateProductPriceSell', ['as' => 'admin']);
        Route::post('update-product-stock/{id}', 'Distributor\ProductController@updateProductStock', ['as' => 'admin']);
        Route::get('product-import', 'Distributor\ProductController@pageImportProduct', ['as' => 'admin']);
        Route::post('product-upload-excel', 'Distributor\ProductController@uploadExcel', ['as' => 'admin']);
        Route::get('product/availability', 'Distributor\ProductController@availability', ['as' => 'admin']);
        Route::get('product/availability/{site_code}', 'Distributor\ProductController@siteCode', ['as' => 'admin']);

        Route::resource('vouchers', 'Admin\VoucherController', ['as' => 'admin']);

        //Penawaran
        Route::get('product-offers', 'Admin\OffersController@index');
        Route::post('store-offers', 'Admin\OffersController@store');
        Route::post('update-offers', 'Admin\OffersController@update');
        Route::get('delete-offers/{id}', 'Admin\OffersController@destroy');

        // Category Offers Item
        Route::post('offers-store-product', 'Admin\OffersController@storeOffers');
        Route::get('offers-product-item/{id}', 'Admin\OffersController@listOffers');
        Route::post('offers-item-remove', 'Admin\OffersController@removeOffersItem');
        Route::get('offers-detail/{id}', 'Admin\OffersController@detail_offers');

        Route::resource('locations', 'Admin\LocationController', ['as' => 'admin']);

        Route::get('ajax-search-product', 'Distributor\ProductController@searchListProduct');
        // User
        Route::post('/store-user', 'Admin\AdminController@storeUser');
        Route::post('/update-user', 'Admin\AdminController@updateUser');
        Route::get('/delete-user/{id}', 'Admin\AdminController@deleteUser');

        // Report
        Route::get('/report', 'Admin\ReportController@report');

        // Logs
        Route::get('logs', 'LogController@export');

        // Messages
        Route::get('/messages', 'Admin\MessageController@index');
        Route::delete('/messages/{id}', 'Admin\MessageController@destroy');
        Route::post('/messages/{id}', 'Admin\MessageController@update');

        Route::get('/complaints', 'Distributor\ComplaintController@index');
        Route::get('/complaint/{id}', 'Distributor\ComplaintController@show');
        Route::delete('/complaint/{id}', 'Distributor\ComplaintController@destroy');
        Route::post('/complaint/{id}', 'Distributor\ComplaintController@update');
        Route::post('/complaint/confirm/{id}', 'Distributor\ComplaintController@confirm');
        Route::post('/complaint/reject/{id}', 'Distributor\ComplaintController@reject');
        Route::post('/complaint/sendStuff/{id}', 'Distributor\ComplaintController@sendStuff');
        Route::post('/complaint/send/{id}', 'Distributor\ComplaintController@store');
    });
});

//Chats
Route::get('admin/chats', 'Admin\ChatController@index', ['as' => 'admin']);
Route::get('admin/chats/list', 'Admin\ChatController@getChats', ['as' => 'admin']);
Route::post('admin/chats/{id}', 'Admin\ChatController@getMessages', ['as' => 'admin']);
Route::post('admin/chat', 'Admin\ChatController@sendMessageUser', ['as' => 'admin']);
Route::post('admin/chat/{id}', 'Admin\ChatController@sendMessageAdmin', ['as' => 'admin']);
Route::post('admin/broadcast', 'Admin\ChatController@broadcastMessage', ['as' => 'admin']);


// Member public
Route::group(['prefix' => 'member'], function () {

    //Login and Auth
    Route::get('login', 'Member\LoginController@index')->name('member.login');
    Route::post('auth', 'Member\LoginController@auth')->name('member.auth');
    Route::get('register', 'Member\RegisterController@index')->name('member.register.index');
    Route::post('register/store', 'Member\RegisterController@store')->name('member.register.store');
});



// Shoppping Cart
Route::get('member/shopping-cart', 'Member\ShoppingCartController@cartList');
Route::get('member/count-shopping-cart', 'Member\ShoppingCartController@cartCount');
Route::post('member/product-add-to-cart', 'Member\ShoppingCartController@addToCart');
Route::post('member/product-update-to-cart', 'Member\ShoppingCartController@changeQty');
Route::post('member/product-remove-from-cart', 'Member\ShoppingCartController@removeFromCart');

// Member login / Customer / Media owner
Route::group(['prefix' => 'member', 'middleware' => 'App\Http\Middleware\MemberMiddleware'], function () {

    // Dashboard
    Route::get('/', 'Member\PageController@dashboard')->name('member.dashboard');
    Route::get('dashboard', 'Member\PageController@dashboard')->name('member.dashboard');

    // User
    Route::get('profile', 'Member\UserController@index')->name('user.index');
    Route::get('edit-profile/{user}/edit', 'Member\UserController@edit');
    Route::put('profile/{user}', 'Member\UserController@update');

    // Address
    Route::get('address', 'Member\UserAddressController@index');
    Route::post('address', 'Member\UserAddressController@createAddress');
    Route::post('update-address/{id}', 'Member\UserAddressController@updateAddress');
    Route::post('delete-address/{id}', 'Member\UserAddressController@deleteAddress');
    Route::get('address-list/{id}', 'Member\UserAddressController@getAddressByUserId');
    Route::post('checkout-new-address', 'Member\UserAddressController@checkoutNewAddress');

    //Wishlist
    Route::get('wishlist', 'Member\WishlistController@index');
    Route::get('wishlist-store/{product_id}/{user_id}', 'Member\WishlistController@store');
    Route::get('wishlist-detail/{product}', 'Member\WishlistController@detail');

    // Orders
    Route::get('order', 'Member\OrderController@index')->name('transactions');
    Route::post('create-order', 'Member\OrderController@store')->name('transactions');
    Route::post('payment-upload', 'Member\OrderController@paymentUpload')->name('transactions');
    Route::get('history', 'Member\OrderController@history')->name('transactions');
    Route::get('order/search', 'Member\OrderController@search');
    Route::get('order-detail/{transaction}', 'Member\OrderController@detail');
    Route::get('invoice/{order_id}', 'Member\OrderController@invoice');
    Route::get('payment/{order_id}', 'Member\OrderController@payment');
    Route::post('submit-review/{order_id}', 'Member\OrderController@submitReview')->name('review');

    // //Notifications
    Route::resource('notifications', 'Member\NotificationController', ['as' => 'member']);
    Route::get('/open-notification/{id}', 'Member\NotificationController@openNotification');

    // //Logout
    Route::get('logout', 'Member\LoginController@logout');
    Route::get('resend-invoice/{id}', 'Member\OrderController@resendEmailInvoice');
});


Route::get('/{slug}', 'PageController@pagePostDetail')->name('post-detail');
Route::get('/page', 'PageController@page')->name('page');
Route::get('page/{slug}', 'PageController@pageDetail')->name('page-detail');

Route::get('/mapping-site/fetchAjax', 'Admin\MappingSiteController@ajaxFetch');
Route::put('/mapping-site/min-update', 'Admin\MappingSiteController@siteUpdate');
