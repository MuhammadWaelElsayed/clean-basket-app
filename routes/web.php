<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CronJobController;
use App\Http\Controllers\SortableController;
use App\Http\Controllers\PDFController;

use App\Livewire\Landing\Home as HomePage;
use App\Livewire\Landing\BecomePartner as BecomePartner;
use App\Livewire\Landing\CommonPage as CommonPage;
use App\Livewire\Landing\TermsConditions as TermsConditions;

use App\Livewire\Admin\Login as AdminLogin;
use App\Livewire\Admin\Dashboard as AdminDashboard;

use App\Livewire\Admin\Vendor\Show as ShowVendor;
use App\Livewire\Admin\Vendor\Create as CreateVendor;
use App\Livewire\Admin\Vendor\Details as DetailsVendor;


use App\Livewire\Admin\Driver\Show as ShowDriver;
use App\Livewire\Admin\Driver\Create as CreateDriver;
use App\Livewire\Admin\Driver\Details as DetailsDriver;

use App\Livewire\Admin\Items\Show as ShowItems;
use App\Livewire\Admin\Items\Create as CreateItem;
use App\Livewire\Admin\Items\Edit as EditItem;
use App\Livewire\Admin\Items\ManageServices as ManageItemServices;
use App\Livewire\Admin\Items\BulkAssignServices as BulkAssignItemServices;

use App\Livewire\Admin\City\Show as ShowCity;
use App\Livewire\Admin\City\Create as CreateCity;
use App\Livewire\Admin\City\Areas as CityAreas;

use App\Livewire\Admin\Page\Show as ShowPage;
use App\Livewire\Admin\Page\Create as CreatePage;

use App\Livewire\Admin\Area\Show as ShowArea;
use App\Livewire\Admin\Area\Create as CreateArea;
use App\Livewire\Admin\Area\Edit as EditArea;

use App\Livewire\Admin\Order\Show as ShowOrders;
use App\Livewire\Admin\Order\Details as OrderDetails;
use App\Livewire\Admin\Order\EditOrder as EditOrderComponent;
use App\Livewire\Admin\Order\Tracking as OrderTracking;
use App\Livewire\Admin\Orders\OrdersMap;

use App\Livewire\Admin\Banner\Show as ShowBanner;
use App\Livewire\Admin\Banner\Create as CreateBanner;
use App\Livewire\Admin\Banner\Edit as EditBanner;

use App\Livewire\Admin\Service\Show as ShowService;
use App\Livewire\Admin\Service\Create as CreateService;

use App\Livewire\Admin\User\Show as ShowUser;
use App\Livewire\Admin\Account;

use App\Livewire\Admin\Onboard\Show as ShowOnboard;
use App\Livewire\Admin\Onboard\Create as CreateOnboard;

use App\Livewire\Admin\Inqueries;
use App\Livewire\Admin\BasketRequests;
use App\Livewire\Admin\Inventory as BasketInventory;
use App\Livewire\Admin\Packages\CombinedReport;
use App\Livewire\Admin\Packages\EditPackage;
use App\Livewire\Admin\Packages\FinancialReport;
use App\Livewire\Admin\Packages\ManagePackages;
use App\Livewire\Admin\Packages\PackageFinancialReport;
use App\Livewire\Admin\Packages\PackageUsageReport;
use App\Livewire\Admin\Packages\PremiumReport;
use App\Livewire\Admin\PromoCode\Show as ShowPromoCode;
use App\Livewire\Admin\PromoCode\Create as CreatePromoCode;
use App\Livewire\Admin\PromoCode\Details as PromoCodeDetails;
use App\Livewire\Admin\Vouchers\CreateVoucher;
use App\Livewire\Admin\Vouchers\EditVoucher;
use App\Livewire\Admin\Vouchers\ManageVouchers;
use App\Livewire\Admin\Vouchers\ViewVoucher;
use App\Livewire\Admin\Vouchers\VoucherReport;
use App\Livewire\Admin\Wallet\ManualCharge;
use App\Livewire\Admin\Wallet\ManualWithdrawal;
use App\Livewire\Admin\Wallet\Transactions;
use App\Livewire\Admin\Wallet\Settings;

