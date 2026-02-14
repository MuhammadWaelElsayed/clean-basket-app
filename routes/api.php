<?php

use App\Http\Controllers\API\AddOnController;
use App\Http\Controllers\API\AgentVendorTimeSlotsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PartnerController;
use App\Http\Controllers\API\PackageController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\ExternalAuthController;
use App\Http\Controllers\API\ExternalOrderController;
use App\Http\Controllers\API\IntegrationTokenController;
use App\Http\Controllers\API\IssueCategoryController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\OrderItemController;
use App\Http\Controllers\API\OrderPriorityController;
use App\Http\Controllers\API\PackageReportsController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\PromoController;
use App\Http\Controllers\API\ReferralController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\API\WebhookController;

//test
use App\Http\Controllers\API\ReportDataController;
use App\Http\Controllers\API\ServiceTypeController;
use App\Http\Controllers\API\SubIssueCategoryController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\VendorSlotsController;
use App\Http\Controllers\API\VoucherController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\WhatsappBotController;
use App\Http\Controllers\API\ServiceFeeSettingsController;

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


Route::get('/vendors/select', [ReportDataController::class, 'listForSelect']);
Route::get('/report/orders', [ReportDataController::class, 'ordersReport']);
Route::get('/report/orders/delivered', [ReportDataController::class, 'allOrderDeliveredByDate']);
Route::get('/report/orders/pickup-dropoff', [ReportDataController::class, 'ordersPickupDropoffReport']);
Route::get('/report/orders/pickup-dropoff-all', [ReportDataController::class, 'ordersPickupDropoffReportAll']);
Route::get('/report/orders/debug', [ReportDataController::class, 'debugOrdersData']);


// Vendor Working Hours API Routes
Route::apiResource('vendor-working-hours', \App\Http\Controllers\API\VendorWorkingHoursController::class);
Route::get('/vendor-working-hours/all', [\App\Http\Controllers\API\VendorWorkingHoursController::class, 'getAllWorkingHours']);
Route::post('/vendor-working-hours/multiple', [\App\Http\Controllers\API\VendorWorkingHoursController::class, 'getMultipleVendorsWorkingHours']);


Route::group(['middleware' => ['apiLanguage']], function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/guestLogin', [AuthController::class, 'guestLogin']);

    Route::post('/google-signin', [AuthController::class, 'googleSignin']);
    Route::post('/apple-signin', [AuthController::class, 'appleSignin']);
    Route::post('/facebook-signin', [AuthController::class, 'facebookSignin']);

    Route::post('/verifyOTP', [AuthController::class, 'verifyOTP']);
    Route::post('/resendOTP', [AuthController::class, 'resendOTP']);

    Route::get('/getCities', [UserController::class, 'getCities']);
    Route::get('/getServices', [UserController::class, 'getServices']);
    Route::get('/getAreasByCity', [UserController::class, 'getAreasByCity']);
    Route::post('/becomePartner', [UserController::class, 'becomePartner']);
});

