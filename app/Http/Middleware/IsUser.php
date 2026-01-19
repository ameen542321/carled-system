<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsUser
{
    public function handle(Request $request, Closure $next)
    {
        // 1) التأكد من تسجيل الدخول في guard الويب
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        // 2) فحص الدور (يجب أن يكون مستخدماً وليس مديراً أو محاسباً هنا)
        if ($user->role !== 'user') {
            return redirect()->route('no.access');
        }

        // 3) فحص الحالة (Enum: active, suspended, inactive)
        // إذا كان المالك موقوفاً، نطرده فوراً
        if ($user->status !== 'active') {
            Auth::guard('web')->logout();
            return redirect()->route('user.suspended')->with('error', 'حسابك موقوف حالياً.');
        }

        // 4) فحص انتهاء الاشتراك (Subscription Logic)
        // نستخدم عمود subscription_end_at الموجود في جدولك
        if ($user->subscription_end_at && now()->gt($user->subscription_end_at)) {

            // السماح فقط بمسارات التجديد والاشتراك حتى لا يحبس المستخدم
            if (!$request->is('subscription*') && !$request->is('logout')) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