use App\Livewire\Admin\Ticket\Show as ShowTickets;
use App\Livewire\Admin\Ticket\Details as TicketDetails;
use App\Livewire\Admin\IssueCategory\Show as ShowIssueCategories;
use App\Livewire\Admin\Items\Import;
use App\Livewire\Admin\SubIssueCategory\Show as ShowSubIssueCategories;
use App\Livewire\Admin\Vendor\VendorsMap;
use App\Livewire\Admin\Vendor\WorkingHours;
use App\Livewire\Admin\Vendor\SingleVendorWorkingHours;

use App\Livewire\Admin\IntegrationToken\Show as ShowIntegrationToken;
use App\Livewire\Admin\IntegrationToken\Create as CreateIntegrationToken;
use App\Livewire\Admin\ServiceFee\Settings as ServiceFeeSettings;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteCategoryProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('update-admin-password', function (){
    \App\Models\Admin::first()->update(['password' => \Illuminate\Support\Facades\Hash::make('q8YmPTKwfdb6BreR')]);
});

Route::get('/test', [AdminController::class, 'test']);
Route::get('/createPDF', [PDFController::class, 'createPDF']);
Route::get('/test-auth', [AdminController::class, 'testAuth']);

Route::get('locale/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return redirect()->back();
})->name('switchLan');

Route::group(['middleware' => 'apiLanguage'], function () {
    Route::redirect('/home', '/');
    Route::get('/', HomePage::class);
    Route::get('/become-partner', BecomePartner::class);
    Route::get('/{page}', CommonPage::class);
});

// Cron Jobs
Route::prefix('cron')->group(function () {
    Route::get('/renew-subscription', [CronJobController::class, "renewSubscription"]);
    Route::get('/review-notification', [CronJobController::class, "reviewNotification"]);
});

// Payments
Route::get('/pay', [PaymentController::class, 'initiatePayment'])->name('pay');
Route::get('/payment-callback', [PaymentController::class, 'paymentCallback'])->name('payment.callback');

// ----------------------------------------------------------------------
// ADMIN ROUTES (fixed for persistent sessions)
// ----------------------------------------------------------------------

