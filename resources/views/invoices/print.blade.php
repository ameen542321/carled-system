<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة #{{ $invoice->id }}</title>

    <style>
        body {
            font-family: "Tajawal", sans-serif;
            background: white;
            color: #000;
            padding: 20px;
        }

        .invoice-box {
            max-width: 700px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 25px;
            border-radius: 8px;
        }

        h2, h3 {
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: right;
        }

        .totals {
            margin-top: 20px;
        }

        .totals div {
            margin-bottom: 8px;
            font-size: 18px;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #555;
        }

        @media print {
            body {
                padding: 0;
            }
            .invoice-box {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>

<body>

<div class="invoice-box">

    {{-- عنوان --}}
    <div class="center">
        <h2>فاتورة مبيعات</h2>
        <p>رقم الفاتورة: #{{ $invoice->id }}</p>
    </div>

    <hr>

    {{-- معلومات عامة --}}
    <table>
        <tr>
            <th>العميل</th>
            <td>{{ $invoice->customer_name ?? 'عميل نقدي' }}</td>
        </tr>

        <tr>
            <th>المحاسب</th>
            <td>{{ $invoice->accountant->name }}</td>
        </tr>

        <tr>
            <th>التاريخ</th>
            <td>{{ $invoice->created_at }}</td>
        </tr>

        <tr>
            <th>طريقة الدفع</th>
            <td>
                @switch($invoice->payment_method)
                    @case('cash') نقدًا @break
                    @case('card') بطاقة @break
                    @case('transfer') تحويل بنكي @break
                    @default —
                @endswitch
            </td>
        </tr>
    </table>

    <hr>

    {{-- جدول المنتجات --}}
    <h3>المنتجات</h3>

    <table>
        <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>مبلغ البيع</th>
            </tr>
        </thead>

        <tbody>

            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->final_price }} ريال</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    {{-- الإجمالي النهائي --}}
    <div class="totals">
        <div><strong>الإجمالي النهائي: {{ $invoice->total }} ريال</strong></div>
    </div>

    <hr>

    {{-- الفوتر --}}
    <div class="footer">
        شكراً لتعاملكم معنا
        <br>
        تم إنشاء الفاتورة بواسطة نظام Carled
    </div>

</div>

<script>
    // الطباعة اختيارية — لا يتم الطباعة تلقائيًا
</script>

</body>
</html>
