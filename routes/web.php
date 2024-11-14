<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\HomeController as AdminHome;
use App\Http\Controllers\Admin\StoreController as AdminStore;
use App\Http\Controllers\Admin\BusinessTypeController as AdminBusinessType;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\StoreCreatePasswordController;

use App\Http\Controllers\StoreController as StoreProfile;
use App\Http\Controllers\StoreBillingController as StoreBilling;
use App\Http\Controllers\ServiceCategoryController as ServiceCategory;
use App\Http\Controllers\AdditionaltaxController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\RebookController;
use App\Http\Controllers\PaymentTypeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TherapistController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\InventoryController;

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

Route::get('/', function () {
    return view('auth.login');
});

Route::get('artisan-call', function () {
\Artisan::call('config:clear');
    \Artisan::call('config:cache');

    dd('Configuration cache cleared!');
});

/* php artisan config:clear */
// 






Auth::routes();

Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post'); 
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::get('create-password/{token}', [ForgotPasswordController::class, 'showCreatePasswordForm'])->name('create.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');
Route::post('create-password', [ForgotPasswordController::class, 'submitCreatePasswordForm'])->name('create.password.post');



// Store Create Password
Route::get('store-create-password/{token}', [StoreCreatePasswordController::class, 'get'])->name('store.create.password.get');
Route::post('store-new-password-save', [StoreCreatePasswordController::class, 'store'])->name('store.create.password.post');
// Forgot password routes
// Route::get('forget-password', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('forget.password.get');
// Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post'); 
// Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
// Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