//using middleware - User App APIs
Route::group(['middleware' => ['auth:sanctum', 'apiLanguage']], function () {

    Route::post('/update-location', [UserController::class, 'updateLocation']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/update-fcm-token', [UserController::class, 'updateFcmToken']);
    Route::post('/delete-account', [UserController::class, 'deleteAccount']);
    Route::post('/logout', [AuthController::class, 'signout']);


    Route::get('/getProfile', [UserController::class, 'profile']);

    Route::post('/is-last-order-reviewed', [UserController::class, 'isLastOrderReviewed']);

    Route::get('/getDashboard', [UserController::class, 'getDashboard']);
    Route::get('/onboardData', [UserController::class, 'onboardData']);
    Route::get('/getServiceItems', [UserController::class, 'getServiceItems']);
    Route::get('/getServiceTypes', [UserController::class, 'getServiceTypes']);

    Route::get('/getUserAddress', [UserController::class, 'getUserAddress']);
    Route::post('/addUserAddress', [UserController::class, 'addUserAddress']);
    Route::post('/updateUserAddress', [UserController::class, 'updateUserAddress']);
    Route::post('/makeAddressDefault', [UserController::class, 'makeAddressDefault']);
    Route::post('/removeAddress', [UserController::class, 'removeAddress']);
    Route::get('/getPickupTimeslots', [UserController::class, 'getPickupTimeslots']);
    Route::get('/getDropoffTimeslots', [UserController::class, 'getDropoffTimeslots']);
    Route::post('/submitContact', [UserController::class, 'submitContact']);

    // Promo code
    Route::get('/myPromoCodes', [PromoController::class, 'myPromoCodes']);
    Route::post('/applyPromoCode', [PromoController::class, 'applyPromoCode']);
    Route::post('/referral/reward', [ReferralController::class, 'rewardReferral']);
    Route::get('/referral/users-used-my-code', [ReferralController::class, 'usersUsedMyReferral']);

    Route::get('/myOrders', [OrderController::class, 'myOrders']);
    Route::get('/getUnpaidOrders', [OrderController::class, 'getUnpaidOrders']);
    Route::get('/getUnpaidAndPartialOrders', [OrderController::class, 'getUnpaidAndPartialOrders']);
    Route::post('/placeOrder', [OrderController::class, 'placeOrder']);
    Route::post('/confirmOrder', [OrderController::class, 'confirmOrder']);
    Route::post('/payUsingPackageAndWallet', [OrderController::class, 'payUsingPackageAndWallet']);
    Route::post('/calculatePaymentPreview', [OrderController::class, 'calculatePaymentPreview']);
    Route::post('/applyVoucher', [VoucherController::class, 'applyVoucher']);
    Route::post('/cancelOrder', [OrderController::class, 'cancelOrder']);
    Route::get('/getOrderDetails', [OrderController::class, 'getOrderDetails']);

    Route::get('/getNotifications', [UserController::class, 'getNotifications']);
    Route::post('/markasReadNotification', [UserController::class, 'markasReadNotification']);
    Route::post('/clearAllNotifications', [UserController::class, 'clearAllNotifications']);

    /* --------------------------------- wallets -------------------------------- */
    // show balance
    Route::get('/wallet', [WalletController::class, 'getBalance']);
    //  transactions
    Route::get('/wallet/transactions', [WalletController::class, 'getTransactions']);
    // To Users (SDK)
    Route::post('/wallet/charge', [WalletController::class, 'chargeFromSDK']);
    // Admin (manual)
    Route::post('/admin/wallet/charge', [WalletController::class, 'manualCharge']);
    // deduct on order
    Route::post('/wallet/deduct', [WalletController::class, 'deduct']);

    /* -------------------------------- packages -------------------------------- */
    //if canPay by Package Or wallet
    Route::get('/canPay', [OrderController::class, 'canPay']);

    //Display the packages available to the user
    Route::get('/packages', [PackageController::class, 'index']);
    Route::post('/packages/purchase', [PackageController::class, 'purchasePackage']);
    Route::get('/myPackage', [PackageController::class, 'getMyPackages']);
    // Route::post('/toggleAutoRenew', [PackageController::class, 'toggleAutoRenew']);
    // Route::post('/packages/upgrade', [PackageController::class, 'upgradePackage']);
    Route::get('/myPackages/current', [PackageController::class, 'getCurrentPackage']);
    Route::get('/myPackages/history', [PackageController::class, 'getHistory']);

    /* --------------------------------- vouchers -------------------------------- */
    Route::post('/vouchers/gift', [VoucherController::class, 'giftVoucher']);
    Route::get('/vouchers/mine', [VoucherController::class, 'myVouchers']);
    Route::get('/vouchers/gifted', [VoucherController::class, 'giftedVouchers']);

    /* --------------------------------- sorting -------------------------------- */
    Route::get('order-priorities', [OrderPriorityController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('items', [ItemController::class, 'index']);
    Route::post('items/services/assign', [ItemController::class, 'assignServices']);
    Route::get('service-types', [ServiceTypeController::class, 'index']);
    Route::get('add-ons', [AddOnController::class, 'index']);
    Route::post('createOrderByClient', [OrderController::class, 'createOrderByClient']);
    Route::post('updateOrderByClient', [OrderController::class, 'updateOrderByClient']);
    Route::post('orders/{order}/items', [OrderItemController::class, 'store']);
    Route::get('showOrderByClient/{order}', [OrderController::class, 'showOrderByClient']);

    /* --------------------------------- tickets -------------------------------- */
    Route::apiResource('issue-categories', IssueCategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('issue-categories.sub-issue-categories', SubIssueCategoryController::class)
        ->parameters(['sub-issue-categories' => 'sub_issue_category'])
        ->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('tickets', TicketController::class)
        ->only(['index', 'show', 'store', 'update']);
    // Vendor Slots API Routes
    Route::get('/vendors/70/pickup-slots', [VendorSlotsController::class, 'pickup']);
    Route::get('/vendors/70/delivery-slots', [VendorSlotsController::class, 'delivery']);
});

//Driver APIs
Route::group(['prefix' => 'driver', 'middleware' => 'apiLanguage'], function () {
    //Guest User can access this routes
    Route::post('/login', [DriverController::class, 'login']);
    Route::post('/signup-freelance', [DriverController::class, 'signupFreelance']);

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::post('/update-location', [DriverController::class, 'updateLocation']);
        Route::post('/update-profile', [DriverController::class, 'updateProfile']);
        Route::post('/update-fcm-token', [DriverController::class, 'updateFcmToken']);
        Route::post('/delete-account', [DriverController::class, 'deleteAccount']);
        Route::post('/updateOnlineStatus', [DriverController::class, 'updateOnlineStatus']);

        Route::post('/logout', [DriverController::class, 'signout']);

        Route::get('/getProfile', [DriverController::class, 'getProfile']);
        Route::get('/getActiveOrders', [DriverController::class, 'getActiveOrders']);
        Route::get('/getCompletedOrders', [DriverController::class, 'getCompletedOrders']);
        Route::get('/getOrderDetails', [DriverController::class, 'getOrderDetails']);
        Route::post('/get-optimized-route', [DriverController::class, 'getOptimizedRoute']);

        Route::post('/updateOrderStatus', [DriverController::class, 'updateOrderStatus']);

        Route::get('/getBasketRequests', [DriverController::class, 'getBasketRequests']);
        Route::post('/updateBasketStatus', [DriverController::class, 'updateBasketStatus']);

// Order request system - NEW
        Route::get('/getPendingOrders', [DriverController::class, 'getPendingOrders']);
        Route::post('/acceptPendingOrder', [DriverController::class, 'acceptPendingOrder']);
        Route::post('/rejectPendingOrder', [DriverController::class, 'rejectPendingOrder']);
        Route::get('/getPendingOrderDetails', [DriverController::class, 'getPendingOrderDetails']);

        Route::get('/getNotifcations', [DriverController::class, 'getNotifcations']);
        Route::get('/getNotifcationBadge', [DriverController::class, 'getNotifcationBadge']);
        Route::post('/markasReadNotifcation', [DriverController::class, 'markasReadNotifcation']);
        Route::post('/clearAllNotifications', [DriverController::class, 'clearAllNotifications']);
    });
});

//Vendor APIs
Route::group(['prefix' => 'vendor', 'middleware' => 'apiLanguage'], function () {
    //Guest User can access this routes
    Route::post('/login', [VendorController::class, 'login']);

    Route::group(['middleware' => ['auth:vendors']], function () {

        Route::post('/logout', [VendorController::class, 'signout']);

        Route::get('/getProfile', [VendorController::class, 'getProfile']);
        Route::get('/getMyOrders', [VendorController::class, 'getMyOrders']);
        Route::get('/getOrderDetails', [VendorController::class, 'getOrderDetails']);

        Route::post('/updateOrderStatus', [VendorController::class, 'updateOrderStatus']);
        Route::post('/orderItemsStatus', [VendorController::class, 'orderItemsStatus']);
        Route::post('/updateOrderItems', [VendorController::class, 'updateOrderItems']);
        Route::post('/updateOrderStatusIfSortingByClient', [VendorController::class, 'updateOrderStatusIfSortingByClient']);

        Route::post('/updateOrderByVendor', [OrderController::class, 'updateOrderByVendor']);

        Route::get('/getMenuItems', [VendorController::class, 'getMenuItems']);
        Route::post('/updateItemStatus', [VendorController::class, 'updateItemStatus']);


        Route::get('/getNotifcations', [VendorController::class, 'getNotifcations']);
        Route::get('/getNotifcationBadge', [VendorController::class, 'getNotifcationBadge']);
        Route::post('/markasReadNotifcation', [VendorController::class, 'markasReadNotifcation']);
        Route::post('/clearAllNotifications', [VendorController::class, 'clearAllNotifications']);
    });
});

// whatsapp bot
// 1)
Route::prefix('bot')
    ->middleware('verifyWhatsappBotRequest')
    ->group(function () {
        Route::post('user', [WhatsappBotController::class, 'createUserFromBot']);
        Route::post('location', [WhatsappBotController::class, 'saveUserLocationFromBot']);
        Route::post('placeOrder', [WhatsappBotController::class, 'createOrderFromBot']);
        Route::get('getOrderDetails', [WhatsappBotController::class, 'getOrderDetails']);
        Route::get('getStatusOrder', [WhatsappBotController::class, 'getStatusOrder']);
        Route::post('updateOrderInstructions', [WhatsappBotController::class, 'updateOrderInstructions']);
    });

// 2)
Route::prefix('bot')
    ->middleware(['verifyWhatsappBotRequest', 'resolveWhatsappUser'])
    ->group(function () {
        Route::get('tickets', [TicketController::class, 'index']);
        Route::get('tickets/{ticket}    ', [TicketController::class, 'show']);
        Route::post('tickets', [TicketController::class, 'store']);
        Route::get('all-issue-categories', [IssueCategoryController::class, 'index']);
    });


//setting api testing message
Route::post('/send-sms', [SmsController::class, 'sendSms']);
Route::post('/send-whatsapp-message/{phone}', [WhatsappBotController::class, 'sendWhatsappMessage']);

//external auth login
Route::post('/ext/login', [ExternalAuthController::class, 'issueToken']); // إصدار التوكن (Login)


// Ai Agent
Route::middleware('externalAuth')->group(function () {
    Route::put('/ext/orders/{order}', [ExternalOrderController::class, 'update']);
    Route::patch('/ext/orders/{order}', [ExternalOrderController::class, 'update']);
    Route::get('/ext/orders/{order?}', [ExternalOrderController::class, 'show']);
    Route::get('/ext/orders-by-date-range', [ExternalOrderController::class, 'getOrdersByDateRange']);
    // Vendor Slots API Routes
    Route::get('/vendors/{vendor}/get-pickup-slots', [AgentVendorTimeSlotsController::class, 'pickup']);
    Route::get('/vendors/{vendor}/get-delivery-slots', [AgentVendorTimeSlotsController::class, 'delivery']);
});

Route::post('/integrations/tokens', [IntegrationTokenController::class, 'store']);
Route::middleware('verify.integration')->group(function () {
    Route::post('/webhooks/rides/fazaa/update/{id}', [WebhookController::class, 'updateRide'])
        ->defaults('provider', 'fazaa');
    Route::post('/webhooks/rides/fazaa/update', [WebhookController::class, 'updateRide'])
        ->defaults('provider', 'fazaa');
});


Route::post('/update-order-status/leajlak', [WebhookController::class, 'handleLeajlakWebhook']);
Route::post('/live-location/leajlak', [WebhookController::class, 'liveLocationLeajlak']);

// Leajlak API endpoints
Route::get('/leajlak/order/{dspOrderId}/status', [WebhookController::class, 'getLeajlakOrderStatus']);
Route::post('/leajlak/order/{dspOrderId}/sync', [WebhookController::class, 'syncLeajlakOrderStatus']);

// Service Fee Settings API
Route::get('/service-fee-settings', [ServiceFeeSettingsController::class, 'getSettings']);
Route::post('/service-fee-settings/calculate', [ServiceFeeSettingsController::class, 'calculatePreview']);


Route::prefix('b2b')->group(function () {

    // Public routes (no authentication required)
    Route::post('/register', [\App\Http\Controllers\API\B2b\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\API\B2b\AuthController::class, 'login']);

    // Protected routes (require B2B authentication)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth endpoints
        Route::post('/logout', [\App\Http\Controllers\API\B2b\AuthController::class, 'logout']);
        Route::post('/logout-all', [\App\Http\Controllers\API\B2b\AuthController::class, 'logoutAll']);
        Route::get('/profile', [\App\Http\Controllers\API\B2b\AuthController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\API\B2b\AuthController::class, 'updateProfile']);
        Route::post('/change-password', [\App\Http\Controllers\API\B2b\AuthController::class, 'changePassword']);

        Route::apiResource('addresses', \App\Http\Controllers\API\B2b\AddressController::class);

        // Items endpoints
        Route::get('/items', [\App\Http\Controllers\API\B2b\ItemController::class, 'index']);
        Route::get('/items/{id}', [\App\Http\Controllers\API\B2b\ItemController::class, 'show']);
        Route::get('/items/service/{serviceId}', [\App\Http\Controllers\API\B2b\ItemController::class, 'byService']);
        Route::get('/services', [\App\Http\Controllers\API\B2b\ItemController::class, 'services']);
        Route::get('/pricing-summary', [\App\Http\Controllers\API\B2b\ItemController::class, 'pricingSummary']);
        Route::post('/place-order-with-items', [\App\Http\Controllers\API\B2b\ItemController::class, 'placeOrderWithItems']);
        Route::post('/place-order', [\App\Http\Controllers\API\B2b\ItemController::class, 'placeOrder']);

        Route::get('/orders', [\App\Http\Controllers\API\B2b\OrderController::class, 'index']);
        Route::get('/orders/{id}/show', [\App\Http\Controllers\API\B2b\OrderController::class, 'show']);

    });
});

