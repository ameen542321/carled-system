<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Store\ExpenseController;
use App\Http\Controllers\Cashier\QuickSaleController;
use App\Http\Controllers\Accountant\DashboardController;
use App\Http\Controllers\Store\EmployeeFinanceController;
use App\Http\Controllers\Accountant\ProductSearchController;
use App\Http\Controllers\Cashier\InvoiceController;
Route::get('/view-report/{filename}', function ($filename) {
    $path = storage_path('app/public/reports/' . $filename);

    if (!file_exists($path)) {
        abort(404, 'الملف غير موجود');
    }

    return response()->file($path);
})->name('pdf.report.view');
Route::prefix('accountant')
    ->middleware(['accountant.auth'])
    ->name('accountant.')
    ->group(function () {
// رابط لعرض التقرير (يحتاجه الواتساب لتحميل الملف)
Route::get('/view-report/{filename}', [DashboardController::class, 'viewReport'])->name('report.view');

Route::post('/balance/store', [DashboardController::class, 'storeBalance'])->name('balance.store');
        // صفحة البيع السريع
        Route::get('/quick-sale', [QuickSaleController::class, 'index'])
            ->name('quick-sale.index');

        // جلب الأشخاص (للبيع الآجل)
        Route::get('/quick-sale/credit-persons', [QuickSaleController::class, 'creditPersons'])
            ->name('quick-sale.credit-persons');

        // تنفيذ عملية البيع (POST)
        Route::post('/quick-sale/submit', [QuickSaleController::class, 'submit'])
            ->name('quick-sale.submit');

        // صفحة إنشاء الفاتورة
        Route::get('/quick-sale/invoice/{sale}', [InvoiceController::class, 'create'])
            ->name('quick-sale.invoice.create');

        // حفظ الفاتورة
        Route::post('/quick-sale/invoice/{sale}/store', [InvoiceController::class, 'store'])
            ->name('quick-sale.invoice.store');

        // طباعة الفاتورة
        Route::get('/quick-sale/invoice/{invoice}/print', [InvoiceController::class, 'print'])
            ->name('quick-sale.invoice.print');
    });
    // مسار تحميل الفاتورة كـ PDF
Route::get('quick-sale/invoice/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])
     ->name('accountant.quick-sale.invoice.pdf');

    Route::post('/accountant/logout', [LoginController::class, 'logout'])
        ->name('accountant.logout');


/*
|--------------------------------------------------------------------------
| صفحة الإيقاف
|--------------------------------------------------------------------------
*/
Route::get('/suspended', fn() => view('accountant.suspended'))
    ->name('accountant.suspended');


/*
|--------------------------------------------------------------------------
| مسارات المحاسب المحمية
|--------------------------------------------------------------------------
*/
Route::prefix('accountant')
    ->middleware(['accountant.auth'])
    ->name('accountant.')
    ->group(function () {

// )->name('quick-sale.submit');



        /*
        |--------------------------------------------------------------------------
        | لوحة التحكم
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | المديونيات
        |--------------------------------------------------------------------------
        */
        Route::get('/debts/{id}', [EmployeeFinanceController::class, 'getDebts'])
            ->name('debts.list');

        Route::get('/debt/collect/full/{debt}', [EmployeeFinanceController::class, 'collectFull'])
            ->name('debt.collect.full');

        Route::get('/debt/collect/partial/{debt}/{amount}', [EmployeeFinanceController::class, 'collectPartial'])
            ->name('debt.collect.partial');


        /*
        |--------------------------------------------------------------------------
        | POS — نظام نقاط البيع
        |--------------------------------------------------------------------------
        */
        Route::prefix('pos')->name('pos.')->group(function () {

            // البحث عن منتج



            // السحب
            Route::get('/withdrawal', [EmployeeFinanceController::class, 'withdrawalPage'])
                ->name('withdrawal.page');

            Route::post('/withdrawal/store/{employee}',
                [EmployeeFinanceController::class, 'storeWithdrawal'])
                ->name('withdrawal.store');


            // الغياب
            Route::get('/absence', [EmployeeFinanceController::class, 'absencePage'])
                ->name('absence.page');

            Route::post('/absence/store/{employee}',
                [EmployeeFinanceController::class, 'storeAbsence'])
                ->name('absence.store');


            // المصروفات
            // Route::get('/expense', [EmployeeFinanceController::class, 'expensePage'])
            //     ->name('expense.page');

            // Route::post('/expense/store/{employee}',
            //     [EmployeeFinanceController::class, 'storeExpense'])
            //     ->name('expense.store');


    Route::get('/expense', [ExpenseController::class, 'index'])->name('expense.page');
Route::post('/expense/store', [ExpenseController::class, 'store']) ->name('expense.store');
   Route::delete('/expense/{id}', [ExpenseController::class, 'destroy'])->name('expense.destroy');



            // البيع الآجل
            Route::get('/credit-sale', [EmployeeFinanceController::class, 'creditSalePage'])
                ->name('credit-sale.page');

            Route::post('/credit-sale/store/{employee}',
                [EmployeeFinanceController::class, 'storeCreditSale'])
                ->name('credit-sale.store');


            // التحصيل
            Route::get('/collection', [EmployeeFinanceController::class, 'collectionPage'])
                ->name('collection.page');

            Route::post('/collection/store/{sale}',
                [EmployeeFinanceController::class, 'storeCollection'])
                ->name('collection.store');


            // المديونية
            Route::get('/debt', [EmployeeFinanceController::class, 'debtPage'])
                ->name('debt.page');

            Route::post('/debt/store/{employee}',
                [EmployeeFinanceController::class, 'storeDebt'])
                ->name('debt.store');
        });


        /*
        |--------------------------------------------------------------------------
        | النظام القديم
        |--------------------------------------------------------------------------
        */
        Route::prefix('employees')->name('employees.')->group(function () {

            Route::get('/{employee}/actions', [App\Http\Controllers\EmployeeActionsController::class, 'index'])
                ->name('actions');

            Route::post('/{employee}/absence', [App\Http\Controllers\EmployeeActionsController::class, 'storeAbsence'])
                ->name('absence.store');

            Route::post('/{employee}/debt', [App\Http\Controllers\EmployeeActionsController::class, 'storeDebt'])
                ->name('debt.store');
        });



        /*
        |--------------------------------------------------------------------------
        | الإشعارات
        |--------------------------------------------------------------------------
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {

            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/{id}', [NotificationController::class, 'show'])->name('show');

            Route::post('/{id}/toggle', [NotificationController::class, 'toggle'])->name('toggle');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');

            Route::post('/mark-all', [NotificationController::class, 'markAll'])->name('markAll');
            Route::post('/mark-selected', [NotificationController::class, 'markSelected'])->name('markSelected');

            Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
            Route::delete('/{id}', [NotificationController::class, 'remov'])->name('remov');
        });

    });


Route::middleware('auth:accountant')->get('/products/search', [App\Http\Controllers\Cashier\ProductSearchController::class, 'search'])->name('products.search');
