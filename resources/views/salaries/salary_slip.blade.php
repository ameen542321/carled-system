<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف راتب - {{ $worker->name }}</title>

    <style>
        body {
            font-family: "Tajawal", sans-serif;
            background: white;
            color: #000;
            padding: 20px;
        }

        .slip-box {
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
            .slip-box {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>

<body>

<div class="slip-box">

    {{-- عنوان --}}
    <div class="center">
        <h2>كشف راتب</h2>
        <p>شهر: {{ $month }}</p>
    </div>

    <hr>

    {{-- معلومات العامل --}}
    <table>
        <tr>
            <th>اسم العامل</th>
            <td>{{ $worker->name }}</td>
        </tr>

        <tr>
            <th>تاريخ التعيين</th>
            <td>{{ $worker->hired_at }}</td>
        </tr>

        <tr>
            <th>الراتب الأساسي</th>
            <td>{{ $worker->salary }} ريال</td>
        </tr>
    </table>

    <hr>

    {{-- تفاصيل الراتب --}}
    <h3>تفاصيل الراتب</h3>

    <table>
        <thead>
            <tr>
                <th>البند</th>
                <th>القيمة</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>الراتب الأساسي</td>
                <td>{{ $worker->salary }} ريال</td>
            </tr>

            <tr>
                <td>إجمالي السحوبات</td>
                <td style="color: red;">{{ $withdrawals }} ريال</td>
            </tr>

            <tr>
                <td><strong>صافي الراتب</strong></td>
                <td style="color: green; font-weight: bold;">
                    {{ $net_salary }} ريال
                </td>
            </tr>

        </tbody>
    </table>

    <hr>

    {{-- ملاحظة --}}
    @if($note)
    <div class="totals">
        <div><strong>ملاحظة:</strong> {{ $note }}</div>
    </div>
    @endif

    {{-- الفوتر --}}
    <div class="footer">
        تم إصدار كشف الراتب بواسطة نظام Carled
        <br>
        التاريخ: {{ date('Y-m-d') }}
    </div>

</div>

<script>
    // الطباعة اختيارية — لا يتم الطباعة تلقائيًا
</script>

</body>
</html>
