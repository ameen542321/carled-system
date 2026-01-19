<?php

namespace App\Http\Controllers\Accountant;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Models\Log;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Accountant;
use App\Models\Withdrawal;
use App\Models\DailyBalance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // تأكد من إضافة هذا السطر

class DashboardController extends Controller
{
    public function index()
{
    $accountant = auth('accountant')->user();
    $storeId = $accountant->store_id;

    // --- نظام الحماية المزدوج لتحديد وقت بداية الوردية ---
    // الخطة أ: البحث في سجلات الأحداث (Logs)
    $lastEvent = Log::where('store_id', $storeId)
                    ->where('action', 'balance_done')
                    ->latest()
                    ->first();

    // الخطة ب: إذا فُقد الـ Log، نبحث في جدول الموازنات (Backup)
    if (!$lastEvent) {
        $lastEvent = \App\Models\DailyBalance::where('store_id', $storeId)
                        ->latest()
                        ->first();
    }

    // تحديد وقت البداية (إما من السجل المكتشف أو من بداية اليوم كحل أخير)
    $startTime = $lastEvent ? ($lastEvent->created_at ?? $lastEvent->end_time) : now()->startOfDay();

    // 1. مبيعات الوردية الحالية فقط (التي تظهر في عداد الموازنة)
    $totalSinceBalance = Sale::where('store_id', $storeId)
                            ->where('created_at', '>', $startTime)
                            ->sum('final_total');

    // 2. إحصائيات الوردية الحالية (تُستخدم حصراً داخل الـ Modal لحساب الكاش المتوقع)
    $currentShiftExpenses = Expense::where('store_id', $storeId)
                            ->where('created_at', '>', $startTime)
                            ->sum('amount');

    $currentShiftWithdrawals = Withdrawal::where('store_id', $storeId)
                            ->where('created_at', '>', $startTime)
                            ->sum('amount');

    // 3. إحصائيات الشهر التراكمية (للعرض في بطاقات الواجهة للمالك والمحاسب)
    $startOfMonth = now()->startOfMonth();
    $stats = [
        'monthly_withdrawals' => Withdrawal::where('store_id', $storeId)
                                    ->where('created_at', '>=', $startOfMonth)
                                    ->sum('amount'),

        'monthly_expenses' => Expense::where('store_id', $storeId)
                                ->where('created_at', '>=', $startOfMonth)
                                ->sum('amount'),
    ];

    // جلب آخر العمليات المنسقة (مبيعات، مصاريف، سحوبات)
    $lastOperations = $this->getLastOperations($storeId);

    return view('dashboard.accountant.index', compact(
        'totalSinceBalance',
        'stats',
        'lastOperations',
        'currentShiftExpenses',
        'currentShiftWithdrawals'
    ));
}

