<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\UserNotificationSendController;


use App\Http\Controllers\AdminOneSignalSettingsController;
use App\Http\Controllers\Notifications\AdminNotificationSendController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\AdminPushNotificationController;

/*
|--------------------------------------------------------------------------
| مسارات المدير (Admin)
|--------------------------------------------------------------------------
| - جميع المسارات تبدأ بـ /admin
| - جميع المسارات تبدأ باسم admin.
| - جميع المسارات محمية بـ web + auth + is.admin
|--------------------------------------------------------------------------
*/
Route::get('/notifications/push', [AdminPushNotificationController::class, 'create'])
        ->name('admin.notifications.push');

    // تنفيذ الإرسال
    Route::post('/notifications/push', [AdminPushNotificationController::class, 'store'])
        ->name('admin.notifications.push.store');


Route::middleware(['web', 'auth', 'is.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | لوحة التحكم
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard.index');

        /*
        |--------------------------------------------------------------------------
        | إدارة المستخدمين
        |--------------------------------------------------------------------------
        */
        Route::resource('users', AdminUserController::class);

        Route::post('users/{user}/suspend', [AdminUserController::class, 'suspend'])
            ->name('users.suspend');

        Route::post('users/{user}/activate', [AdminUserController::class, 'activate'])
            ->name('users.activate');

        /*
        |--------------------------------------------------------------------------
        | إدارة المتاجر (معلّقة حالياً)
        |--------------------------------------------------------------------------
        */
        // Route::resource('stores', AdminStoreController::class);
        // Route::post('stores/{store}/suspend', [AdminStoreController::class, 'suspend'])
        //     ->name('stores.suspend');
        // Route::post('stores/{store}/activate', [AdminStoreController::class, 'activate'])
        //     ->name('stores.activate');

        /*
        |--------------------------------------------------------------------------
        | الإشعارات (Notifications)
        |--------------------------------------------------------------------------
        */
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->name('notifications.index');

        Route::get('/notifications/{id}', [NotificationController::class, 'show'])
            ->name('notifications.show');

        Route::post('/notifications/{id}/toggle', [NotificationController::class, 'toggle'])
            ->name('notifications.toggle');

        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        Route::post('/notifications/read-all', [NotificationController::class, 'markAll'])
            ->name('notifications.readAll');

        Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])
            ->name('notifications.delete');
        Route::delete('/{id}', [NotificationController::class, 'deleteindex'])->name('deleteindex');
        Route::post('/notifications/delete-selected', [NotificationController::class, 'deleteSelected'])
            ->name('notifications.deleteSelected');

        /*
        |--------------------------------------------------------------------------
        | إرسال إشعارات داخلية (للمستخدمين)
        |--------------------------------------------------------------------------
        */

// Route::get('/notifications/send', [AdminNotificationSendController::class, 'create'])
//     ->name('notifications.send');

// Route::post('/notifications/send', [AdminNotificationSendController::class, 'store'])
//     ->name('notifications.send.store');


        /*
        |--------------------------------------------------------------------------
        | إرسال إشعارات OneSignal
        |--------------------------------------------------------------------------
        */
        // Route::get('/notifications/push', [AdminPushNotificationController::class, 'create'])
        //     ->name('notifications.push');

        // Route::post('/notifications/push', [AdminPushNotificationController::class, 'store'])
        //     ->name('notifications.push.store');

        /*
        |--------------------------------------------------------------------------
        | إعدادات OneSignal
        |--------------------------------------------------------------------------
        */
        Route::get('/onesignal', [AdminOneSignalSettingsController::class, 'index'])
            ->name('onesignal.index');

        Route::post('/onesignal', [AdminOneSignalSettingsController::class, 'update'])
            ->name('onesignal.update');

        Route::post('/onesignal/test', [AdminOneSignalSettingsController::class, 'test'])
            ->name('onesignal.test');
    });

/*
|--------------------------------------------------------------------------
| Device Token
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth:web', 'is.admin'])->group(function () {

    // صفحة إرسال إشعار OneSignal
    // Route::get('/notifications/push', [\App\Http\Controllers\AdminPushNotificationController::class, 'create'])
    //     ->name('admin.notifications.push');

    // // تنفيذ الإرسال
    // Route::post('/notifications/push', [\App\Http\Controllers\AdminPushNotificationController::class, 'store'])
    //     ->name('admin.notifications.push.store');

});

Route::post('/device-token', [DeviceTokenController::class, 'store'])
    ->name('device.token.store')
    ->middleware('auth');
Route::get('/notifications/send', [AdminNotificationSendController::class, 'create'])
    ->name('notifications.internal.send');

Route::post('/notifications/send', [AdminNotificationSendController::class, 'store'])
    ->name('notifications.internal.send.store');


//     Route::middleware(['auth:web', 'admin'])->prefix('admin')->name('admin.')->group(function () {

//     Route::prefix('notifications')->name('notifications.')->group(function () {

//         Route::get('/', [NotificationController::class, 'index'])
//             ->name('index');

//         Route::get('/{id}', [NotificationController::class, 'show'])
//             ->name('show');

//         Route::post('/{id}/toggle', [NotificationController::class, 'toggle'])
//             ->name('toggle');

//         Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
//             ->name('read');

//         Route::post('/mark-all', [NotificationController::class, 'markAll'])
//             ->name('markAll');

//         Route::delete('/{id}', [NotificationController::class, 'delete'])
//             ->name('delete');

//         Route::post('/delete-selected', [NotificationController::class, 'deleteSelected'])
//             ->name('deleteSelected');
//     });

// });
