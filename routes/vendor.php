<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


use App\Livewire\Partner\Login as VendorLogin;
use App\Livewire\Partner\Dashboard as VendorDashboard;

use App\Livewire\Partner\Order\Show as ShowOrders;
use App\Livewire\Partner\Order\Details as OrderDetails;
// use App\Livewire\Partner\Order\Edit as EditOrder;
use App\Livewire\Partner\Account;
use App\Livewire\Partner\Invite\Index as Invite;
use App\Livewire\Partner\BasketRequests;

use App\Livewire\Partner\Report;
use App\Livewire\Partner\ForgotPassword;
use App\Livewire\Partner\Subscription;

Route::group(['prefix' => 'partner','middleware'=>'guest'], function() {
    Route::get('login', VendorLogin::class)->name('partner.login');
    Route::get('forgot-password', ForgotPassword::class)->name('partner.forgot-password');
    
    Route::redirect('', 'partner/dashboard');
    //Vendor Middleware
    Route::group(['middleware' => 'VendorAuth'], function() {

        // Route::get('logout', [VendorController::class,'logout']);
        Route::get('dashboard', VendorDashboard::class)->name('partner.dashboard');
        Route::get('/clear-notifications', [AdminController::class,'clearVendorNotification']);

        Route::get('orders', ShowOrders::class)->name('partner.orders');
        Route::get('order-details/{id}', OrderDetails::class)->name('partner.order-details');
        Route::get('requests', BasketRequests::class)->name('partner.requests');

        Route::get('logout', [AdminController::class,'partnerLogout'])->name('partner.logout');

        // Route::get('reports', ShowOrders::class)->name('partner.reports');
        Route::get('reports', Report::class)->name('partner.reports');
        Route::get('account', Account::class)->name('partner.account');

    });
});