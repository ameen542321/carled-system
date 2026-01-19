<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Store;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Accountant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * التحقق من صلاحية إنشاء متجر جديد حسب الخطة
     */
    protected function canUserAddStore()
    {
        $user = auth()->user();
        if (!$user->plan_id && !$user->allowed_stores) return false;
        
        $allowed = $user->plan_id ? $user->plan->allowed_stores : $user->allowed_stores;
        return $user->stores()->count() < $allowed;
    }

    public function index()
    {
        $user = auth()->user();
        $stores = $user->stores()->latest()->get();
        $trashedCount = $user->stores()->onlyTrashed()->count();
        
        return view('user.stores.index', compact('stores', 'trashedCount'));
    }

    public function show(Store $store)
    {
        // التحقق من ملكية المتجر
        $this->authorizeStoreAccess($store);
        
        $user = auth()->user();
        
        // حساب الإحصائيات - التأكد من استخدام الجداول الصحيحة
        
        // مبيعات اليوم (من جدول sales)
        $todaySales = Sale::where('store_id', $store->id)
            ->whereDate('created_at', today())
            ->sum('final_total');
        
        // مبيعات الشهر
        $monthSales = Sale::where('store_id', $store->id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('final_total');
        
        // عدد الفواتير (من جدول sales)
        $invoicesCount = Sale::where('store_id', $store->id)->count();
        
        // المحاسبين (من جدول accountants)
        $accountantsCount = Accountant::where('store_id', $store->id)->count();
        
        // العمال (من جدول employees)
        $employeesCount = Employee::where('store_id', $store->id)->count();
        
        // الأقسام
        $categoriesCount = Category::where('store_id', $store->id)->count();
        
        // المنتجات
        $productsCount = Product::where('store_id', $store->id)->count();
        
        // أفضل المنتجات مبيعاً (من خلال جدول sale_items)
        $topProducts = Product::where('store_id', $store->id)
            ->withCount(['saleItems as total_sold' => function($query) {
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'))
                      ->whereHas('sale', function($q) {
                          $q->where('created_at', '>=', now()->subDays(30));
                      });
            }])
            ->having('total_sold', '>', 0)
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
        
        // مبيعات آخر 7 أيام للرسم البياني
        $sevenDaysSales = Sale::where('store_id', $store->id)
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_total) as total_sales'),
                DB::raw('COUNT(*) as sales_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // تحضير بيانات الرسم البياني
        $chartLabels = [];
        $chartData = [];
        
        // تعبئة البيانات لآخر 7 أيام
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->translatedFormat('l');
            
            $sale = $sevenDaysSales->firstWhere('date', $date);
            
            $chartLabels[] = $dayName;
            $chartData[] = $sale ? (float) $sale->total_sales : 0;
        }
        
        // إجمالي الأرباح
        $totalProfit = Sale::where('store_id', $store->id)->sum('profit');
        
        // آخر العمليات المسجلة
        $operations = Log::where('store_id', $store->id)
            ->with(['user'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('user.stores.show', [
            'store' => $store,
            'accountantsCount' => $accountantsCount,
            'employeesCount' => $employeesCount,
            'categoriesCount' => $categoriesCount,
            'productsCount' => $productsCount,
            'todaySales' => $todaySales,
            'monthSales' => $monthSales,
            'invoicesCount' => $invoicesCount,
            'totalProfit' => $totalProfit,
            'topProducts' => $topProducts,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'operations' => $operations,
            'user' => $user
        ]);
    }

    public function setCurrentStore(Store $store)
    {
        $this->authorizeStoreAccess($store);
        
        if ($store->status == 'suspended') {
            return back()->with('error', 'لا يمكن تعيين متجر معطل كمجر حالي');
        }
        
        auth()->user()->update(['current_store_id' => $store->id]);
        
        // تسجيل العملية
        Log::create([
            'store_id' => $store->id,
            'user_id' => auth()->id(),
            'actor_type' => 'App\Models\User',
            'actor_id' => auth()->id(),
            'model_type' => Store::class,
            'model_id' => $store->id,
            'action' => 'set_current',
            'description' => 'تم تعيين المتجر كمتجر حالي',
            'details' => json_encode(['name' => $store->name]),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        return back()->with('success', 'تم تعيين ' . $store->name . ' كمتجر حالي');
    }

    /**
     * دالة مساعدة للتحقق من صلاحية الوصول للمتجر
     */
    private function authorizeStoreAccess(Store $store)
    {
        if ($store->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بالوصول لهذا المتجر.');
        }
    }

    /**
     * دالة إضافية: الحصول على إحصائيات متقدمة للمتجر (API)
     */
    public function getAdvancedStats(Store $store)
    {
        $this->authorizeStoreAccess($store);
        
        // إحصائيات المبيعات الشهرية للعام الحالي
        $monthlySales = Sale::where('store_id', $store->id)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(final_total) as total_sales'),
                DB::raw('COUNT(*) as sales_count'),
                DB::raw('SUM(profit) as total_profit')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        // إحصائيات المنتجات
        $productStats = Product::where('store_id', $store->id)
            ->select(
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(price * quantity) as total_value'),
                DB::raw('AVG(price) as average_price')
            )
            ->first();
        
        // المنتجات قليلة المخزون
        $lowStockProducts = Product::where('store_id', $store->id)
            ->whereRaw('quantity <= min_stock')
            ->where('min_stock', '>', 0)
            ->orderBy('quantity')
            ->limit(10)
            ->get(['id', 'name', 'quantity', 'min_stock', 'price']);
        
        // إحصائيات الموظفين
        $employeeStats = Employee::where('store_id', $store->id)
            ->select(
                DB::raw('COUNT(*) as total_employees'),
                DB::raw('SUM(salary) as total_salary'),
                DB::raw('AVG(salary) as average_salary')
            )
            ->first();
        
        return response()->json([
            'monthly_sales' => $monthlySales,
            'product_stats' => $productStats,
            'employee_stats' => $employeeStats,
            'low_stock_products' => $lowStockProducts,
            'low_stock_count' => $lowStockProducts->count()
        ]);
    }

    // باقي الدوال تبقى كما هي (create, store, edit, update, destroy, restore, forceDelete, toggleStatus, duplicate)
    // ... مع التأكد من استخدام الجداول الصحيحة في كل دالة
    
    /**
     * حذف المتجر (نقل للسلة)
     */
    public function destroy(Store $store)
    {
        $this->authorizeStoreAccess($store);
        
        DB::beginTransaction();
        try {
            // تسجيل العملية قبل الحذف
            Log::create([
                'store_id' => $store->id,
                'user_id' => auth()->id(),
                'actor_type' => 'App\Models\User',
                'actor_id' => auth()->id(),
                'model_type' => Store::class,
                'model_id' => $store->id,
                'action' => 'delete',
                'description' => 'تم نقل المتجر إلى سلة المهملات',
                'details' => json_encode(['name' => $store->name]),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            // حذف المتجر (Soft Delete)
            $store->delete();
            
            DB::commit();
            
            return redirect()->route('user.stores.index')
                ->with('success', 'تم نقل المتجر إلى سلة المهملات بنجاح');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.stores.show', $store)
                ->with('error', 'حدث خطأ أثناء حذف المتجر: ' . $e->getMessage());
        }
    }

    /**
     * تغيير حالة المتجر
     */
    public function toggleStatus(Store $store, Request $request)
    {
        $this->authorizeStoreAccess($store);
        
        $request->validate([
            'status' => 'required|in:active,suspended',
            'suspension_reason' => 'required_if:status,suspended|string|max:255'
        ]);
        
        $oldStatus = $store->status;
        $newStatus = $request->status;
        
        $store->update([
            'status' => $newStatus,
            'suspension_reason' => $newStatus == 'suspended' ? $request->suspension_reason : null
        ]);
        
        // تسجيل العملية
        Log::create([
            'store_id' => $store->id,
            'user_id' => auth()->id(),
            'actor_type' => 'App\Models\User',
            'actor_id' => auth()->id(),
            'model_type' => Store::class,
            'model_id' => $store->id,
            'action' => 'status_change',
            'description' => 'تم تغيير حالة المتجر',
            'details' => json_encode([
                'name' => $store->name,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $request->suspension_reason
            ]),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        $message = $newStatus == 'active' ? 'تم تفعيل المتجر' : 'تم تعليق المتجر';
        return back()->with('success', $message . ' بنجاح');
    }
}