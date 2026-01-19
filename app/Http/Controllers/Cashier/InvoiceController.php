<?php

namespace App\Http\Controllers\Cashier;

use App\Models\Sale;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
    use Barryvdh\Snappy\Facades\SnappyPdf as PDF; // تأكد من وجود هذا الـ Alias

class InvoiceController extends Controller
{
    public function create(Sale $sale)
    {
        // جلب العناصر المرتبطة بالبيع لضمان عرضها في صفحة الإنشاء
        $sale->load('items.product');
        return view('cashier.quick-sale.invoice-create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'vehicle_type'   => 'required|string|max:255',
            'plate_number'   => 'required|string|max:255',
            'tax_number'     => 'nullable|string|max:20', // هذا هو الرقم الضريبي للعميل
            'notes'          => 'nullable|string',
        ]);

        // تأمين ترقيم الفواتير
        $invoiceNumber = DB::transaction(function () {
            $lastNumber = Invoice::lockForUpdate()->max('invoice_number');
            return $lastNumber ? ($lastNumber + 1) : 1001;
        });

        // --- الحسابات المالية الدقيقة (الضريبة على المنتجات فقط) ---
        $productsTotal = $sale->products_total; // صافي المنتجات
        $laborTotal    = $sale->labor_total;    // صافي أجور اليد
        $taxRate       = $sale->tax_rate;       // نسبة الضريبة (مثلاً 15)

        // 1. حساب قيمة الضريبة على المنتجات فقط
        $taxAmount = round($productsTotal * ($taxRate / 100), 2);

        // 2. الصافي (مجموع المنتج + اليد بدون ضريبة)
        $subtotal = $productsTotal + $laborTotal;

        // 3. الإجمالي النهائي (الصافي + الضريبة المحسوبة)
        $finalTotal = $subtotal + $taxAmount;

        $invoice = Invoice::create([
            'sale_id'        => $sale->id,
            'invoice_number' => $invoiceNumber,
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'vehicle_type'   => $request->vehicle_type,
            'plate_number'   => $request->plate_number,
            'tax_number'     => $request->tax_number, // حفظ الرقم الضريبي للعميل هنا
            'notes'          => $request->notes,
            'subtotal'       => $subtotal,
            'tax_rate'       => $taxRate,
            'tax_amount'     => $taxAmount,
            'total_amount'   => $finalTotal,
            'status'         => 'printed',
        ]);

        // تحديث حالة عملية البيع لتوضيح أنها مفوترة
        $sale->update(['has_invoice' => true]);

        // ملاحظة: تأكد من صحة مسار الـ redirect حسب الـ Route المبرمج عندك
        return redirect()->route('accountant.quick-sale.invoice.print', $invoice->id);
    }

    public function print(Invoice $invoice)
    {
        // جلب بيانات البيع والمنتجات المرتبطة بالفاتورة للطباعة
        $invoice->load(['sale.items.product', 'sale.store']);
        return view('cashier.quick-sale.invoice-print', compact('invoice'));
    }

public function downloadPDF(Invoice $invoice)
{
    $invoice->load(['sale.items.product', 'sale.store']);

    return PDF::loadView('pdf.invoice-pdf', compact('invoice')) // تم التغيير هنا
        ->setOption('encoding', 'utf-8')
        ->setOption('enable-local-file-access', true)
        ->setOption('margin-top', '10mm')
        ->setOption('margin-bottom', '10mm')
        ->download('فاتورة-'.$invoice->invoice_number.'.pdf');
}
}
