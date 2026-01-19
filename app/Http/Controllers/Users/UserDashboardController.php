<?php

namespace App\Http\Controllers\Users;

use App\Models\Log;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\CreditSale;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth('web')->user();

        // جلب المتاجر
        $stores = $user->stores;
        $storeIds = $stores->pluck('id');

        // حالة المتاجر 0: إذا لم يكن هناك متاجر، نرسل بيانات صفرية لتجنب أخطاء SQL
        if ($storeIds->isEmpty()) {
            return view('dashboard.user.index', $this->emptyStateData($user, $stores));
        }

        // المحاسبين والموظفين
        $accountantsCount = $user->accountants()->count();
        $employeesCount   = $user->employees()->count();

        // الاشتراك
        $subscriptionEnd  = $user->subscription_end_at;
        $daysLeft         = $subscriptionEnd ? now()->diffInDays($subscriptionEnd, false) : null;

        /*
        |--------------------------------------------------------------------------
        | المبيعات (تم التعديل بناءً على هيكل جداولك)
        |--------------------------------------------------------------------------
        */

        // مبيعات الكاش والشبكة (Sale) - نستخدم final_total كما هو في جدولك
        $cashSalesToday = Sale::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', ['cash', 'card'])
            ->sum('final_total');

        $cashSalesMonth = Sale::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereIn('sale_type', ['cash', 'card'])
            ->sum('final_total');

        // مبيعات الآجل (Credit) - نستخدم final_total من جدول sales حيث النوع credit
        $creditSalesToday = Sale::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->where('sale_type', 'credit')
            ->sum('final_total');

        $creditSalesMonth = Sale::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('sale_type', 'credit')
            ->sum('final_total');

        $salesToday = $cashSalesToday + $creditSalesToday;
        $salesMonth = $cashSalesMonth + $creditSalesMonth;

        /* المصروفات - نستخدم عمود amount كما هو في جدولك */
        $expensesToday = Expense::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->sum('amount');

        $expensesMonth = Expense::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        /* صافي الربح - نستخدم عمود profit الموجود في جدولك مباشرة لأدق نتيجة */
        $profitToday = Sale::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->sum('profit') - $expensesToday;

        $profitMonth = Sale::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('profit') - $expensesMonth;

        /* تحليل المديونيات */
        $creditOpen   = Sale::whereIn('store_id', $storeIds)->where('sale_type', 'credit')->where('remaining_amount', '>', 0)->count();
        $creditClosed = Sale::whereIn('store_id', $storeIds)->where('sale_type', 'credit')->where('remaining_amount', '<=', 0)->count();
        $creditLate   = Sale::whereIn('store_id', $storeIds)->where('sale_type', 'credit')->where('remaining_amount', '>', 0)->whereDate('created_at', '<', now()->subDays(30))->count();

        /* المخطط البياني للـ 14 يوم الأخيرة */
        $chartData = $this->prepareChartData($storeIds);

        /* آخر العمليات */
        $activities = Log::with('store')->whereIn('store_id', $storeIds)->latest()->limit(10)->get();

        return view('dashboard.user.index', array_merge(compact(
            'stores', 'accountantsCount', 'employeesCount', 'daysLeft', 'salesToday', 'salesMonth',
            'expensesToday', 'expensesMonth', 'profitToday', 'profitMonth', 'creditOpen', 
            'creditClosed', 'creditLate', 'user', 'activities'
        ), $chartData));
    }

    private function prepareChartData($storeIds)
    {
        $chartStart = now()->subDays(13)->startOfDay();
        $chartEnd   = now()->endOfDay();

        $dailySales = Sale::selectRaw('DATE(created_at) as day, SUM(final_total) as total, SUM(CASE WHEN sale_type = "credit" THEN final_total ELSE 0 END) as credit')
            ->whereIn('store_id', $storeIds)->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day')->get()->keyBy('day');

        $dailyExpenses = Expense::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereIn('store_id', $storeIds)->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day')->get()->keyBy('day');

        $labels = []; $sales = []; $exps = []; $credits = [];

        for ($i = 0; $i < 14; $i++) {
            $date = $chartStart->copy()->addDays($i)->toDateString();
            $labels[]  = $date;
            $sales[]   = $dailySales[$date]->total ?? 0;
            $credits[] = $dailySales[$date]->credit ?? 0;
            $exps[]    = $dailyExpenses[$date]->total ?? 0;
        }

        return ['chartLabels' => $labels, 'chartSales' => $sales, 'chartExpenses' => $exps, 'chartCredit' => $credits];
    }

    private function emptyStateData($user, $stores)
    {
        return [
            'stores' => $stores, 'user' => $user, 'accountantsCount' => 0, 'employeesCount' => 0,
            'daysLeft' => 0, 'salesToday' => 0, 'salesMonth' => 0, 'expensesToday' => 0,
            'expensesMonth' => 0, 'profitToday' => 0, 'profitMonth' => 0, 'creditOpen' => 0,
            'creditClosed' => 0, 'creditLate' => 0, 'activities' => collect(),
            'chartLabels' => [], 'chartSales' => [], 'chartExpenses' => [], 'chartCredit' => []
        ];
    }
}