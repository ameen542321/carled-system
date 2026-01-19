<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserDashboardController;

use App\Http\Controllers\Store\StoreMembersController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AccountantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\Store\ExpenseController;
use App\Http\Controllers\EmployeeActionsController;
use App\Http\Controllers\Employees\EmployeeController;

    Route::post('/user/logout', [LoginController::class, 'logout'])
        ->name('logout');



        Route::middleware(['web', 'owner.unified'])->prefix('user')->name('user.')->group(function () {

   
    // 1. قسم الإشعارات (Notifications)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // ضع راوتات الإشعارات هنا حرفياً
    });

    // 2. قسم المتاجر (Stores)
    Route::prefix('stores')->name('stores.')->group(function () {
         Route::get('/', [StoreController::class, 'index'])->name('index');
                Route::get('/create', [StoreController::class, 'create'])->name('create');
                Route::get('/trash', [StoreController::class, 'trash'])->name('trash');

                Route::post('/store', [StoreController::class, 'store'])
                    ->name('store');

                     Route::patch('/{store}/toggle-status', [StoreController::class, 'toggleStatus'])
                   ->name('toggle-status');

                Route::get('/{store}', [StoreController::class, 'show'])->name('show');
                Route::get('/{store}/edit', [StoreController::class, 'edit'])->name('edit');
                Route::put('/{store}', [StoreController::class, 'update'])->name('update');
                Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');

                Route::post('/{id}/restore', [StoreController::class, 'restore'])
                    ->middleware('plan.limit:store-restore')
                    ->name('restore');

                Route::delete('/{id}/force-delete', [StoreController::class, 'forceDelete'])
                    ->name('forceDelete');

                Route::get('/{store}/catalog', [StoreController::class, 'catalog'])
                    ->name('catalog');
          
         // للحصول على بيانات الرسم البياني ديناميكياً
    Route::get('/sales-chart', [StoreController::class, 'salesChart'])
        ->name('sales.chart');
        
        // ضع راوتات المتاجر (Index, Create, Show, Edit, Delete) هنا
        
        // بريفكس فرعي لمنتجات وتصنيفات المتجر
        Route::prefix('{store}')->group(function () {
             
    // محاسبو المتجر
    Route::get('accountants', [StoreMembersController::class, 'accountants'])
        ->name('accountants.index');

    // موظفو المتجر
    Route::get('employees', [StoreMembersController::class, 'employees'])
        ->name('employees.index');



            Route::prefix('categories')->name('categories.')->group(function () { /* التصنيفات */ });
            Route::prefix('products')->name('products.')->group(function () { /* المنتجات */ });
        });
    });

    // 3. قسم المحاسبين (Accountants)
    Route::prefix('accountants')->name('accountants.')->group(function () {
        // ضع راوتات المحاسبين هنا
    });

    // 4. قسم الموظفين (Employees)
    Route::prefix('employees')->name('employees.')->group(function () {

     Route::get('/', [EmployeeController::class, 'index'])->name('index');
    Route::get('/create', [EmployeeController::class, 'create'])->name('create');
    Route::post('/', [EmployeeController::class, 'store'])->name('store');

    // Trash
    Route::get('/trash', [EmployeeController::class, 'trash'])->name('trash');
    Route::post('/{id}/restore', [EmployeeController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('forceDelete');

    // CRUD
    Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');

    // Logs + Actions
    Route::get('/{employee}/actions', [EmployeeActionsController::class, 'index'])->name('actions');
    Route::get('/{employee}/logs', [EmployeeActionsController::class, 'logs'])->name('logs');

    // PDF Report
    Route::get('/{employee}/export-log', [EmployeeController::class, 'exportSnappy'])->name('exportLog');

    // Daily operations
    Route::post('/{employee}/absence', [EmployeeActionsController::class, 'storeAbsence'])->name('absence.store');
    Route::post('/{employee}/debt', [EmployeeActionsController::class, 'storeDebt'])->name('debt.store');
    Route::post('/{employee}/credit-sale', [EmployeeActionsController::class, 'storeCreditSale'])->name('credit-sale.store');
    Route::post('/{employee}/credit-sale/{sale}/collect', [EmployeeActionsController::class, 'collectCreditSale'])
        ->name('credit-sale.collect');
        // ضع راوتات الموظفين (العمليات، المديونيات، السحب، التقارير) هنا
    });

    // 5. قسم المالية (Finance)
    Route::prefix('finance')->name('finance.')->group(function () {
        // ضع راوتات المصروفات (Expenses) هنا
    });

    // 6. لوحة التحكم والإعدادات (Dashboard & Settings)
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard.index');

});








