@extends('dashboard.app')

@section('title', 'لوحة المحاسب')

@section('content')

{{-- العنوان --}}
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-white">لوحة المحاسب</h1>
        <p class="text-gray-400 text-sm mt-1">نظرة عامة على العمليات المالية لليوم</p>
    </div>
    <div class="text-right">
        <span class="text-xs text-gray-500 block"> التاريخ والوقت</span>
        <span class="text-white font-mono">{{ now()->format('Y-m-d H:i') }}</span>
    </div>
</div>

{{-- البطاقات --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    {{-- إضافة بيع --}}
    <a href="{{ route('accountant.quick-sale.index') }}" class="block group">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:border-blue-500 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">إضافة بيع جديد</p>
                    <h3 class="text-xl font-bold text-white mt-1">البيع السريع</h3>
                </div>
                <div class="bg-blue-500/15 text-blue-300 p-3 rounded-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-cart-plus text-xl"></i>
                </div>
            </div>
        </div>
    </a>

    {{-- إحصائية السحب --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عمليات السحب (اليوم)</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ $stats['withdrawals_count'] ?? 0 }}</h3>
            </div>
            <div class="bg-yellow-500/15 text-yellow-300 p-3 rounded-lg">
                <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
            </div>
        </div>
    </div>

    {{-- إحصائية المصروفات --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">إجمالي المصاريف</p>
                <h3 class="text-2xl font-bold text-red-400 mt-1">{{ number_format($stats['expenses_sum'] ?? 0, 2) }}</h3>
            </div>
            <div class="bg-red-500/15 text-red-300 p-3 rounded-lg">
                <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
            </div>
        </div>
    </div>

    {{-- بطاقة الموازنة والإقفال --}}
    <div x-data="{ openConfirm: false }" class="relative">
        <div @click="openConfirm = true" class="bg-indigo-900/40 border border-indigo-500/50 rounded-xl p-5 shadow cursor-pointer hover:bg-indigo-800/50 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-200 text-sm">الموازنة </p>
                    <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($totalSinceBalance, 2) }} <span class="text-xs">ريال</span></h3>
                </div>
                <div class="bg-white/10 text-white p-3 rounded-lg animate-pulse">
                    <i class="fa-solid fa-scale-balanced text-xl"></i>
                </div>
            </div>
        </div>

        {{-- نافذة التأكيد المحدثة (Modal) --}}
       <div x-show="openConfirm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4" x-cloak>

    <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 max-w-sm w-full shadow-2xl" @click.away="openConfirm = false">
        <div class="text-center mb-4">
            <h2 class="text-xl font-bold text-white">تأكيد إغلاق الحساب اليومي</h2>
            <p class="text-gray-400 text-[10px] mt-1 uppercase tracking-wider">ملخص الحساب النقدي  </p>
        </div>

        {{-- تفاصيل الحساب النقدي للوردية --}}
        <div class="mb-4 bg-gray-800/50 border border-gray-700 rounded-xl p-4 space-y-2">
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-400">إجمالي المبيعات:</span>
                <span class="text-white font-medium">{{ number_format($totalSinceBalance, 2) }} ريال</span>
            </div>

            @if($currentShiftExpenses > 0)
            <div class="flex justify-between items-center text-sm text-red-400">
                <span>المصاريف :</span>
                <span>- {{ number_format($currentShiftExpenses, 2) }} ريال</span>
            </div>
            @endif

            @if($currentShiftWithdrawals > 0)
            <div class="flex justify-between items-center text-sm text-yellow-500">
                <span>السحوبات :</span>
                <span>- {{ number_format($currentShiftWithdrawals, 2) }} ريال</span>
            </div>
            @endif

            <div class="pt-2 border-t border-gray-700 flex justify-between items-center">
                <span class="text-indigo-400 font-bold text-sm">المتوقع في الصندوق:</span>
                <span class="text-white font-black text-lg">
                    {{ number_format($totalSinceBalance - ($currentShiftExpenses + $currentShiftWithdrawals), 2) }}
                </span>
            </div>
        </div>

        <form action="{{ route('accountant.balance.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-gray-400 text-xs mb-2 block text-center">أدخل المبلغ النقدي الفعلي الموجود معك الآن:</label>
                <input type="number" step="0.01" name="actual_cash" required autofocus
                    class="w-full bg-gray-800 border-2 border-indigo-500/30 rounded-xl px-4 py-4 text-white text-3xl text-center focus:border-indigo-500 outline-none transition shadow-inner"
                    placeholder="0.00">
            </div>

            <div>
                <label class="text-gray-400 text-xs mb-1 block">ملاحظات العجز أو الزيادة (إن وجدت):</label>
                <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-3 py-2 text-white text-sm outline-none focus:border-indigo-500" placeholder="اكتب أي ملاحظة تخص الصندوق هنا..."></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="openConfirm = false" class="flex-1 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition font-semibold">إلغاء</button>
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold shadow-lg shadow-indigo-600/20 transition">تأكيد الإقفال</button>
            </div>
        </form>
    </div>
</div>
    </div>
</div>

{{-- أزرار الوصول السريع --}}
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mt-6">
    <a href="{{ route('accountant.pos.withdrawal.page') }}" class="p-3 bg-gray-800 border border-gray-700 rounded-lg text-center hover:bg-gray-750 transition">
        <i class="fa-solid fa-hand-holding-dollar text-yellow-500 mb-1 block"></i>
        <span class="text-xs text-gray-300">سحب</span>
    </a>
    <a href="{{ route('accountant.pos.expense.page') }}" class="p-3 bg-gray-800 border border-gray-700 rounded-lg text-center hover:bg-gray-750 transition">
        <i class="fa-solid fa-receipt text-red-400 mb-1 block"></i>
        <span class="text-xs text-gray-300">مصروف</span>
    </a>
    <a href="{{ route('accountant.pos.absence.page') }}" class="p-3 bg-gray-800 border border-gray-700 rounded-lg text-center hover:bg-gray-750 transition">
        <i class="fa-solid fa-user-xmark text-orange-400 mb-1 block"></i>
        <span class="text-xs text-gray-300">غياب</span>
    </a>
    <a href="{{ route('accountant.pos.debt.page') }}" class="p-3 bg-gray-800 border border-gray-700 rounded-lg text-center hover:bg-gray-750 transition">
        <i class="fa-solid fa-money-bill-transfer text-pink-400 mb-1 block"></i>
        <span class="text-xs text-gray-300">مديونية</span>
    </a>
    <a href="{{ route('accountant.pos.credit-sale.page') }}" class="p-3 bg-gray-800 border border-gray-700 rounded-lg text-center hover:bg-gray-750 transition">
        <i class="fa-solid fa-clock-rotate-left text-indigo-400 mb-1 block"></i>
        <span class="text-xs text-gray-300">آجل</span>
    </a>
    <a href="{{ route('accountant.pos.collection.page') }}" class="p-3 bg-gray-800 border border-gray-700 rounded-lg text-center hover:bg-gray-750 transition">
        <i class="fa-solid fa-money-check-dollar text-teal-400 mb-1 block"></i>
        <span class="text-xs text-gray-300">تحصيل</span>
    </a>
</div>

{{-- جدول آخر العمليات --}}
<div class="mt-10 bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-lg">
    <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
        <span class="text-yellow-400">🕘</span>
        آخر 10 عمليات تمت اليوم
    </h2>

    <div class="overflow-x-auto">
        @forelse($lastOperations as $op)
            <div class="grid grid-cols-5 gap-4 py-4 border-b border-gray-700/50 items-center hover:bg-white/5 transition px-2 rounded-lg">
                <div class="flex items-center gap-3">
                    @include('accountants.partials.op-icon', ['type' => $op->type])
                </div>
                <div class="text-gray-300 text-sm">
                    <span class="text-gray-500 text-xs block">بواسطة</span>
                    {{ $op->employee->name ?? '—' }}
                </div>
                <div class="text-gray-400 text-xs truncate max-w-[150px]">
                    {{ $op->description ?? '—' }}
                </div>
                <div class="text-white font-bold text-sm">
                    {{ $op->amount ? number_format($op->amount, 2) . ' ريال' : '—' }}
                </div>
                <div class="text-gray-500 text-xs text-left font-mono">
                    {{ $op->created_at->format('H:i:s') }}
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-gray-500 italic">لا توجد عمليات مسجلة اليوم حتى الآن.</div>
        @endforelse
    </div>
</div>
@if(session('wa_url'))
<script>
    // نستخدم JavaScript خالص (Vanilla JS) لضمان العمل حتى لو تعطلت مكتبة jQuery
    window.onload = function() {
        // 1. إخلاق أي مودال مفتوح
        if (typeof bootstrap !== 'undefined') {
            var myModal = document.getElementById('balanceModal'); // تأكد من الـ ID
            if (myModal) {
                var modal = bootstrap.Modal.getInstance(myModal);
                if (modal) modal.hide();
            }
        }

        // 2. استخدام SweetAlert2 مع التأكد من وجوده
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '✅ تم الإقفال بنجاح',
                text: 'اضغط على الزر لفتح واتساب وإرسال التقرير',
                icon: 'success',
                confirmButtonText: 'إرسال الآن 💬',
                confirmButtonColor: '#25D366',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // فتح الرابط (هنا المتصفح لن يحظره لأنها ضغطة زر مباشرة)
                    var url = "{!! session('wa_url') !!}";
                    window.open(url, '_blank');
                }
            });
        } else {
            // حل احتياطي إذا لم تكن مكتبة Swal محملة
            if(confirm('تم الإقفال بنجاح، هل تريد فتح واتساب لإرسال التقرير؟')) {
                window.open("{!! session('wa_url') !!}", '_blank');
            }
        }
    };
</script>
@endif
@endsection
