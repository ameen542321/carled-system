<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif; /* Snappy يفضل الخطوط الأساسية أو المحملة محلياً */
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #1a202c;
        }
        .invoice-wrapper {
            padding: 20px;
        }
        /* رأس الفاتورة باستخدام الجداول لضمان التموضع */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #111827;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .store-info h1 { margin: 0; font-size: 20px; }
        .store-details p { margin: 2px 0; font-size: 11px; color: #4b5563; }

        /* بيانات العميل والمركبة */
        .info-container {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            padding: 10px;
        }
        .info-label { font-size: 10px; color: #64748b; font-weight: bold; }
        .info-value { font-size: 13px; font-weight: bold; display: block; }

        /* جدول الأصناف */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #f1f5f9;
            padding: 10px;
            border-top: 2px solid #111827;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
        }

        /* المخلص المالي */
        .footer-section { width: 100%; }
        .notes-column { width: 55%; vertical-align: top; border-left: 2px solid #e2e8f0; padding-left: 10px; }
        .totals-column { width: 40%; vertical-align: top; }

        .total-line { width: 100%; margin-bottom: 5px; }
        .grand-total {
            border-top: 2px solid #111827;
            padding-top: 8px;
            font-weight: bold;
            font-size: 16px;
        }

        .bank-info {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px dashed #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="store-info">
                    <h1>{{ $invoice->sale->store->name }}</h1>
                    <div class="store-details">
                        <p>العنوان: {{ $invoice->sale->store->address ?? 'غير محدد' }}</p>
                        <p>السجل التجاري: {{ $invoice->sale->store->commercial_register ?? 'غير مسجل' }}</p>
                        <p>الهاتف: {{ $invoice->sale->store->phone ?? 'غير مسجل' }}</p>
                        <p>الرقم الضريبي: <strong>{{ $invoice->sale->store->tax_number ?? '3000XXXXXXXXXXX' }}</strong></p>
                    </div>
                </div>
            </td>
            <td style="width: 30%; text-align: left;">
                {{-- الباركود بصيغة SVG لضمان العمل بدون Imagick --}}
                {!! QrCode::size(85)->generate($invoice->zatca_qr_code) !!}
                <div style="font-weight: bold; font-size: 10px; margin-top: 5px;">فاتورة ضريبية مبسطة</div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 15px;">
        <span>رقم الفاتورة: <strong>#{{ $invoice->invoice_number }}</strong></span>
        <span style="margin-right: 30px;">التاريخ: <strong>{{ $invoice->created_at->format('Y/m/d H:i') }}</strong></span>
    </div>

    <div class="info-container">
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <span class="info-label">بيانات العميل</span><br>
                    <span class="info-value">{{ $invoice->customer_name }}</span>
                    <span style="font-size: 11px;">{{ $invoice->customer_phone }}</span>
                </td>
                <td style="width: 50%; text-align: left;">
                    <span class="info-label">بيانات المركبة</span><br>
                    <span class="info-value">{{ $invoice->vehicle_type }}</span>
                    <span style="color: #059669; font-weight: bold;">{{ $invoice->plate_number }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="text-align: right;">الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الضريبة</th>
                <th style="text-align: left;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->sale->items as $item)
            <tr>
                <td style="text-align: right;">{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($item->total * ($invoice->tax_rate / 100), 2) }}</td>
                <td style="text-align: left;">{{ number_format($item->total + ($item->total * ($invoice->tax_rate / 100)), 2) }}</td>
            </tr>
            @endforeach

            @if($invoice->sale->labor_total > 0)
            <tr>
                <td style="text-align: right;">{{ $invoice->sale->description ?? 'أجور يد وتركيب' }}</td>
                <td>1</td>
                <td>{{ number_format($invoice->sale->labor_total, 2) }}</td>
                <td>0.00</td>
                <td style="text-align: left;">{{ number_format($invoice->sale->labor_total, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <table style="width: 100%;">
        <tr>
            <td class="notes-column">
                <strong>ملاحظات:</strong><br>
                <div style="margin-top: 5px;">{{ $invoice->notes ?? 'لا توجد ملاحظات.' }}</div>
                <div style="margin-top: 15px; color: #94a3b8; font-size: 9px;">
                    * ضريبة القيمة المضافة تُطبق على قطع الغيار والمواد فقط.
                </div>
            </td>
            <td class="totals-column">
                <table style="width: 100%;">
                    <tr>
                        <td>المجموع الصافي:</td>
                        <td style="text-align: left;">{{ number_format($invoice->subtotal, 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>الضريبة ({{ $invoice->tax_rate }}%):</td>
                        <td style="text-align: left;">{{ number_format($invoice->tax_amount, 2) }} ر.س</td>
                    </tr>
                    <tr class="grand-total">
                        <td>الإجمالي:</td>
                        <td style="text-align: left;">{{ number_format($invoice->total_amount, 2) }} ر.س</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="bank-info">
        {{ $invoice->sale->store->bank_accounts_info ?? 'مصرف الراجحي: SA0000000000000000000000 | البنك الأهلي: SA0000000000000000000000' }}
    </div>

    <div style="margin-top: 15px; text-align: center; font-size: 10px; color: #94a3b8;">
        طريقة الدفع: {{ $invoice->sale->sale_type == 'cash' ? 'نقداً' : ($invoice->sale->sale_type == 'card' ? 'شبكة' : 'آجل') }}
        | المحاسب: {{ $invoice->sale->accountant->name }}
    </div>
</div>

</body>
</html>