/*
|--------------------------------------------------------------------------
| WEB + USER MIDDLEWARE
|--------------------------------------------------------------------------
*/
Route::middleware('web')->group(function () {

    Route::middleware([
        'auth:web',
        'subscription.active',
        'subscription.warning',
        'check.suspended',
        'active.welcome',
        'is.user',
    ])
        ->prefix('user')
        ->name('user.')
        ->group(function () {
 
            /*
            |--------------------------------------------------------------------------
            | Dashboard + Welcome
            |--------------------------------------------------------------------------
            */
            Route::get('/send-all-reports', [AdminReportController::class, 'sendAllReports']);

            Route::post('/welcome/continue', function () {
                auth('web')->user()->update(['welcome_shown' => true]);
                return redirect()->route('user.dashboard.index');
            })->name('welcome.continue');

            // Route::get('/dashboard', [UserDashboardController::class, 'index'])
            //     ->name('dashboard.index');





            /*
            |--------------------------------------------------------------------------
            | Accountants
            |--------------------------------------------------------------------------
            */
Route::prefix('accountants')->name('accountants.')->group(function () {

    // ثابتة
    Route::get('/', [AccountantController::class, 'index'])->name('index');
    Route::get('/create', [AccountantController::class, 'create'])->name('create');
    Route::post('/store', [AccountantController::class, 'store'])
        ->middleware('plan.limit:accountant')
        ->name('store');
    Route::get('/trash', [AccountantController::class, 'trash'])->name('trash');

    // تعديل
    Route::get('/{id}/edit', [AccountantController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AccountantController::class, 'update'])->name('update');

    // عرض
    Route::get('/{id}', [AccountantController::class, 'show'])->name('show');

    // إجراءات
    Route::get('/{id}/suspend', [AccountantController::class, 'suspend'])->name('suspend');
    Route::get('/{id}/activate', [AccountantController::class, 'activate'])->name('activate');
    Route::delete('/{id}/delete', [AccountantController::class, 'delete'])->name('delete');
    Route::get('/{id}/restore', [AccountantController::class, 'restore'])
        ->middleware('plan.limit:accountant-restore')
        ->name('restore');
    Route::delete('/{id}/force-delete', [AccountantController::class, 'forceDelete'])->name('forceDelete');
});



            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */
            Route::prefix('stores/{store}/categories')->name('stores.categories.')->group(function () {

                Route::get('/', [CategoryController::class, 'index'])->name('index');
                Route::get('/create', [CategoryController::class, 'create'])->name('create');
                Route::post('/', [CategoryController::class, 'store'])->name('store');

                Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
                Route::put('/{category}', [CategoryController::class, 'update'])->name('update');

                Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

                Route::put('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
                    ->name('toggle-status');

                Route::get('/trash', [CategoryController::class, 'trash'])->name('trash');
                Route::put('/{category}/restore', [CategoryController::class, 'restore'])->name('restore');
                Route::delete('/{category}/force-delete', [CategoryController::class, 'forceDelete'])->name('force-delete');
            });


            /*
            |--------------------------------------------------------------------------
            | Products
            |--------------------------------------------------------------------------
            */
           Route::prefix('stores/{store}/products')->name('stores.products.')->group(function () {

    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/create', [ProductController::class, 'create'])->name('create');
    Route::post('/', [ProductController::class, 'store'])->name('store');

    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

    Route::put('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
        ->name('toggle-status');

    Route::get('/trash', [ProductController::class, 'trash'])->name('trash');
    Route::put('/{id}/restore', [ProductController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('force-delete');

    /*
    |--------------------------------------------------------------------------
    | روابط إدارة المخزون
    |--------------------------------------------------------------------------
    */

    // صفحة إدارة المخزون
    Route::get('/{product}/stock', [ProductStockController::class, 'index'])
        ->name('stock');

    // زيادة المخزون
    Route::post('/{product}/stock/increase', [ProductStockController::class, 'increase'])
        ->name('stock.increase');

    // خصم المخزون
    Route::post('/{product}/stock/decrease', [ProductStockController::class, 'decrease'])
        ->name('stock.decrease');

});

        });

});


/*
|--------------------------------------------------------------------------
| EMPLOYEES (CRUD + TRASH + ACTIONS + REPORTS)
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->group(function () {
Route::prefix('employees')->name('employees.')->group(function () {

    // Route::get('/', [EmployeeController::class, 'index'])->name('index');
    // Route::get('/create', [EmployeeController::class, 'create'])->name('create');
    // Route::post('/', [EmployeeController::class, 'store'])->name('store');

    // // Trash
    // Route::get('/trash', [EmployeeController::class, 'trash'])->name('trash');
    // Route::post('/{id}/restore', [EmployeeController::class, 'restore'])->name('restore');
    // Route::delete('/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('forceDelete');

    // // CRUD
    // Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
    // Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
    // Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
    // Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');

    // // Logs + Actions
    // Route::get('/{employee}/actions', [EmployeeActionsController::class, 'index'])->name('actions');
    // Route::get('/{employee}/logs', [EmployeeActionsController::class, 'logs'])->name('logs');

    // // PDF Report
    // Route::get('/{employee}/export-log', [EmployeeController::class, 'exportSnappy'])->name('exportLog');

    // // Daily operations
    // Route::post('/{employee}/absence', [EmployeeActionsController::class, 'storeAbsence'])->name('absence.store');
    // Route::post('/{employee}/debt', [EmployeeActionsController::class, 'storeDebt'])->name('debt.store');
    // Route::post('/{employee}/credit-sale', [EmployeeActionsController::class, 'storeCreditSale'])->name('credit-sale.store');
    // Route::post('/{employee}/credit-sale/{sale}/collect', [EmployeeActionsController::class, 'collectCreditSale'])
    //     ->name('credit-sale.collect');
});});

Route::prefix('user')->middleware('auth:user')->group(function () {


    // // جلب المديونيات
    // Route::get('/debts/{id}', [EmployeeActionsController::class, 'getDebts'])
    //     ->name('user.debts.list');

    // // تحصيل كامل
    // Route::get('/debt/collect/full/{debt}', [EmployeeActionsController::class, 'collectFull'])
    //     ->name('user.debt.collect.full');

    // // تحصيل جزئي
    // Route::get('/debt/collect/partial/{debt}/{amount}', [EmployeeActionsController::class, 'collectPartial'])
    //     ->name('user.debt.collect.partial');
});

/*
|--------------------------------------------------------------------------
| USER → EMPLOYEE PROMOTION / DEMOTION / WITHDRAWALS
|--------------------------------------------------------------------------
*/
Route::prefix('user')->name('user.')->group(function () {

    Route::post('/employees/{employee}/promote', [EmployeeController::class, 'promote'])
        ->name('employees.promote');

    Route::post('/employees/{employee}/demote', [EmployeeController::class, 'demote'])
        ->name('employees.demote');

    Route::post('/{person}/withdrawal', [EmployeeActionsController::class, 'storeWithdrawal'])
        ->name('withdrawal.store');

    Route::get('/{person}/withdrawal', [EmployeeActionsController::class, 'showWithdrawalForm'])
        ->name('withdrawal.form');
        // تحصيل كامل للبيع الآجل

         // تحصيل جزئي للبيع الآجل
         Route::get('{person}/credit-sale/{sale}/collect-partial/{amount}', [EmployeeActionsController::class, 'collectPartialCreditSale']) ->name('credit-sale.collect.partial');
});


/*
|--------------------------------------------------------------------------
| EMAIL CHECK (AJAX)
|--------------------------------------------------------------------------
*/
Route::post('/employees/check-email', [EmployeeController::class, 'checkEmail'])
    ->name('user.employees.checkEmail');



Route::middleware(['auth:user'])->prefix('user')->name('user.')->group(function () {

    // Route::prefix('notifications')->name('notifications.')->group(function () {
    //      Route::delete('/{id}', [NotificationController::class, 'remov'])->name('remov');

    //     Route::get('/', [NotificationController::class, 'index'])->name('index');
    //     Route::get('/{id}', [NotificationController::class, 'show'])->name('show');

    //     Route::post('/{id}/toggle', [NotificationController::class, 'toggle'])->name('toggle');
    //     Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');

    //     Route::post('/mark-all', [NotificationController::class, 'markAll'])->name('markAll');
    //     Route::post('/mark-selected', [NotificationController::class, 'markSelected'])->name('markSelected');
    //     Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
    // });

});

Route::prefix('user')->group(function () {

    // جلب المديونيات
    Route::get('/debts/{id}', [EmployeeActionsController::class, 'getDebts'])
        ->name('debts.list');

    // تحصيل كامل
    Route::get('/debt/collect/full/{debt}', [EmployeeActionsController::class, 'collectFull'])
        ->name('user.debt.collect.full');

    // تحصيل جزئي
    Route::get('/debt/collect/partial/{debt}/{amount}', [EmployeeActionsController::class, 'collectPartial'])
        ->name('user.debt.collect.partial');

});


// إنشاء بيع آجل جديد
Route::post('employees/{employee}/credit-sale',
    [EmployeeActionsController::class, 'storeCreditSale'])
    ->name('employees.credit-sale.store');

// تحصيل كامل
Route::get('employees/{employee}/credit-sale/{sale}/collect-full',
    [EmployeeActionsController::class, 'collectCreditSale'])
    ->name('employees.credit-sale.collect.full');

// تحصيل جزئي
Route::get('employees/{employee}/credit-sale/{sale}/collect-partial/{amount}',
    [EmployeeActionsController::class, 'collectPartialCreditSale'])
    ->name('employees.credit-sale.collect.partial');


Route::middleware(['auth:user'])->group(function () {

    Route::get('/expense', [ExpenseController::class, 'index'])->name('expense.page');
    Route::post('/expense/store', [ExpenseController::class, 'store'])->name('expense.store');
    Route::delete('/expense/{id}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
    Route::get('/expense/edit/{id}', [ExpenseController::class, 'edit']) ->name('expense.edit');
     Route::put('/expense/update/{id}', [ExpenseController::class, 'update']) ->name('expense.update');
});
