<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة ضريبية #{{ $invoice->invoice_number }}</title>
    <style>
      

        body {
            font-family: 'Amiri', 'Cairo', sans-serif; /* تأكد من استخدام خط يدعم العربية */
            direction: rtl;
            text-align: right;
        }

        .invoice-wrapper {
            max-width: 850px;
            margin: 0 auto;
            border: 1px solid #d1d5db;
            padding: 40px;
            position: relative;
        }

        /* رأس الفاتورة */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .store-info h1 {
            margin: 0;
            font-size: 24px;
            color: #111827;
        }

        .store-details {
            font-size: 12px;
            color: #4b5563;
            margin-top: 5px;
        }

        .qr-section {
            text-align: left;
        }

        .qr-box {
            width: 100px;
            height: 100px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
        }

        /* صف بيانات العميل والمركبة */
        .customer-vehicle-row {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px 25px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .info-block {
            display: flex;
            flex-direction: column;
        }

        .info-block.customer {
            text-align: right;
        }

        .info-block.vehicle {
            text-align: left;
        }

        .info-label {
            font-size: 11px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        /* الجدول */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        table th {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 10px;
            font-size: 13px;
            border-top: 2px solid #111827;
            border-bottom: 1px solid #e2e8f0;
        }

        table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }

        /* المخلص المالي */
        .footer-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .notes-area {
            width: 50%;
            font-size: 12px;
            border-right: 3px solid #e2e8f0;
            padding-right: 15px;
        }

        .totals-area {
            width: 35%;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }

        .grand-total-line {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding: 10px 0;
            border-top: 2px solid #111827;
            font-weight: bold;
            font-size: 18px;
            color: #111827;
        }

        /* الحسابات البنكية سطر صغير */
        .bank-info-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #111827;
            color: #fff;
            padding: 10px 25px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        @media print {
            .print-btn { display: none; }
            body { padding: 0; }
            .invoice-wrapper { border: none; max-width: 100%; }
        }
        @media print {
    .no-print {
        display: none !important;
    }
    body {
        background-color: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
    </style>
</head>
<body>
<div class="no-print" dir="rtl" style="background: #111827; padding: 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #374151; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
        {{-- زر العودة لصفحة البيع السريع --}}
        <a href="{{ route('accountant.quick-sale.index') }}"
           style="background-color: #374151; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; transition: 0.3s;">
           ← العودة للبيع السريع
        </a>

        <a href="{{ route('accountant.dashboard') }}"
           style="background-color: #1f2937; color: #9ca3af; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
           الرئيسية
        </a>
    </div>
<a href="{{ route('accountant.quick-sale.invoice.pdf', $invoice->id) }}"
   style="background-color: #059669; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; margin-right: 10px;">
   📥 تحميل PDF
</a>
    {{-- زر تفعيل أمر الطباعة يدوياً --}}
    <button onclick="window.print()"
            style="background-color: #2563eb; color: white; padding: 10px 25px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; font-size: 14px;">
            🖨️ طباعة الفاتورة الآن
    </button>
</div>
    <div class="invoice-wrapper">

        <div class="header-section">
            <div class="store-info">
                <h1>{{ $invoice->sale->store->name }}</h1>
                <div class="store-details">
                    <p>العنوان: {{ $invoice->sale->store->address ?? 'غير محدد' }}</p>
                    <p>السجل التجاري: {{ $invoice->sale->store->commercial_register ?? 'غير مسجل' }}</p>
                    <p>الهاتف: {{ $invoice->sale->store->phone ?? 'غير مسجل' }}</p>
                    <p>الرقم الضريبي: <strong>3000XXXXXXXXXXX</strong></p>
                </div>
            </div>
            <div class="qr-section">
                <div class="qr-box">
                    {!! QrCode::size(90)->generate($invoice->zatca_qr_code) !!}
                    </div>
                <p style="font-size: 11px; margin-top: 5px; font-weight: bold;">فاتورة ضريبية مبسطة</p>
            </div>
        </div>

        <div style="margin-bottom: 20px; font-size: 13px;">
            <span>رقم الفاتورة: <strong>#{{ $invoice->invoice_number }}</strong></span>
            <span style="margin-right: 20px;">التاريخ: <strong>{{ $invoice->created_at->format('Y/m/d H:i') }}</strong></span>
        </div>

        <div class="customer-vehicle-row">
            <div class="info-block customer">
                <span class="info-label">بيانات العميل</span>
                <span class="info-value">{{ $invoice->customer_name }}</span>
                <span style="font-size: 12px; color: #64748b;">{{ $invoice->customer_phone }}</span>
                @if($invoice->tax_number)
                    <span style="font-size: 11px; color: #64748b;">رقم ضريبي: {{ $invoice->tax_number }}</span>
                @endif
            </div>

            <div class="info-block vehicle">
                <span class="info-label">بيانات المركبة</span>
                <span class="info-value">{{ $invoice->vehicle_type }}</span>
                <span style="font-size: 14px; color: #059669; font-weight: bold;">{{ $invoice->plate_number }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="text-align: right;">الوصف</th>
                    <th style="text-align: center;">الكمية</th>
                    <th style="text-align: center;">سعر الوحدة</th>
                    <th style="text-align: center;">الضريبة</th>
                    <th style="text-align: left;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: center;">{{ number_format($item->price, 2) }}</td>
                    <td style="text-align: center;">{{ number_format($item->total * ($invoice->tax_rate / 100), 2) }}</td>
                    <td style="text-align: left;">{{ number_format($item->total + ($item->total * ($invoice->tax_rate / 100)), 2) }}</td>
                </tr>
                @endforeach

                @if($invoice->sale->labor_total > 0)
                <tr>
                    <td>{{ $invoice->sale->description ?? 'أجور يد وتركيب' }}</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: center;">{{ number_format($invoice->sale->labor_total, 2) }}</td>
                    <td style="text-align: center;">0.00</td>
                    <td style="text-align: left;">{{ number_format($invoice->sale->labor_total, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="footer-flex">
            <div class="notes-area">
                <strong>ملاحظات:</strong><br>
                {{ $invoice->notes ?? 'لا توجد ملاحظات.' }}
                <div style="margin-top: 10px; color: #94a3b8; font-size: 10px;">
                    * ضريبة القيمة المضافة تُطبق على قطع الغيار والمواد فقط.
                </div>
            </div>

            <div class="totals-area">
                <div class="total-line">
                    <span>المجموع الصافي:</span>
                    <span>{{ number_format($invoice->subtotal, 2) }} ر.س</span>
                </div>
                <div class="total-line">
                    <span>الضريبة ({{ $invoice->tax_rate }}%):</span>
                    <span>{{ number_format($invoice->tax_amount, 2) }} ر.س</span>
                </div>
                <div class="grand-total-line">
                    <span>الإجمالي:</span>
                    <span>{{ number_format($invoice->total_amount, 2) }} ر.س</span>
                </div>
            </div>
        </div>

        <div class="bank-info-footer">
            {{ $invoice->sale->store->bank_accounts_info ?? 'مصرف الراجحي: SA0000000000000000000000 | البنك الأهلي: SA0000000000000000000000' }}
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px;">
            طريقة الدفع: {{ $invoice->sale->sale_type == 'cash' ? 'نقداً' : ($invoice->sale->sale_type == 'card' ? 'شبكة' : 'آجل') }} | المحاسب: {{ $invoice->sale->accountant->name }}
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">طباعة 🖨️</button>

</body>
</html>
