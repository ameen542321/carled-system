<?php

namespace App\Http\Controllers\Cashier;

use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\CreditSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class QuickSaleController extends Controller
{
    public function index()
    {
        return view('cashier.quick-sale.index');
    }

    public function creditPersons()
{
    $storeId = auth('accountant')->user()->store_id;

    return \App\Models\Employee::where('store_id', $storeId)
        ->select('id', 'name')
        ->get();
}





    public function submit(Request $request)
{
    // 1. التحقق من البيانات المدخلة
    $request->validate([
        'items'        => 'required|json',
        'labor_total'  => 'numeric|min:0',
        'tax_rate'     => 'integer|in:0,15',
        'paid_amount'  => 'numeric|min:0',
        'sale_type'    => 'required|in:cash,card,credit',
        'employee_id'  => 'required_if:sale_type,credit',
        'description'  => 'nullable|string',
        'has_invoice'  => 'nullable|in:0,1',
    ]);

    DB::beginTransaction();
    try {
        $items = json_decode($request->items, true);
        $totalProfit = 0;
        $productsTotal = 0;

        // 2. التحقق من توفر الكميات قبل البدء (منع البيع إذا الكمية صفر)
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || $product->quantity < $item['quantity']) {
                return redirect()->back()->with('error', "الكمية غير كافية للمنتج: " . ($product->name ?? 'غير معروف'));
            }

            $productsTotal += $item['total'];
            $itemProfit = ($item['price'] - ($product->cost_price ?? 0)) * $item['quantity'];
            $totalProfit += $itemProfit;
        }

        // حسابات المبالغ
        $totalProfit += $request->labor_total; // أجور اليد تضاف للربح بالكامل
        $taxValue = ($productsTotal * $request->tax_rate) / 100;
        $finalTotal = ($productsTotal + $taxValue) + $request->labor_total;
        $remaining = $finalTotal - $request->paid_amount;

        // 3. إنشاء سجل البيع الرئيسي
        $sale = Sale::create([
            'store_id'         => auth('accountant')->user()->store_id,
            'accountant_id'    => auth('accountant')->id(),
            'employee_id'      => $request->employee_id,
            'products_total'   => $productsTotal,
            'tax_rate'         => $request->tax_rate,
            'labor_total'      => $request->labor_total,
            'final_total'      => $finalTotal,
            'total'            => $finalTotal, // الحقل الذي كان يسبب خطأ Database
            'paid_amount'      => $request->paid_amount,
            'remaining_amount' => max(0, $remaining),
            'sale_type'        => $request->sale_type,
            'has_invoice'      => $request->has_invoice == 1, // حفظ حالة طلب الفاتورة
            'description'      => $request->description,
            'profit'           => $totalProfit,
        ]);

        // 4. خصم المخزون وحفظ الأصناف
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            $sale->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
                'total'      => $item['total'],
            ]);

            // خصم حقيقي من المخزون وتسجيل الحركة
            $product->decreaseStock(
                $item['quantity'],
                "بيع سريع - فاتورة #" . $sale->id,
                auth('accountant')->id()
            );
        }

        DB::commit();

        // 5. منطق التوجيه بناءً على زر "إنشاء فاتورة"
        if ($request->has_invoice == 1) {
            // التحويل لصفحة إنشاء الفاتورة (InvoiceController) لإكمال بيانات العميل
            return redirect()->route('accountant.quick-sale.invoice.create', ['sale' => $sale->id])
                             ->with('success', 'تم تسجيل البيع، يرجى إكمال بيانات الفاتورة الضريبية.');
        }

        // إذا لم يتم اختيار فاتورة، العودة للبيع السريع مباشرة
        return redirect()->back()->with('success', 'تمت عملية البيع بنجاح وتحديث المخزون.');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("QuickSale Error: " . $e->getMessage());
        return redirect()->back()->with('error', 'حدث خطأ تقني: ' . $e->getMessage());
    }
}









}
