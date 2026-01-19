<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * صفحة تخبر المستخدم أن الاشتراك منتهٍ
     * route: subscription.expired
     */
    public function expired(Request $request)
    {
        $user = $request->user();

        // لو المستخدم مدير، ما المفروض يوصل هنا، لكن للاحتياط:
        if ($user && $user->role === 'admin') {
            return redirect()->route('dashboard');
        }

        // لو المستخدم رجع نشط لأي سبب، رجّعه للداشبورد
        if ($user && $user->status === 'نشط') {
            return redirect()->route('dashboard');
        }

        return view('subscriptions.expired', [
            'user' => $user,
        ]);
    }

    /**
     * صفحة اختيار الخطة وتجديد الاشتراك
     * route: subscription.renew (GET)
     */
    public function renew(Request $request)
    {
        $user = $request->user();

        // لو ما فيه مستخدم → رجوع لتسجيل الدخول
        if (!$user) {
            return redirect()->route('login');
        }

        // تعريف الخطط الثلاث (ثابتة حاليًا)
        $plans = [
            'basic' => [
                'name'            => 'الخطة العادية',
                'stores_limit'    => 1,
                'accountants_limit' => 2,
                'months'          => 6,
            ],
            'silver' => [
                'name'            => 'الخطة الفضية',
                'stores_limit'    => 3,
                'accountants_limit' => 8,
                'months'          => 6,
            ],
            'gold' => [
                'name'            => 'الخطة الذهبية',
                'stores_limit'    => 6,
                'accountants_limit' => 15,
                'months'          => 6,
            ],
        ];

        return view('subscription.renew', [
            'user'  => $user,
            'plans' => $plans,
        ]);
    }

    /**
     * معالجة طلب التجديد واختيار الخطة
     * route: subscription.processRenew (POST)
     */
    public function processRenew(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // التحقق من الخطة المختارة
        $data = $request->validate([
            'plan' => 'required|in:basic,silver,gold',
        ]);

        $planKey = $data['plan'];

        $plans = [
            'basic' => [
                'name'              => 'الخطة العادية',
                'stores_limit'      => 1,
                'accountants_limit' => 2,
                'months'            => 6,
            ],
            'silver' => [
                'name'              => 'الخطة الفضية',
                'stores_limit'      => 3,
                'accountants_limit' => 8,
                'months'            => 6,
            ],
            'gold' => [
                'name'              => 'الخطة الذهبية',
                'stores_limit'      => 6,
                'accountants_limit' => 15,
                'months'            => 6,
            ],
        ];

        $plan = $plans[$planKey];

        // نقطة البداية لحساب نهاية الاشتراك:
        // - لو عنده اشتراك لم ينتهِ بعد → نمدّده من تاريخه الحالي
        // - لو منتهي أو null → نبدأ من اليوم
        $now = Carbon::now();

        if ($user->subscription_end_at && $now->lessThanOrEqualTo($user->subscription_end_at)) {
            $baseDate = Carbon::parse($user->subscription_end_at);
        } else {
            $baseDate = $now;
        }

        $newEndDate = $baseDate->copy()->addMonths($plan['months']);

        // تحديث بيانات المستخدم حسب الخطة
        $user->subscription_end_at = $newEndDate;
        $user->status              = 'نشط';
        $user->plan                = $planKey;           // تأكد أن عندك عمود plan في جدول users
        $user->max_stores          = $plan['stores_limit'];      // تأكد من الأعمدة في DB
        $user->max_accountants     = $plan['accountants_limit']; // نفس الشيء

        $user->save();

        // فلاش رسالة نجاح
        session()->flash('subscription_success', 'تم تجديد اشتراكك بنجاح على ' . $plan['name']);

        return redirect()->route('dashboard');
    }

    /**
     * (اختياري) تعديل تاريخ الاشتراك يدويًا من قبل المدير
     * تستطيع ربط هذه بالدashboard الخاص بالأدمن لاحقًا
     */
    public function updateUserSubscriptionDate(Request $request, $userId)
    {
        // هنا منطق خاص بالأدمن لتعديل تاريخ الاشتراك يدويًا
        // مثل:
        // - التحقق أن المستخدم Admin
        // - إيجاد المستخدم المطلوب
        // - تعديل subscription_end_at حسب التاريخ القادم من الفورم
        // هذا الجزء نتركه الآن حتى نعرّف المتطلبات الدقيقة له.
    }
}
