<?php

namespace App\Http\Controllers\Financial;

use Carbon\Carbon;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class FinancialController extends Controller
{
    /**
     * لوحة التحكم المالية (إحصائيات عامة)
     */
    public function index()
{
    // جلب متاجري فقط (الأمان)
    $storeIds = auth()->user()->stores->pluck('id');
    $query = Sale::whereIn('store_id', $storeIds);

    // حساب المبيعات مباشرة من قاعدة البيانات (السرعة)
    $totalSales = (clone $query)->sum('final_total');

    $salesByType = (clone $query)->selectRaw('sale_type, SUM(final_total) as total')
                                 ->groupBy('sale_type')
                                 ->pluck('total', 'sale_type');

    // مبيعات اليوم باستخدام index قاعدة البيانات
    $todaySales = (clone $query)->whereDate('created_at', Carbon::today())->sum('final_total');

    // ملاحظة: التكلفة والربح يجب أن تكون أعمدة في الجدول لتجنب الـ Loops
    $totalCost = (clone $query)->sum('total_cost_at_sale');
    $profit = $totalSales - $totalCost;

    return view('financial.index', compact(
        'totalSales', 'todaySales', 'totalCost', 'profit', 'salesByType'
    ));
}

    /**
     * تقرير حسب التاريخ
     */
    public function reportByDate($from, $to)
    {
        $sales = Sale::whereBetween('created_at', [$from, $to])->get();

        return response()->json([
            'total'  => $sales->sum('final_total'),
            'cash'   => $sales->where('sale_type', 'cash')->sum('final_total'),
            'card'   => $sales->where('sale_type', 'card')->sum('final_total'),
            'credit' => $sales->where('sale_type', 'credit')->sum('final_total'),
        ]);
    }

    /**
     * تقرير الأرباح والخسائر
     */
    public function profitLoss()
    {
        $sales = Sale::all();

        $totalSales = $sales->sum('final_total');

        $totalCost = $sales->sum(function ($sale) {
            $items = json_decode($sale->items, true);
            $cost = 0;

            foreach ($items as $item) {
                $cost += ($item['cost'] ?? 0) * $item['quantity'];
            }

            return $cost;
        });

        $profit = $totalSales - $totalCost;

        return view('financial.profit-loss', compact(
            'totalSales',
            'totalCost',
            'profit'
        ));
    }

    /**
     * أعلى المنتجات مبيعًا
     */
   public function topProducts()
{
    $storeIds = auth()->user()->stores->pluck('id');

    $topProducts = DB::table('sale_items')
        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
        ->whereIn('sales.store_id', $storeIds)
        ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_qty'))
        ->groupBy('sale_items.product_id')
        ->orderByDesc('total_qty')
        ->take(10) // أفضل 10 منتجات
        ->get();

    return response()->json($topProducts);
}
}