// User Routes
Route::group(['middleware' => ['auth', 'store']], function () {

    // Dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/payment-history', [HomeController::class, 'paymentHistory'])->name('paymentHistory');
    Route::get('/customer-bar-chart', [HomeController::class, 'customerBarChart'])->name('customerBarChart');
    Route::get('/sale-line-chart', [HomeController::class, 'salesLineChart'])->name('salesLineChart');
    Route::get('/service-pie-chart', [HomeController::class, 'servicePieChart'])->name('servicePieChart');
    Route::get('/payment-datatable-list', [HomeController::class, 'paymentDatatable'])->name('paymentDatatable');
    Route::get('/dashboard-filter-list', [HomeController::class, 'dashboardFilter'])->name('dashboardFilter');
    
    
    // User profile routes
    Route::resource('profile', ProfileController::class);
    Route::post('update-password', [ProfileController::class, 'updatePassword']);
    Route::post('update-user-photo', [ProfileController::class, 'updateUserPhoto']);

    // Store Routes
    Route::resource('store-profile', StoreProfile::class);
    Route::post('store-profile/update-logo', [StoreProfile::class, 'updateLogo']);
    Route::post('store-profile/delete-logo', [StoreProfile::class, 'deleteLogo']);
    Route::resource('store-billings', StoreBilling::class);
    Route::post('store-billings/update-gst', [StoreBilling::class, 'updateGst']);

    Route::resource('rooms', RoomController::class);
    Route::get('rooms/get/all', [RoomController::class, 'getAll'])->name('rooms-get-all');
    // Additional tax Routes
    Route::resource('additional-tax', AdditionaltaxController::class);

    // Services Routes
    Route::resource('services', ServiceController::class);
    Route::post('services/restore/{id}', [ServiceController::class, 'restore']);
    Route::post('services/import/data', [ServiceController::class, 'import'])->name('services.import');
    Route::post('services/get-details', [ServiceController::class, 'getDetails'])->name('get_details');
    Route::get('service-category/autocomplete', [ServiceCategory::class, 'autocomplete'])->name('service-category.autocomplete');
    
    
    // Packages Routes
    Route::resource('packages', PackageController::class);
    Route::post('packages/update-status', [PackageController::class, 'updateStatus']);
    Route::post('packages/restore/{id}', [PackageController::class, 'restore']);
    Route::post('packages/get-packages-details', [PackageController::class, 'getPackageDetails'])->name('getPackageDetails');
    //get all Memberships
    Route::get('/get-all-memberships', [MembershipController::class, 'getAllMemberships'])->name('get_all_memberships');
    Route::post('/get-memberships-details', [MembershipController::class, 'getMembershipsDetails'])->name('get_membership_details');
    // Customer Routes
    Route::resource('customers', CustomerController::class);
    Route::post('customers/restore/{id}', [CustomerController::class, 'restore']);
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customer.import');
    Route::post('customers/hard-delete/{id}', [CustomerController::class, 'hardDelete']);
    Route::get('customers/autocomplete/details', [CustomerController::class, 'autocomplete'])->name('customers.autocomplete');
    Route::post('customers/in-store-credit', [CustomerController::class, 'updateInstoreCredit'])->name('customers.updateInstoreCredit');
    Route::get('customers/list/in-store-credit', [CustomerController::class, 'listInstoreCredit'])->name('customers.listInstoreCredit');
    Route::get('customers/list/membership-in-store-credit', [CustomerController::class, 'listMembershipInstoreCredit'])->name('customers.listMembershipInstoreCredit');
    Route::get('customers/list/customer-dues', [CustomerController::class, 'listCustomerDues'])->name('customers.listCustomerDues');
    Route::get('in-store-credit/list', [CustomerController::class, 'instoreCreditDetailedView'])->name('customers.instoreCreditDetailedView');
    Route::get('customer-service/list', [CustomerController::class, 'listCustomerServices'])->name('customers.listCustomerServices');
    Route::get('customer-review-book', [CustomerController::class, 'reviewAboutCustomer'])->name('customers.reviewAboutCustomer');
    Route::post('/submit-customer-membership', [CustomerController::class, 'SubmitCustomerMemership'])->name('customers.SubmitCustomerMemership');
    Route::post('/store-comment', [CustomerController::class, 'storeComment'])->name('customers.storeComment');
    Route::get('/edit-comment', [CustomerController::class, 'editComment'])->name('customers.editComment');
    Route::delete('/delete-comment', [CustomerController::class, 'deleteComment'])->name('customers.deleteComment');
    Route::put('/update-comment', [CustomerController::class, 'updateComment'])->name('customers.updateComment');
    Route::post('/store-call-log', [CustomerController::class, 'storeCallLog'])->name('customers.storeCallLog');
    Route::get('/edit-call-log', [CustomerController::class, 'editlogs'])->name('customers.editlogs');
    Route::delete('/delete-call-log', [CustomerController::class, 'deleteCallLog'])->name('customers.deleteCallLog');
    Route::put('/update-call-log', [CustomerController::class, 'updateCallLog'])->name('customers.updateCallLog');
    Route::get('/create-calllog', [CustomerController::class, 'createCallLog'])->name('customers.createCallLog');
    Route::get('/list-calllog/{id}', [CustomerController::class, 'getCustomerCallLog'])->name('customers.getCustomerCallLog');
    Route::get('/customer-status-filter', [CustomerController::class, 'customerStatusFilter'])->name('customerStatusFilter');
    
    // User Routes
    Route::resource('users', UserController::class);
    Route::post('users/update-status', [UserController::class, 'updateStatus']);
    Route::post('users/restore/{id}', [UserController::class, 'restore']);

    // Billing Routes
    Route::middleware([isStoreCompleted::class])->group(function() {
        Route::resource('billings', BillingController::class);
        Route::get('billings/invoice/{id}', [BillingController::class, 'invoice'])->name('getInvoiceData');
        Route::post('billings/invoice/get-data', [BillingController::class, 'getInvoiceData']);
        Route::post('billings/manage-discount', [BillingController::class, 'manageDiscount']);
        Route::post('billings/store-payment', [BillingController::class, 'storePayment']);
        Route::post('billings/in-store-payment', [BillingController::class, 'inStoreCreditPayment'])->name('inStoreCreditPayment');
        Route::post('billings/remove-in-store-payment', [BillingController::class, 'removeInStoreCreditPayment'])->name('removeInStoreCreditPayment');
        Route::post('billings/cancel-bill', [BillingController::class, 'cancelBillPayment'])->name('billings.cancelBillPayment');
        Route::get('refund-bill', [BillingController::class, 'refundBill'])->name('billings.refundBill');
        Route::post('refund-bill-amount', [BillingController::class, 'refundBillPayment'])->name('billings.refundBillPayment');
        Route::get('schedule-service-list', [BillingController::class, 'scheduleServiceLists'])->name('billings.scheduleServiceLists');
        Route::get('cancel-service-list', [BillingController::class, 'cancelServiceLists'])->name('billings.getBillItemDetails');
        
        Route::resource('rebook', RebookController::class);

        Route::get('/bill-overview', [BillingController::class, 'billOverview'])->name('billOverview');
        Route::get('/payment-type-filter', [BillingController::class, 'paymentTypeFilter'])->name('paymentTypeFilter');
        Route::get('/sales-payment-type-filter', [BillingController::class, 'salesPaymentTypeFilter'])->name('salesPaymentTypeFilter');
        Route::get('/search-payment', [BillingController::class,'searchByPaymentType'])->name('searchPayment');
        Route::get('/cashbookList', [BillingController::class,'cashbookList'])->name('cashbookList');
        Route::get('/pettyCashbookList', [BillingController::class,'pettyCashbookList'])->name('pettyCashbookList');
        
        //Schedule Routes
        Route::resource('schedules', ScheduleController::class);
        Route::get('schedules/calendar/{mode}', [ScheduleController::class, 'index']);
        Route::get('schedules/get/calendar/bookings', [ScheduleController::class, 'bookings']); 
        Route::post('schedules/re-schedule', [ScheduleController::class, 'reSchedule']);
        Route::post('schedules/check-in', [ScheduleController::class, 'updateCheckInStatus'])->name('scheduler.updateCheckInStatus');
        Route::get('customer-schedule/list', [ScheduleController::class, 'listCustomerSchedules'])->name('scheduler.listCustomerSchedules');
        Route::get('schedule-filter', [ScheduleController::class, 'scheduleFilter'])->name('scheduleFilter');
        $schedule = 'schedules';
        Route::get($schedule.'/lists', [ScheduleController::class, 'lists']);
        Route::post($schedule.'/save-booking', [ScheduleController::class, 'store']);
        // Route::post($schedule.'/update/{id}', [ScheduleController::class, 'updateSchedule']);
        Route::get($schedule.'/get-services/{id}', [ServiceController::class, 'getServices'])->name('getPackageServices');
        //Schedule Routes
        Route::resource('therapists', TherapistController::class);

        //Attendance Routes
        Route::resource('attendance', AttendanceController::class);
        Route::get('attendance/edit/markings', [AttendanceController::class, 'editMarking']);
         
        $billing = 'billings';       
        Route::put($billing . '/invoice/update/{id}', [BillingController::class, 'updateInvoice']);
        Route::get($billing .'/invoice-data/generate-pdf/{id}', [BillingController::class, 'generatePDF']);
        Route::get($billing .'/invoice-data/print/{billing}', [BillingController::class, 'printPDF'])->name('printPdf');
        Route::post($billing . '/add-new-customer', [BillingController::class, 'storeCustomer']);
        Route::post($billing . '/cancel/{billing}', [BillingController::class, 'cancelBill'])->name('cancelBill');
        Route::get('/cancel-bill/{billing}', [BillingController::class, 'cancelBillInvoice'])->name('cancelBillInvoice');
    });

    // Old 
    // Route::get('users/lists', [UserController::class, 'lists']);
    // Route::post('users/unique', [UserController::class, 'isUnique']);
    
    $customer = 'customers';
    // Route::get($customer . '/lists', [CustomerController::class, 'lists']);
    // Route::get($customer . '/{id}/view', [CustomerController::class, 'show']);
    // Route::get($customer . '/view-details/{id}', [CustomerController::class, 'show']);
    Route::get($customer . '/billing-report/{id}', [CustomerController::class, 'billReport']);
    Route::get($customer . '/cancelled-billing-report/{id}', [CustomerController::class, 'cancelledBillReport']);
    Route::get($customer . '/create-bill/{id}', [CustomerController::class, 'createBill']);
    Route::post($customer . '/export-report', [CustomerController::class, 'exportReport']);
    Route::get('/get-instore-data', [CustomerController::class, 'getInstoreData'])->name('getInstoreData');
    Route::post('/edit-instore-data', [CustomerController::class, 'editInstoreData'])->name('editInstoreData');
        
    // Packages Routes
    // $packages = 'packages';
    // Route::resource($packages, PackageController::class)->except(['show']);
    // Route::get($packages . '/lists', [PackageController::class, 'lists']);
    // Route::post($packages . '/restore/{id}', [PackageController::class, 'restore']);

    Route::get('change-password', [ProfileController::class, 'index']);
    Route::post('change-password', [ProfileController::class, 'update']);

    // Store Routes
    $store_link = 'store';
    Route::get($store_link . '/billings', [StoreProfile::class, 'billings']);
    Route::get($store_link . '/billing-series', [StoreProfile::class, 'billingSeries']);
    Route::put($store_link . '/update/{id}', [StoreProfile::class, 'update']);
    Route::put($store_link . '/update/billing/{id}', [StoreProfile::class, 'storeBilling']);
    Route::post($store_link . '/update/bill-format/', [StoreProfile::class, 'updateBillFormat']);
    Route::post($store_link . '/theme-settings', [StoreProfile::class, 'themeSettings'])->name('store.themeSettings');
       
    //Staff Routes
    $staff = 'staffs';
    Route::resource($staff, StaffController::class)->except(['show']);
    Route::get($staff.'/lists', [StaffController::class, 'lists']);
    Route::get($staff.'/{id}/manage-document', [StaffController::class, 'manageDocument']);
    Route::post($staff.'/update/user-image', [StaffController::class, 'updateUserImage']);
    Route::post($staff.'/get-document', [StaffController::class, 'getDocument']);
    Route::post($staff.'/upload-id-proof', [StaffController::class, 'uploadIdProofs']);
    Route::post($staff.'/remove-id-proof', [StaffController::class, 'removeIdProofs']);
    Route::post($staff.'/delete-id-proof', [StaffController::class, 'deleteIdProofs']);
    Route::get($staff.'/download-files/{document}', [StaffController::class, 'downloadFile'])->name('download-files');
    Route::post($staff.'/update/document-details', [StaffController::class, 'updateDocumentDetails']);
    Route::post($staff.'/store-document', [StaffController::class, 'storeDocuments']);
    Route::post($staff.'/remove-temp-document', [StaffController::class, 'removeTempDocuments']);
    Route::get('/therapists/lists/{id}', [StaffController::class, 'getTherapist'])->name('getTherapist');
    Route::get('/staff-work-history/{id}', [StaffController::class, 'staffWorkHistory'])->name('staffWorkHistory');
    Route::get('/staff-service-history/{id}', [StaffController::class, 'staffServiceHistory'])->name('staffServiceHistory');
    Route::get('/get-staff-date-range/{id}', [StaffController::class, 'getDateRangeStaffDetails'])->name('getDateRangeStaffDetails');
    
    // Business type Routes
    // $business_type = 'business-types';
    // Route::resource($business_type, AdminBusinessType::class)->except(['show']);
    // Route::get($business_type . '/lists', [AdminBusinessType::class, 'lists']);
            
    // Roles Routes
    // Route::resource('roles', RoleController::class);
             
    // Service category Routes
    $service_category = 'service-category';
    Route::resource($service_category, ServiceCategory::class)->except(['show']);
    Route::get($service_category . '/lists', [ServiceCategory::class, 'lists']);
    
           
    // Country Routes
    $country = 'country';
    Route::resource($country, CountryController::class)->except(['show']);
    Route::get($country . '/lists', [CountryController::class, 'lists']);
           
    // State Routes
    $state = 'states';
    Route::resource($state, StateController::class)->except(['show']);
    Route::get($state . '/lists', [StateController::class, 'lists']);

    // District Routes
    $district = 'districts';
    Route::resource($district, DistrictController::class)->except(['show']);
    Route::get($district . '/lists', [DistrictController::class, 'lists']);

    // Payment Type Routes
    $paymentTypes = 'payment-types';
    Route::resource($paymentTypes, PaymentTypeController::class)->except(['show']);
    Route::get($paymentTypes . '/lists', [PaymentTypeController::class, 'lists']);
    Route::get($paymentTypes . '/select-list', [PaymentTypeController::class, 'lists']);
    Route::post($paymentTypes . '/update', [CustomerController::class, 'update']);

    $link = 'common';
    Route::get($link . '/get-states', [CommonController::class, 'getStates']);    
    Route::get($link . '/get-districts', [CommonController::class, 'getDistricts']);    
    Route::get($link . '/get-all-services', [CommonController::class, 'getAllServices'])->name('get_all_services');    
    Route::post($link . '/get-services', [CommonController::class, 'getServices'])->name('getServices');    
    Route::post($link . '/get-packages', [CommonController::class, 'getPackages']);    
    Route::get($link . '/get-all-packages', [CommonController::class, 'getAllPackages'])->name('get_all_packages');   
    Route::get($link . '/get-districts', [CommonController::class, 'getDistricts']);    
    Route::post($link . '/get-shop-districts', [CommonController::class, 'getShopDistricts']);    
    Route::post($link . '/get-shop-states', [CommonController::class, 'getShopStates']);    
    Route::get($link . '/get-customer-details', [CommonController::class, 'getCustomerDetails'])->name('get_customer_details');   
    Route::post($link . '/get-taxdetails', [CommonController::class, 'calculateTax']);  
    Route::post($link . '/list-service-with-tax', [CommonController::class, 'calculateTaxTable'])->name('list_service_with_tax');  
    Route::post($link . '/get-timezone', [CommonController::class, 'getTimezone'])->name('getTimezone');         
    Route::post($link . '/get-currencies', [CommonController::class, 'getCurrencies'])->name('getCurrencies');    
    Route::post($link . '/get-therapist/{id}', [CommonController::class, 'getTherapist']);    
    Route::post($link . '/billings/get-report-by-date/', [CommonController::class, 'getBillingReports'])->name('get_report_by_date');    
    Route::get($link . '/get-payment-types', [CommonController::class, 'getPaymentTypes']);  
    Route::get($link . '/get-package-services/{id}', [CommonController::class, 'getPackageServiceList'])->name('get_package_services');  
    Route::get($link . '/sent-mail', [CommonController::class, 'sendEmail'])->name('sendEmail');  
    
    // Report Routes 
    $reports = 'reports';
    Route::get($reports . '/sales-report', [ReportController::class, 'salesReport']);
    Route::get($reports . '/daily-report', [ReportController::class, 'dailyReport'])->name('dailyReport');
    Route::post($reports . '/get-sales-chart-data', [ReportController::class, 'getSalesReportChartData']);
    Route::get($reports . '/get-sales-table-data', [ReportController::class, 'getSalesReportTableData']);
    Route::get('/get-sales-table-data-filter', [ReportController::class, 'getSalesReportTableDataFilter'])->name("getSalesReportTableDataFilter");
    Route::post($reports . '/export-report', [ReportController::class, 'exportReport']);
    Route::get($reports . '/export-report', [ReportController::class, 'exportReport']);
    Route::get('report-filter', [ReportController::class, 'reportFilter'])->name('reportFilter');

    // Cashbook Routes 
    $cashbook = 'cashbook';
    Route::resource($cashbook, CashbookController::class)->except(['show']);
    Route::get($cashbook . '/lists', [CashbookController::class, 'lists']);
    Route::post($cashbook . '/withdraw', [CashbookController::class, 'withdraw']);
    Route::get('/get-cash-details', [CashbookController::class, 'getCashDetails'])->name('getCashDetails');

    // Roles Routes
    Route::resource('roles', RoleController::class);
    Route::resource('membership', MembershipController::class);
    // Route::post('role/user-update/{id}', RoleController::class, 'updateByUser');


    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('stocks', StockController::class);
    Route::resource('inventories', InventoryController::class);
    
   
});

