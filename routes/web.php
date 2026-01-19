<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OneSignalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Cashier\InvoiceController;
use App\Http\Controllers\Cashier\QuickSaleController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Route::middleware('auth:accountant')->get('/quick-sale/credit-persons',
//     [QuickSaleController::class, 'creditPersons']
// )->name('quick-sale.credit-persons');
// Route::middleware(['auth:accountant'])->post('/quick-sale/submit',
//     [QuickSaleController::class, 'submit']

/*
|--------------------------------------------------------------------------
| راوتات المدير والمستخدم (تحت web guard)
|--------------------------------------------------------------------------
*/
// Route::post('/force-logout', function (\Illuminate\Http\Request $request) {

//     // تسجيل خروج المستخدم من أي guard
//     auth()->logout();

//     // تدمير الجلسة
//     $request->session()->invalidate();
//     $request->session()->regenerateToken();

//     // تحويل لصفحة تسجيل الدخول بدون أي Middleware
//     return redirect('/login');
// })->name('force.logout');


Route::middleware('web')->group(function () {

    require base_path('routes/admin.php');
    require base_path('routes/user.php');
     require base_path('routes/accountant.php');


    /*
    |--------------------------------------------------------------------------
    | صفحة الترحيب المشتركة
    |--------------------------------------------------------------------------
    */
    Route::post('/welcome/continue', function () {
        $user = auth('web')->user(); // ← مهم جدًا

        $user->update(['welcome_shown' => true]);

        return match ($user->role) {
            'admin'      => redirect()->route('admin.dashboard.index'),
            'accountant' => redirect()->route('accountant.dashboard'),
            default      => redirect()->route('user.dashboard.index'),
        };
    })->middleware('auth:web')->name('welcome.continue');

    /*
    |--------------------------------------------------------------------------
    | سجل النشاطات
    |--------------------------------------------------------------------------
    */
    Route::get('/logs', [LogController::class, 'index'])
        ->name('user.logs.index')
        ->middleware('auth:web');

    /*
    |--------------------------------------------------------------------------
    | صفحات عامة
    |--------------------------------------------------------------------------
    */
    Route::view('/no-access', 'errors.no-access')->name('no.access');
    Route::view('/suspended', 'auth.suspended')->name('user.suspended');
    Route::view('/welcome-screen', 'auth.welcome-screen')->name('welcome.screen');

    /*
    |--------------------------------------------------------------------------
    | الصفحة الرئيسية
    |--------------------------------------------------------------------------
    */
    Route::get('/', fn() => view('welcome'));

    /*
    |--------------------------------------------------------------------------
    | مسارات الضيوف (web فقط)
    |--------------------------------------------------------------------------
    */
    Route::middleware('guest:web')->group(function () {

        Route::get('/login', [LoginController::class, 'showLoginForm'])
            ->name('login');

        Route::post('/login', [LoginController::class, 'login'])
            ->name('login.submit');

        Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
            ->name('password.request');

        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('password.email');

        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
            ->name('password.reset');

        Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
            ->name('password.update');
    });

    /*
    |--------------------------------------------------------------------------
    | تسجيل الخروج (web فقط)
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [LoginController::class, 'logout'])
        ->middleware('auth:web')
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | مسارات الاشتراك
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:web')->group(function () {

        Route::get('/subscription/expired', [SubscriptionController::class, 'expired'])
            ->name('subscription.expired');

        Route::get('/subscription/renew', [SubscriptionController::class, 'renew'])
            ->name('subscription.renew');

        Route::post('/subscription/renew', [SubscriptionController::class, 'processRenew'])
            ->name('subscription.processRenew');
    });

    /*
    |--------------------------------------------------------------------------
    | صفحات عامة إضافية
    |--------------------------------------------------------------------------
    */
    Route::view('/products', 'products.index')->name('products.index');
    Route::view('/categories', 'categories.index')->name('categories.index');
    Route::view('/workers', 'workers.index')->name('workers.index');
    Route::view('/salaries', 'salaries.index')->name('salaries.index');
    Route::view('/subscriptions', 'subscriptions.index')->name('subscriptions.index');
});

/*
|--------------------------------------------------------------------------
| مسارات المحاسب (تحت accountant guard فقط)
|--------------------------------------------------------------------------
*/
// Route::prefix('accountant')
//     ->middleware('accountant.auth')
//     ->name('accountant.')
//     ->group(function () {

//         Route::get('/dashboard', [AccountantDashboardController::class, 'index'])
//             ->name('dashboard');

//         Route::get('/no-store', fn() => view('accountant.no-store'))
//             ->name('no-store');

//         Route::prefix('invoices')->name('invoices.')->group(function () {

//             Route::get('/', [AccountantInvoiceController::class, 'index'])
//                 ->name('index');

//             Route::get('/create', [AccountantInvoiceController::class, 'create'])
//                 ->name('create');

//             Route::post('/store', [AccountantInvoiceController::class, 'store'])
//                 ->name('store');

//             Route::get('/{invoice}', [AccountantInvoiceController::class, 'show'])
//                 ->name('show');
//         });
//     });
// Route::post('/sales', [\App\Http\Controllers\SaleController::class, 'store']);
// Route::get('/pos', function () {
//     return view('pos.index');
// });
Route::prefix('accountant')->group(function () {



    // صفحة الحساب الموقوف
    Route::get('/suspended', function () {
        return view('auth.suspended');
    })->name('accountant.suspended');

});

Route::middleware(['auth:web'])->prefix('user')->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('user.notifications.index');

    Route::get('/notifications/{id}', [NotificationController::class, 'show'])
        ->name('user.notifications.show');

    Route::post('/notifications/{id}/toggle', [NotificationController::class, 'toggle'])
        ->name('user.notifications.toggle');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('user.notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAll'])
        ->name('user.notifications.readAll');
        //  Route::delete('/{id}', [NotificationController::class, 'remov'])->name('remov');

    Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])
        ->name('user.notifications.delete');
    Route::post('/notifications/delete-selected', [NotificationController::class, 'deleteSelected'])
        ->name('user.notifications.deleteSelected');

});






