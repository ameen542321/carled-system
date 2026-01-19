@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">تفاصيل الفاتورة</h1>
        <p class="text-gray-400 mt-1">رقم الفاتورة: #{{ $invoice->id }}</p>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('invoices.index') }}"
           class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white">
            رجوع
        </a>

        <a href="{{ route('invoices.print', $invoice->id) }}"
           class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">
            طباعة
        </a>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-8">

    {{-- معلومات عامة --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div>
            <h2 class="text-gray-400 mb-1">العميل</h2>
            <p class="text-gray-200 text-lg">
                {{ $invoice->customer_name ?? 'عميل نقدي' }}
            </p>
        </div>

        <div>
            <h2 class="text-gray-400 mb-1">المحاسب</h2>
            <p class="text-gray-200 text-lg">{{ $invoice->accountant->name }}</p>
        </div>

        <div>
            <h2 class="text-gray-400 mb-1">التاريخ</h2>
            <p class="text-gray-300">{{ $invoice->created_at }}</p>
        </div>

        <div>
            <h2 class="text-gray-400 mb-1">طريقة الدفع</h2>
            <p class="text-gray-200 text-lg">
                @switch($invoice->payment_method)
                    @case('cash') نقدًا @break
                    @case('card') بطاقة @break
                    @case('transfer') تحويل بنكي @break
                    @default —
                @endswitch
            </p>
        </div>

        <div>
            <h2 class="text-gray-400 mb-1">الحالة</h2>
            @if($invoice->is_paid)
                <span class="px-3 py-1 rounded bg-green-700 text-white text-sm">مدفوعة</span>
            @else
                <span class="px-3 py-1 rounded bg-red-700 text-white text-sm">غير مدفوعة</span>
            @endif
        </div>

    </div>

    <hr class="border-gray-700">

    {{-- جدول المنتجات --}}
    <div>
        <h2 class="text-xl text-gray-200 mb-4 font-semibold">المنتجات</h2>

        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4">

            <table class="w-full text-right">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-700">
                        <th class="py-3">المنتج</th>
                        <th class="py-3">الكمية</th>
                        <th class="py-3">مبلغ البيع</th>
                    </tr>
                </thead>

                <tbody class="text-gray-300">

                    {{-- مثال (لاحقًا foreach) --}}
                    @foreach($invoice->items as $item)
                        <tr class="border-b border-gray-700">
                            <td class="py-3">{{ $item->product->name }}</td>
                            <td class="py-3">{{ $item->quantity }}</td>
                            <td class="py-3">{{ $item->final_price }} ريال</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

    <hr class="border-gray-700">

    {{-- الإجمالي النهائي --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 text-center md:col-span-3">
            <h3 class="text-gray-400 mb-2">الإجمالي النهائي</h3>
            <p class="text-3xl font-bold text-green-400">{{ $invoice->total }} ريال</p>
        </div>

    </div>

</div>

@endsection
