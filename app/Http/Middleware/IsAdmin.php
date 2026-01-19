<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
class IsAdmin
{
    public function handle(Request $request, Closure $next)
{
    if (!Auth::guard('web')->check()) {
        return redirect()->route('login');
    }

    $user = Auth::guard('web')->user();

    // 1. فحص الدور والحالة
    if ($user->role !== 'user' || $user->status !== 'active') {
        return redirect()->route('no.access');
    }

    // 2. فحص الاشتراك (مهم جداً لمشروعك)
    // إذا انتهى تاريخ الاشتراك الموجود في الجدول
    if ($user->subscription_end_at && now()->gt($user->subscription_end_at)) {
        // نسمح له فقط بدخول صفحات الاشتراك للتجديد، ونمنعه من باقي النظام
        if (!$request->is('subscription*')) {
            return redirect()->route('subscription.expired');
        }
    }

    return $next($request);
}
}