// Route::middleware('auth:accountant')->get('/products/search', function () {
//     $query = request('query');

//     $accountant = auth('accountant')->user();
//     $storeId = $accountant->store_id;

//     return Product::where('store_id', $storeId)
//         ->where(function ($q) use ($query) {
//             $q->where('name', 'like', "%{$query}%")
//               ->orWhere('barcode', 'like', "%{$query}%");
//         })
//         ->limit(10)
//         ->get(['id', 'name', 'price', 'description']);
// })->name('products.search');





// Route::middleware(['auth:web', 'is.admin'])->group(function () {

//     Route::get('/admin/notifications/push', [AdminPushNotificationController::class, 'create'])
//         ->name('admin.notifications.push');

//     Route::post('/admin/notifications/push', [AdminPushNotificationController::class, 'store'])
//         ->name('admin.notifications.push.store');
// });



Route::post('/device-token', [DeviceTokenController::class, 'store'])
    ->name('device.token.store')
    ->middleware('auth');



// Route::middleware(['auth:web', 'is.admin'])->group(function () {

//     Route::get('/admin/onesignal', [AdminOneSignalSettingsController::class, 'index'])
//         ->name('admin.onesignal.index');

//     Route::post('/admin/onesignal', [AdminOneSignalSettingsController::class, 'update'])
//         ->name('admin.onesignal.update');

//     Route::post('/admin/onesignal/test', [AdminOneSignalSettingsController::class, 'test'])
//         ->name('admin.onesignal.test');
// });
Route::post('/save-player-id', [OneSignalController::class, 'savePlayerId'])
    ->middleware('auth')
    ->name('onesignal.save');
Route::get('/db-structure', function () {
    $tables = DB::select('SHOW TABLES');
    $structure = [];

    foreach ($tables as $table) {
        $tableName = current((array)$table);
        $structure[$tableName] = DB::select("DESCRIBE $tableName");
    }

    return response()->json($structure);
});