    // هذه هي الدالة التي كانت تنقصك لربط الـ formatOp بالـ Index
    private function getLastOperations($storeId)
    {
        $sales = Sale::where('store_id', $storeId)->latest()->take(5)->get()
            ->map(fn($m) => $this->formatOp($m, 'sale'));

        $expenses = Expense::where('store_id', $storeId)->latest()->take(5)->get()
            ->map(fn($m) => $this->formatOp($m, 'expense'));

        $withdrawals = Withdrawal::where('store_id', $storeId)->latest()->take(5)->get()
            ->map(fn($m) => $this->formatOp($m, 'withdrawal'));

        // دمج وترتيب
        return $sales->concat($expenses)->concat($withdrawals)
                     ->sortByDesc('created_at')
                     ->take(10);
    }
// 1. دالة عرض الملف (يستدعيها الواتساب)
public function viewReport($filename)
{
    $path = storage_path('app/public/reports/' . $filename);
    if (!file_exists($path)) abort(404);
    return response()->file($path);
}

// 2. دالة إرسال الواتساب (تُستدعى داخل storeBalance)
private function sendReportToOwner($phone, $fileName)
{
    // الرابط المباشر للملف (يجب أن يكون موقعك مرفوعاً على سيرفر حقيقي ليعمل)
    $fileUrl = route('accountant.report.view', ['filename' => $fileName]);

    // إعدادات API الواتساب (مثال UltraMsg)
    $params = [
        'token' => 'YOUR_ULTRAMSG_TOKEN',
        'to'    => $phone, // رقم المالك
        'filename' => $fileName,
        'document' => $fileUrl,
        'caption'  => "تقرير إقفال المتجر ليوم " . now()->format('Y-m-d')
    ];

    // إرسال الطلب (Curl أو Guzzle)
    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.ultramsg.com/YOUR_INSTANCE_ID/messages/document",
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => http_build_query($params),
      CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
}
   public function storeBalance(Request $request)
{
    $request->validate(['actual_cash' => 'required|numeric|min:0']);

    // جلب المحاسب الحالي مع علاقاته (المالك والمتجر) لتقليل الاستعلامات
    $accountant = Accountant::with(['user', 'employee'])->find(auth('accountant')->id());

    // 1. جلب المتجر (تأكدنا أن العمود في الموديل هو store_id وليس current_store_id)
    $store = \App\Models\Store::find($accountant->store_id);

    if (!$store) {
        return redirect()->back()->with('error', 'خطأ: لم يتم العثور على المتجر المرتبط بحسابك. (تأكد من حقل store_id)');
    }

    // 2. جلب رقم هاتف المالك مباشرة من علاقة المستخدم المعرفة في الموديل
    // الموديل يحتوي على public function user()، لذا نستخدمها
    $managerPhone = $accountant->user->phone ?? $store->phone ?? null;

    if (!$managerPhone) {
        return redirect()->back()->with('error', 'خطأ: رقم هاتف المالك غير مسجل.');
    }

    // 3. تحديد وقت البداية (من اللوق أو الموازنة السابقة)
    $lastEvent = \App\Models\Log::where('store_id', $store->id)
                    ->where('action', 'balance_done')
                    ->latest()->first()
                 ?? \App\Models\DailyBalance::where('store_id', $store->id)->latest()->first();

    $startTime = $lastEvent ? ($lastEvent->created_at ?? $lastEvent->end_time) : now()->startOfDay();
    $endTime = now();

    // 4. الحسابات المالية
    $salesInShift = Sale::where('store_id', $store->id)
                        ->whereBetween('created_at', [$startTime, $endTime])
                        ->get();

    $cashSales  = (float) $salesInShift->where('sale_type', 'cash')->sum('final_total');
    $cardSales  = (float) $salesInShift->where('sale_type', 'card')->sum('final_total');
    $totalSales = (float) $salesInShift->sum('final_total');

    $expenses    = Expense::where('store_id', $store->id)->whereBetween('created_at', [$startTime, $endTime])->sum('amount');
    $withdrawals = Withdrawal::where('store_id', $store->id)->whereBetween('created_at', [$startTime, $endTime])->sum('amount');

    $expectedCashInHand = $cashSales - ($expenses + $withdrawals);
    $actualCash = (float) $request->actual_cash;
    $difference = $actualCash - $expectedCashInHand;

    // استنتاج أجرة اليد
    $totalProductsValue = $salesInShift->sum(function($sale) {
        return $sale->items->sum(fn($item) => $item->unit_price * $item->quantity);
    });
    $laborCost = max(0, $totalSales - $totalProductsValue);

    // 5. حفظ التقرير والسجلات
    $data = [
        'store_name' => $store->name,
        'day' => now()->translatedFormat('l'),
        'date' => $endTime->format('Y-m-d H:i'),
        'total_sales' => $totalSales, 'cash_sales' => $cashSales, 'card_sales' => $cardSales,
        'expected_cash_in_hand' => $expectedCashInHand, 'actual_cash' => $actualCash, 'difference' => $difference,
        'labor_cost' => $laborCost, 'expenses' => $expenses, 'withdrawals' => $withdrawals, 'accountant' => $accountant->name
    ];

    $fileName = 'Report_' . time() . '.pdf';
    $filePath = storage_path('app/public/reports/' . $fileName);
    PDF::loadView('pdf.pdf_report', $data)->setOption('encoding', 'utf-8')->save($filePath);

    $this->saveFinalRecords($store->id, $accountant->id, $data, $startTime, $endTime);

    // 6. توجيه الواتساب
    $reportLink = route('pdf.report.view', ['filename' => $fileName]);
    $message = "📊 *تقرير إقفال وردية - {$store->name}*\n"
             . "🗓️ التاريخ: " . $data['date'] . "\n"
             . "👤 المحاسب: " . $accountant->name . "\n"
             . "💰 كاش متوقع: " . number_format($expectedCashInHand, 2) . "\n"
             . "💵 كاش فعلي: " . number_format($actualCash, 2) . "\n"
             . "⚠️ الفرق: " . number_format($difference, 2) . "\n"
             . "📄 الرابط: " . $reportLink;

    $waUrl = "https://api.whatsapp.com/send?phone=" . $managerPhone . "&text=" . urlencode($message);

return redirect()->route('accountant.dashboard')->with([
    'success' => 'تم حفظ الموازنة بنجاح',
    'wa_url'  => $waUrl // تأكد أن هذا المتغير يحتوي على الرابط كاملاً
]);
}
private function saveFinalRecords($storeId, $accountantId, $data, $startTime, $endTime)
{
    // 1. حفظ الموازنة في جدول الموازنات اليومية (الذي يُحذف بعد 31 يوم كما اتفقنا)
    \App\Models\DailyBalance::create([
        'store_id' => $storeId,
        'accountant_id' => $accountantId,
        'system_sales_total' => $data['total_sales'],
        'system_cash_expected' => $data['actual_cash'] - $data['difference'], // المتوقع الأصلي
        'actual_cash_submitted' => $data['actual_cash'],
        'difference' => $data['difference'],
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    // 2. تسجيل الحدث في الـ Logs (المرجع الدائم للمالك)
    \App\Models\Log::create([
        'store_id' => $storeId,
        'actor_type' => 'App\Models\Accountant',
        'actor_id' => $accountantId,
        'action' => 'balance_done',
        'description' => "إقفال موازنة: مبيعات ({$data['total_sales']})، أجرة يد ({$data['labor_cost']})، فرق ({$data['difference']})",
        'details' => json_encode($data), // حفظ كل تفاصيل المصفوفة للرجوع لها مستقبلاً
        'ip' => request()->ip(),
    ]);

    // 3. تنظيف الموازنات التي مر عليها أكثر من شهر تلقائياً
    \App\Models\DailyBalance::where('store_id', $storeId)
        ->where('created_at', '<', now()->subDays(31))
        ->delete();
}
    private function formatOp($model, $type)
    {
        // محاولة جلب الاسم من العلاقات المختلفة
        $employeeName = optional($model->employee)->name
            ?? optional($model->person)->name
            ?? optional($model->accountant)->name
            ?? '—';

        $description = $model->description ?? $model->reason ?? $model->note ?? 'عملية نظام';
        $amount = $model->final_total ?? $model->amount ?? 0;

        return (object)[
            'type'        => $type,
            'employee'    => $employeeName,
            'description' => $description,
            'amount'      => $amount,
            'created_at'  => $model->created_at,
        ];
    }
}
