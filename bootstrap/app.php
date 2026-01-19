<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // Web middleware group
        $middleware->group('web', [

            /* |--- ⭐ تشغيل الجدولة ---
            | تم تعطيل الحارس القديم لأنه يسبب بطء شديد في الموقع (انتحار الأداء)
            | \App\Http\Middleware\RunScheduler::class,
            */

            // الكوكيز
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,

            // تفعيل الجلسة
            \Illuminate\Session\Middleware\StartSession::class,

            // مشاركة الأخطاء
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,

            // حماية CSRF
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,

            // ربط الروتات (يجب أن يكون دائماً قبل حراس المتاجر)
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Aliases
        $middleware->alias([
            'is.admin'   => \App\Http\Middleware\IsAdmin::class,

            /* |--- 🛡️ الحراس الجدد (مدمجة ومنظمة) --- */

            // حارس المالك الشامل (يدمج: is.user, check.suspended, subscription.active, active.welcome, plan.limit)
            'owner.master' => \App\Http\Middleware\UnifiedOwnerGuard::class,

            // حارس المحاسب الشامل (يدمج: accountant.auth, check.suspended, subscription.active, store.active)
            'accountant.master' => \App\Http\Middleware\UnifiedAccountantGuard::class,

            // حارس المتجر الشامل (يدمج: store.access, store.active)
            'store.master' => \App\Http\Middleware\UnifiedStoreGuard::class,

            // /* |--- 🛑 الحراس القدامى (للمراجعة فقط - سيتم حذفهم لاحقاً) ---
            'is.user'              => \App\Http\Middleware\IsUser::class,
            'subscription.active'  => \App\Http\Middleware\CheckSubscriptionActive::class,
            'subscription.warning' => \App\Http\Middleware\SubscriptionWarning::class,
            'store.active'         => \App\Http\Middleware\CheckStoreStatus::class,
            'store.access'         => \App\Http\Middleware\CheckStoreAccess::class,
            'check.suspended'      => \App\Http\Middleware\CheckUserSuspended::class,
            'active.welcome'       => \App\Http\Middleware\RedirectActiveUser::class,
            'accountant.auth'      => \App\Http\Middleware\AccountantAuth::class,
            // |-------------------------------------------------------------------------- */

            'redirect.dashboard' => \App\Http\Middleware\RedirectIfAuthenticatedToDashboard::class,
            'no.access'          => \App\Http\Middleware\NoAccess::class,
            'plan.limit'         => \App\Http\Middleware\CheckPlanLimit::class, 
            // الحراس النهائيين 
            // حارس المالك
            'owner.unified' => \App\Http\Middleware\UnifiedOwnerGuard::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->withSchedule(function (Schedule $schedule) {
        /* |--- ✅ المكان الصحيح لتشغيل الجدولة ---
        | بدلاً من تشغيلها مع كل نقرة مستخدم، لارافيل سيتولى الأمر هنا بكفاءة
        */
        $schedule->command('model:prune')->daily();
        // أضف مهامك هنا..
    })

    ->create();