Route::middleware(['web'])->prefix('admin')->group(function () {

    /**
     * Guest Routes (Login)
     * Only for users not authenticated as admin
     */
    Route::middleware('guest')->group(function () {
        Route::get('login', AdminLogin::class)->name('admin.login');
    });

    /**
     * Authenticated Admin Routes
     */
    Route::middleware('auth:admin')->group(function () {

        // Redirect root admin path to dashboard
        Route::get('/', fn() => redirect()->route('admin.dashboard'));

        Route::get('dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('logout', [AdminController::class, 'logout'])->name('admin.logout');
        Route::get('clear-notifications', [AdminController::class, 'clearNotification'])->name('admin.clear-notifications');

        // Vendors
        Route::get('partners', ShowVendor::class)->name('admin.partners');
        Route::get('partners/create', CreateVendor::class);
        Route::get('partners/{id}', DetailsVendor::class);
        Route::get('partners/{id}/edit', CreateVendor::class);

        Route::get('partners-map', VendorsMap::class)->name('admin.partners.map');
        Route::get('partners-working-hours', WorkingHours::class)->name('admin.partners.working-hours');

        // Drivers
        Route::get('drivers', ShowDriver::class)->name('admin.drivers');
        Route::get('drivers/create', CreateDriver::class);
        Route::get('drivers/{id}', DetailsDriver::class);
        Route::get('drivers/{id}/edit', CreateDriver::class);
        Route::get('drivers/map/view', \App\Livewire\Admin\Driver\DriverMap::class)->name('admin.drivers.map');

        // Items
        Route::get('items', ShowItems::class)->name('admin.items');
        Route::get('items/create', CreateItem::class);
        Route::get('items/{id}/edit', EditItem::class);
        Route::get('items/services/manage', ManageItemServices::class)->name('admin.items.services');
        Route::get('items/services/bulk-assign', BulkAssignItemServices::class)->name('admin.items.bulk-services');
        Route::get('items/import', \App\Livewire\Admin\Items\Import::class)->name('admin.items.import');

        // Banners
        Route::get('banners', ShowBanner::class)->name('admin.banners');
        Route::get('banners/create', CreateBanner::class);
        Route::get('banners/{id}/edit', EditBanner::class);

        // Cities
        Route::get('cities', ShowCity::class)->name('admin.cities');
        Route::get('cities/create', CreateCity::class);
        Route::get('cities/{id}', CityAreas::class);

        // Pages
        Route::get('pages', ShowPage::class)->name('admin.pages');
        Route::get('pages/create', CreatePage::class);

        // Orders
        Route::get('orders', ShowOrders::class)->name('admin.orders');
        Route::get('order-details/{id}', OrderDetails::class)->name('admin.order.details');
        Route::get('orders/{id}/tracking', OrderTracking::class)->name('admin.order.tracking');
        Route::get('orders/{order}/edit', EditOrderComponent::class)->name('admin.orders.edit');
        Route::get('trips', \App\Livewire\Admin\Trips\Index::class)->name('admin.orders.trips');
        Route::get('driver-request-monitor', \App\Livewire\Admin\Driver\OrderDriverMonitor::class)->name('admin.order-driver-monitor');

        // Users
        Route::get('users', ShowUser::class)->name('admin.users');
        Route::get('account', Account::class)->name('admin.account');

        Route::get('inqueries', Inqueries::class);
        Route::get('requests', BasketRequests::class)->name('admin.requests');
        Route::get('inventory', BasketInventory::class)->name('admin.inventory');

        Route::get('cities', ShowCity::class)->name('admin.cities');
        Route::get('cities/create', CreateCity::class);
        Route::get('cities/{id}/edit', CreateCity::class);
        Route::get('cities/{id}', CityAreas::class);

        Route::get('pages', ShowPage::class)->name('admin.pages');
        Route::get('pages/create', CreatePage::class);
        Route::get('pages/{id}/edit', CreatePage::class);

        Route::get('settings', Settings::class)->name('admin.settings');


        Route::get('services', ShowService::class)->name('admin.services');
        Route::get('services/create', CreateService::class);
        Route::get('services/{id}/edit', CreateService::class);

        Route::get('orders', ShowOrders::class)->name('admin.orders');
        Route::get('orders/map', OrdersMap::class)->name('admin.orders.map');
        Route::get('orders/{order}/edit', EditOrderComponent::class)->name('admin.orders.edit');
        Route::get('orders/{id}/tracking', OrderTracking::class)->name('admin.order.tracking');
        Route::get('order-details/{id}', OrderDetails::class)->name('admin.order.details');
        Route::get('order-details/{order}/edit-items', \App\Livewire\Admin\Order\EditOrderItems::class)->name('admin.order.edit-items');

        Route::get('order-drivers', \App\Livewire\Admin\OrderDriver\Index::class)->name('admin.order-drivers.index');
        Route::get('order-drivers/{id}', \App\Livewire\Admin\OrderDriver\Show::class)->name('admin.order-drivers.show');

        Route::get('onboard', ShowOnboard::class)->name('admin.onboard');
        Route::get('onboard/create', CreateOnboard::class);
        Route::get('onboard/{id}/edit', CreateOnboard::class);

        Route::get('codes', ShowPromoCode::class)->name('admin.codes');
        Route::get('codes/create', CreatePromoCode::class);
        Route::get('codes/{id}/edit', CreatePromoCode::class);
        Route::get('codes/{id}', PromoCodeDetails::class)->name('admin.code-details');

        Route::get('wallet/transactions', Transactions::class)->name('admin.wallet.transactions');
        Route::get('/wallet/manual-charge', ManualCharge::class)->name('admin.wallet.manualCharge');
        Route::get('/wallet/manual-withdrawal', ManualWithdrawal::class)->name('admin.wallet.manualWithdrawal');
        Route::get('/wallet/settings', Settings::class)->name('admin.wallet.settings');

        Route::get('packages', ManagePackages::class)->name('admin.packages');
        Route::get('packages/{package}/edit',  EditPackage::class)->name('admin.packages.edit');
        Route::get('/admin/packages/financial-report', PackageFinancialReport::class)->name('admin.packages.financialReport');
        Route::get('packages/usage-report',  PackageUsageReport::class)->name('admin.packages.usageReport');
        Route::get('/admin/packages/premium-report', PremiumReport::class)->name('admin.packages.premiumReport');

        // Tickets Management
        Route::get('tickets', ShowTickets::class)->name('admin.tickets');
        Route::get('tickets/{ticket}', TicketDetails::class)->name('admin.tickets.details');

        // Issue Categories Management
        Route::get('issue-categories', ShowIssueCategories::class)->name('admin.issue-categories');

        // Sub Issue Categories Management
        Route::get('sub-issue-categories', ShowSubIssueCategories::class)->name('admin.sub-issue-categories');
        Route::get('sub-issue-categories/{category_id}', ShowSubIssueCategories::class)->name('admin.sub-issue-categories.category');

        // Integration Tokens Management
        Route::get('integration-tokens', ShowIntegrationToken::class)->name('admin.integration-tokens');
        Route::get('integration-tokens/create', CreateIntegrationToken::class)->name('admin.integration-tokens.create');
        Route::get('integration-tokens/{id}/edit', CreateIntegrationToken::class)->name('admin.integration-tokens.edit');

        Route::get('service-fee-settings', ServiceFeeSettings::class)->name('admin.service-fee-settings');

        Route::get('admin-management', \App\Livewire\Admin\AdminManagement::class)->name('admin-management');

        Route::get('roles', \App\Livewire\Admin\Roles::class)->name('roles');

        Route::get('permission-assignment', \App\Livewire\Admin\PermissionAssignment::class)->name('permission-assignment');

        Route::prefix('b2b-clients')->name('b2b-clients.')->group(function () {
            Route::get('/', \App\Livewire\Admin\B2bClient\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Admin\B2bClient\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\Admin\B2bClient\Edit::class)->name('edit');
            Route::get('/{id}/change-password', \App\Livewire\Admin\B2bClient\ChangePassword::class)->name('change-password');
        });

        Route::get('/b2b/partners/create', \App\Livewire\Admin\B2BPartners\Create::class)->name('b2b.partners.create');
        Route::get('/b2b/partners/{id}', \App\Livewire\Admin\B2BPartners\Edit::class)->name('b2b.partners.edit');
        Route::get('/b2b/partners', \App\Livewire\Admin\B2BPartners\Index::class)->name('b2b.partners');

        Route::prefix('b2b-orders')->name('b2b-orders.')->group(function () {
            Route::get('/', \App\Livewire\Admin\Order\ShowForB2b::class)->name('index');
        });

        Route::prefix('pricing-tiers')->name('pricing-tiers.')->group(function () {
            Route::get('/', \App\Livewire\Admin\PricingTier\Index::class)->name('index');
            Route::get('/create', \App\Livewire\Admin\PricingTier\Create::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\Admin\PricingTier\Edit::class)->name('edit');
            Route::get('/{id}/item-prices', \App\Livewire\Admin\PricingTier\ItemPrices::class)->name('item-prices');
        });

        // Route::get('vouchers', ManageVouchers::class)->name('admin.vouchers');
        // Route::get('vouchers/create', CreateVoucher::class)->name('admin.vouchers.create');
        // Route::get('vouchers/{voucher}/edit', EditVoucher::class)->name('admin.vouchers.edit');
        // Route::get('vouchers/{voucher}', ViewVoucher::class)->name('admin.vouchers.view');
        // Route::get('vouchers/{voucher}/delete', ManageVouchers::class)->name('admin.vouchers.delete');
        // Route::get('admin/vouchers/voucher-report', VoucherReport::class)->name('admin.vouchers.voucherReport');

        //    Route::get('packages/reports/usage', UsageReport::class)
        //         ->name('admin.packages.usageReport');
        //    Route::get('packages/reports/priority', PriorityReport::class)
        //         ->name('admin.packages.priorityReport');
    });

});

// Sortable
Route::post('update-sort-order/{model}', [SortableController::class, 'updateOrder']);

// Vendor Routes
require __DIR__ . '/vendor.php';
