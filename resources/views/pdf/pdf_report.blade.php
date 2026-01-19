<div style="direction: rtl; font-family: 'Arial', sans-serif;">
    <h2 style="text-align: center;">تقرير إقفال الوردية المفصل</h2>

    <div style="background: #f8fafc; padding: 10px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong>المتجر:</strong> {{ $store_name }}</p>
        <p><strong>المحاسب:</strong> {{ $accountant }}</p>
        <p><strong>الفترة:</strong> من آخر إقفال حتى {{ $date }}</p>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background: #1e293b; color: white;">
            <th style="padding: 10px; border: 1px solid #ddd;">البيان</th>
            <th style="padding: 10px; border: 1px solid #ddd;">المبلغ (ريال)</th>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd;">مبيعات نقدية (كاش)</td>
            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ number_format($cash_sales, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #ddd;">مبيعات شبكة (بنك)</td>
            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ number_format($card_sales, 2) }}</td>
        </tr>
        <tr style="color: #dc2626;">
            <td style="padding: 10px; border: 1px solid #ddd;">إجمالي المصاريف والسحوبات</td>
            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">- {{ number_format($expenses + $withdrawals, 2) }}</td>
        </tr>
        <tr style="background: #f1f5f9; font-weight: bold;">
            <td style="padding: 10px; border: 1px solid #ddd;">المتوقع وجوده في الدرج (كاش)</td>
            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ number_format($expected_cash_in_hand, 2) }}</td>
        </tr>
        <tr style="background: #e0f2fe; font-weight: bold;">
            <td style="padding: 10px; border: 1px solid #ddd;">المبلغ المستلم فعلياً</td>
            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ number_format($actual_cash, 2) }}</td>
        </tr>
        <tr style="background: {{ $difference >= 0 ? '#f0fdf4' : '#fef2f2' }}; color: {{ $difference >= 0 ? '#166534' : '#991b1b' }};">
            <td style="padding: 10px; border: 1px solid #ddd;">العجز / الزيادة في الكاش</td>
            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">{{ number_format($difference, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 20px; font-size: 12px; color: #64748b;">
        * ملاحظة: مبيعات الشبكة ({{ number_format($card_sales, 2) }} ريال) يجب أن تكون قد وصلت لحساب البنك مباشرة.
    </div>
</div>