Route::prefix('partner')->middleware(
    [
        \App\Http\Middleware\ValidatePartner::class,
        'throttle:60,1'
    ])->group(function () {

    // Items endpoints
    Route::get('/items', [\App\Http\Controllers\API\Partner\ItemController::class, 'index']);
    Route::get('/items/{id}', [\App\Http\Controllers\API\Partner\ItemController::class, 'show']);
    Route::get('/items/service/{serviceId}', [\App\Http\Controllers\API\Partner\ItemController::class, 'byService']);
    Route::get('/services', [\App\Http\Controllers\API\Partner\ItemController::class, 'services']);
    Route::get('add-ons', [AddOnController::class, 'index']);

    Route::post('/place-order-with-items', [\App\Http\Controllers\API\Partner\ItemController::class, 'placeOrderWithItems']);

    Route::get('/orders', [\App\Http\Controllers\API\Partner\OrderController::class, 'index']);
    Route::get('/orders/{id}/show', [\App\Http\Controllers\API\Partner\OrderController::class, 'show']);
    Route::post('/orders/rate', [\App\Http\Controllers\API\Partner\OrderController::class, 'rate']);

    Route::apiResource('webhooks', \App\Http\Controllers\API\Partner\PartnerWebhookController::class);

    Route::get('/getPickupTimeslots', [\App\Http\Controllers\API\Partner\PartnerController::class, 'pickup']);
    Route::get('/getDropoffTimeslots', [\App\Http\Controllers\API\Partner\PartnerController::class, 'delivery']);

    Route::get('/service-fee-settings', [ServiceFeeSettingsController::class, 'calculatePreviewForPartner']);

    Route::post('/apply-promo-code', [\App\Http\Controllers\API\Partner\PartnerController::class, 'applyPromoCode']);

    Route::get('/check-service-availability', [\App\Http\Controllers\API\Partner\PartnerController::class, 'checkServiceAvailability']);

});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
});
