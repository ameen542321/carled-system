<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedToDashboard
{
    public function handle($request, Closure $next)
    {
        // 1) إذا كان المحاسب مسجلاً الدخول
        if (Auth::guard('accountant')->check()) {
            return redirect()->route('accountant.dashboard');
        }

        // 2) إذا كان المستخدم العادي أو الأدمن مسجلاً الدخول
        if (Auth::guard('web')->check()) {

            $user = Auth::guard('web')->user();

            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard.index'),
                default => redirect()->route('user.dashboard.index'),
            };
        }

        // 3) إذا لم يكن أحد مسجلاً الدخول
        return $next($request);
    }
}