// Super Admin Routes
Route::prefix('admin/')->name('admin.')->group(function () {
    Route::group(['middleware' => ['auth', 'admin']], function () {

        // Dashboard
        Route::get('home', [AdminHome::class, 'index'])->name('home');
        
        // Store Routes
        $store_link = 'stores';
        Route::resource($store_link, AdminStore::class)->names([
            'index' => 'stores.index',
            'create' => 'stores.create',
            'store' => 'stores.store',
            'show' => 'stores.show',
            'edit' => 'stores.edit',
            'update' => 'stores.update',
            'destroy' => 'stores.destroy',
        ]);
        // Route::get($store_link . '/lists', [AdminStore::class, 'lists']);
        Route::post($store_link. '/manage-status', [AdminStore::class, 'manageStatus']);       

        // Business Type Routes
        $business_type = 'business-types';
        Route::resource($business_type, AdminBusinessType::class)->except(['show']);
        Route::get($business_type . '/lists', [AdminBusinessType::class, 'lists']);

        // Roles Routes
        Route::resource('roles', RoleController::class);
        Route::resource('membership', MembershipController::class);

        // User
        Route::post('users/unique', [UserController::class, 'isUnique']);
        
        // Notifications
        $notifications = 'notifications';
        Route::resource($notifications, AdminStore::class)->except(['show']);
    });
});

// App Common routes
Route::post('common/is-unique-email', [CommonController::class, 'isUniqueEmail'])->name('isUniqueEmail');
Route::post('common/customer/is-unique-email', [CommonController::class, 'isUniqueCustomerEmail'])->name('customer.uniqueEmail');
Route::post('common/is-unique-store-email', [CommonController::class, 'isUniqueStoreEmail'])->name('isUniqueStoreEmail');
Route::post('common/is-unique-mobile', [CommonController::class, 'isUniqueMobile']);
Route::post('common/get-states-by-country', [CommonController::class, 'getStatesByCountry'])->name('getStatesByCountry');
Route::any('common/get-districts-by-state', [CommonController::class, 'getDistrictsByState'])->name('get_districts_by_state'); 
Route::any('test-mail', [CommonController::class, 'testMail']); 

