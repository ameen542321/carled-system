@extends('dashboard.app')

@section('title', 'إصدار فاتورة ضريبية')

@section('content')
{{-- x-data المحاسبي: يحسب الضريبة على المنتجات فقط ويجمعها مع أجور اليد --}}
<div class="max-w-4xl mx-auto py-8 px-4" x-data="{
    productsNet: {{ $sale->products_total ?? 0 }},
    laborNet: {{ $sale->labor_total ?? 0 }},
    taxRate: {{ $sale->tax_rate ?? 0 }},

    // 1. الصافي: مجموع المنتج وشغل اليد بدون ضريبة
    get subtotal() {
        return (parseFloat(this.productsNet) + parseFloat(this.laborNet)).toFixed(2);
    },

    // 2. قيمة الضريبة: تُحسب على المنتجات فقط (100 * 15% = 15)
    get taxAmount() {
        return (parseFloat(this.productsNet) * (this.taxRate / 100)).toFixed(2);
    },

    // 3. الإجمالي النهائي: الصافي + ضريبة المنتجات (120 + 15 = 135)
    get finalTotal() {
        return (parseFloat(this.subtotal) + parseFloat(this.taxAmount)).toFixed(2);
    }
}">

    {{-- رأس الصفحة --}}
    <div class="flex items-center justify-between mb-8 text-right" dir="rtl">
        <div>
            <h1 class="text-3xl font-extrabold text-white">إصدار فاتورة ضريبية</h1>
            <p class="text-gray-400 mt-1 italic">ربط مع عملية البيع: #{{ $sale->id }}</p>
        </div>
        <div class="text-left">
            <span class="text-gray-400 block text-sm font-bold">المبلغ المستحق</span>
            <span class="text-3xl font-black text-green-500" x-text="finalTotal + ' ر.س'"></span>
        </div>
    </div>

    <form method="POST" action="{{ route('accountant.quick-sale.invoice.store', $sale->id) }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-right" dir="rtl">
        @csrf

        <div class="lg:col-span-2 space-y-6">

            {{-- 1. تفاصيل المنتجات (من جدول sale_items) --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl text-right">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2 border-b border-gray-800 pb-3">
                    <span class="text-yellow-500">📦</span> المنتجات والمواد (خاضعة للضريبة)
                </h2>

                <div class="space-y-3">
                    @forelse($sale->items as $item)
                        <div class="flex justify-between items-center bg-gray-800/30 p-4 rounded-xl border border-gray-800 transition-hover hover:bg-gray-800/50">
                            <div>
                                <span class="text-white font-bold block">
                                    {{ $item->product->name ?? 'منتج رقم #' . $item->product_id }}
                                </span>
                                <span class="text-gray-500 text-xs italic">
                                    الكمية: {{ $item->quantity }} × {{ number_format($item->price, 2) }} ر.س
                                </span>
                            </div>
                            <div class="text-blue-400 font-mono font-bold">
                                {{ number_format($item->total, 2) }} <small class="text-xs">ر.س</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm italic text-center py-4">لا توجد منتجات مسجلة.</p>
                    @endforelse
                </div>
            </div>

            {{-- 2. تفاصيل أجور اليد (معفاة من الضريبة حسب طلبك) --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl text-right">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2 border-b border-gray-800 pb-3">
                    <span class="text-blue-400">🛠️</span> أجور اليد والتركيب (غير خاضعة)
                </h2>

                @if($sale->labor_total > 0)
                <div class="p-4 bg-blue-900/10 border border-blue-900/20 rounded-xl">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-blue-400 font-bold block">وصف العمل</span>
                            <span class="text-gray-400 text-xs italic">{{ $sale->description ?? 'تركيب وفحص عام' }}</span>
                        </div>
                        <span class="text-blue-400 font-mono font-bold text-lg">
                            {{ number_format($sale->labor_total, 2) }} <small class="text-xs">ر.س</small>
                        </span>
                    </div>
                </div>
                @else
                <p class="text-gray-500 text-sm italic text-center">لا توجد أجور يد مسجلة.</p>
                @endif
            </div>

            {{-- 3. بيانات العميل والمركبة --}}
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl text-right">
    <h2 class="text-lg font-semibold text-white mb-6 flex items-center gap-2 border-b border-gray-800 pb-3">
        <span class="text-blue-500">👤</span> بيانات العميل والمركبة
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="space-y-2">
            <label class="text-sm text-gray-400 block italic mr-1">اسم العميل</label>
            <input type="text" name="customer_name" required placeholder="أدخل اسم العميل"
                   class="w-full bg-gray-800 border border-gray-700 focus:border-blue-500 text-white rounded-xl px-4 py-3 outline-none transition-all">
        </div>
        <div class="space-y-2">
            <label class="text-sm text-gray-400 block italic mr-1">رقم الهاتف</label>
            <input type="text" name="customer_phone" required placeholder="05xxxxxxxx"
                   class="w-full bg-gray-800 border border-gray-700 focus:border-blue-500 text-white rounded-xl px-4 py-3 text-left outline-none font-mono">
        </div>

        {{-- التعديل الجديد: الرقم الضريبي للعميل --}}
        <div class="space-y-2">
            <label class="text-sm text-yellow-500/80 block italic mr-1 font-bold">الرقم الضريبي للعميل (اختياري)</label>
            <input type="text" name="customer_tax_number" placeholder="3xxxxxxxxxxxxxx"
                   class="w-full bg-gray-800 border border-gray-700 focus:border-yellow-500 text-white rounded-xl px-4 py-3 text-left outline-none font-mono"
                   maxlength="15">
        </div>

        <div class="space-y-2">
            <label class="text-sm text-gray-400 block italic mr-1">نوع المركبة</label>
            <input type="text" name="vehicle_type" required placeholder="مثلاً: لاندكروزر 2024"
                   class="w-full bg-gray-800 border border-gray-700 focus:border-blue-500 text-white rounded-xl px-4 py-3 outline-none">
        </div>
        <div class="space-y-2">
            <label class="text-sm text-gray-400 block italic mr-1">رقم اللوحة</label>
            <input type="text" name="plate_number" required placeholder="أ ب ج 1234"
                   class="w-full bg-gray-800 border border-gray-700 focus:border-blue-500 text-white rounded-xl px-4 py-3 text-center font-bold outline-none font-sans">
        </div>

        <div class="space-y-2 md:col-span-2 border-t border-gray-800 pt-4 mt-2">
            <label class="text-sm text-yellow-500 block italic font-bold mr-1">📝 ملاحظات إضافية (تظهر في الفاتورة)</label>
            <textarea name="notes" rows="2" placeholder="اكتب أي ملاحظات أخرى هنا..."
                      class="w-full bg-gray-800 border border-gray-700 focus:border-yellow-500/50 text-white rounded-xl px-4 py-3 outline-none"></textarea>
        </div>
    </div>
</div>
        </div>

        {{-- الجانب الأيسر: الملخص المالي المحاسبي --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl sticky top-8 text-right">
                <h2 class="text-lg font-semibold text-white mb-6 border-b border-gray-800 pb-3">الملخص المالي</h2>

                <div class="mb-6 p-4 bg-blue-600/10 rounded-xl border border-blue-500/30">
                    <label class="text-xs text-blue-400 mb-1 block italic text-center font-bold">نسبة الضريبة</label>
                    <div class="text-3xl font-black text-white text-center" x-text="taxRate + '%'"></div>
                    <input type="hidden" name="tax_rate" :value="taxRate">
                </div>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between text-gray-400 border-b border-gray-800/50 pb-2">
                        <span>إجمالي الصافي:</span>
                        <span class="text-white font-bold font-mono" x-text="subtotal + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400 border-b border-gray-800/50 pb-2">
                        <div class="flex flex-col">
                            <span>قيمة الضريبة:</span>
                            <small class="text-[10px] text-gray-600 italic">(على المنتجات فقط)</small>
                        </div>
                        <span class="text-yellow-500 font-bold font-mono" x-text="taxAmount + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-xl font-black pt-4 bg-green-500/5 p-3 rounded-xl shadow-inner">
                        <span class="text-white">الإجمالي:</span>
                        <span class="text-green-500 font-mono" x-text="finalTotal + ' ر.س'"></span>
                    </div>
                </div>

                <button type="submit"
                        class="w-full mt-8 bg-green-600 hover:bg-green-500 text-white py-5 rounded-2xl font-black text-lg shadow-2xl transition-all flex items-center justify-center gap-3 active:scale-95">
                    <span>🖨️</span>
                    اعتماد وطباعة الفاتورة
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
