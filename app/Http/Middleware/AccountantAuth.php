<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountantAuth
{
   public function handle(Request $request, Closure $next)
{
    // 1. السماح بمسارات تسجيل الدخول والخروج لتجنب الـ Infinite Redirect
    if ($request->is('login*') || $request->is('accountant/logout')) {
        return $next($request);
    }

    // 2. التحقق من وجود الجلسة
    if (!Auth::guard('accountant')->check()) {
        return redirect()->route('login');
    }

    $accountant = Auth::guard('accountant')->user();

    // 3. التحقق من حالة المحاسب (باستخدام Enum الجداول التي أرسلتها)
    if ($accountant->status !== 'active') {
        Auth::guard('accountant')->logout();
        return redirect()->route('accountant.suspended')->with('error', 'حساب المحاسب موقوف.');
    }

    // 4. الربط مع جدول الموظفين (القاعدة التي ذكرتها: المحاسب هو موظف)
    // نستخدم الـ Null-safe operator لضمان عدم انهيار النظام
    if (!$accountant->employee || $accountant->employee->status !== 'active') {
        Auth::guard('accountant')->logout();
        return redirect()->route('login')->with('error', 'عذراً، حالة الموظف المرتبطة بهذا الحساب غير نشطة.');
    }

    // 5. التحقق من حالة المالك وحالة المتجر
    if ($accountant->user?->status !== 'active' || $accountant->store?->status !== 'active') {
        Auth::guard('accountant')->logout();
        return redirect()->route('accountant.suspended')->with('error', 'المتجر أو حساب المالك غير نشط حالياً.');
    }

    return $next($request);
}
}
