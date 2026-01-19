@extends('dashboard.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="p-6 space-y-10">

    {{-- ========================================================= --}}
    {{--  القسم الأول: الهيدر الاحترافي --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white">مرحباً، {{ $user->name }}</h1>
            <p class="text-gray-400 mt-1">نظرة عامة ذكية على أداء متاجرك.</p>
        </div>

        <div class="flex flex-col items-start md:items-end gap-2">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-900/40 text-indigo-300 text-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                خطة الاشتراك: {{ $user->plan->name ?? 'بدون خطة' }}
            </span>

            @if($daysLeft !== null)
                <span class="px-3 py-1 rounded-lg text-xs
                    @if($daysLeft > 3) bg-emerald-900/40 text-emerald-300
                    @elseif($daysLeft >= 0) bg-yellow-900/40 text-yellow-300
                    @else bg-red-900/40 text-red-300 @endif">
                    @if($daysLeft > 0)
                        متبقي {{ $daysLeft }} يوم
                    @elseif($daysLeft == 0)
                        ينتهي اليوم
                    @else
                        منتهي منذ {{ abs($daysLeft) }} يوم
                    @endif
                </span>
            @endif
        </div>
    </div>

    {{-- ========================================================= --}}
    {{--  القسم الثاني: التنبيهات الذكية --}}
    {{-- ========================================================= --}}
    <div class="space-y-3">

        @if($salesToday == 0)
            <div class="alert-box bg-yellow-900/40 border-yellow-700 text-yellow-200">
                ⚠️ لا توجد مبيعات اليوم حتى الآن
            </div>
        @endif

        @if($expensesMonth > $salesMonth)
            <div class="alert-box bg-red-900/40 border-red-700 text-red-200">
                🔥 مصروفات هذا الشهر أعلى من المبيعات بنسبة
                {{ number_format(($expensesMonth / max($salesMonth,1)) * 100, 1) }}%
            </div>
        @endif

        @if($creditLate > 0)
            <div class="alert-box bg-orange-900/40 border-orange-700 text-orange-200">
                ⚠️ لديك {{ $creditLate }} مديونيات متأخرة لأكثر من 30 يوم
            </div>
        @endif

    </div>


    {{-- ========================================================= --}}
    {{--  القسم الرابع: الإحصائيات العامة (دمج بين الداشبوردين) --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- مبيعات اليوم --}}
        <x-stat-card title="مبيعات اليوم" value="{{ number_format($salesToday) }}" color="emerald" />

        {{-- مصروفات اليوم --}}
        <x-stat-card title="مصروفات اليوم" value="{{ number_format($expensesToday) }}" color="red" />

        {{-- صافي الربح اليوم --}}
        <x-stat-card title="صافي الربح اليوم"
            value="{{ number_format($profitToday) }}"
            color="{{ $profitToday >= 0 ? 'emerald' : 'red' }}" />

        {{-- عدد المتاجر --}}
        <x-stat-card title="عدد المتاجر" value="{{ $stores->count() }}" color="indigo" />

    </div>

    {{-- الصف الثاني --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        <x-stat-card title="مبيعات الشهر" value="{{ number_format($salesMonth) }}" color="emerald" />
        <x-stat-card title="مصروفات الشهر" value="{{ number_format($expensesMonth) }}" color="red" />
        <x-stat-card title="صافي الربح الشهر"
            value="{{ number_format($profitMonth) }}"
            color="{{ $profitMonth >= 0 ? 'emerald' : 'red' }}" />
        <x-stat-card title="عدد الموظفين" value="{{ $employeesCount }}" color="yellow" />

    </div>

    {{-- ========================================================= --}}
    {{--  القسم الخامس: تحليل المديونيات --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-stat-card title="مديونيات مفتوحة" value="{{ $creditOpen }}" color="yellow" />
        <x-stat-card title="مديونيات مسددة" value="{{ $creditClosed }}" color="emerald" />
        <x-stat-card title="مديونيات متأخرة" value="{{ $creditLate }}" color="red" />
    </div>

    {{-- ========================================================= --}}
    {{--  القسم السادس: المخطط الذكي --}}
    {{-- ========================================================= --}}
    <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
        <p class="text-sm font-semibold text-white mb-3">أداء آخر 14 يوم</p>
        <canvas id="smartChart" class="w-full h-64"></canvas>
    </div>

    {{-- ========================================================= --}}
    {{--  القسم السابع: آخر العمليات --}}
    {{-- ========================================================= --}}
    <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
    <p class="text-sm font-semibold text-white mb-3">آخر العمليات</p>

    <div class="space-y-4 max-h-72 overflow-y-auto custom-scrollbar">

        @forelse ($activities as $activity)
            @php
                $store = $activity->store;
                $employeeName = null;

                // استخراج اسم الموظف من الوصف إذا كان موجودًا
                if (preg_match('/الْمُوَظَّف\s+([^\s]+)/u', $activity->description, $matches)) {
                    $employeeName = $matches[1];
                }
            @endphp

            <div class="border-b border-gray-800 pb-3 last:border-none">

                {{-- اسم المتجر --}}
                <p class="text-xs text-emerald-400 font-semibold">
                    {{ $store->name ?? 'متجر غير معروف' }}
                </p>

                {{-- اسم الموظف إن وجد --}}
                @if($employeeName)
                    <p class="text-xs text-gray-400">
                        الموظف: {{ $employeeName }}
                    </p>
                @endif

                {{-- وصف العملية --}}
                <p class="text-xs text-gray-300 mt-1 leading-relaxed">
                    {{ $activity->description }}
                </p>

                {{-- الوقت --}}
                <p class="text-[11px] text-gray-500 mt-1">
                    {{ $activity->created_at->format('Y-m-d H:i') }}
                </p>
            </div>

        @empty
            <p class="text-xs text-gray-500">لا توجد عمليات مسجلة.</p>
        @endforelse

    </div>
</div>



</div>

{{-- ========================================================= --}}
{{--  سكربت المخطط --}}
{{-- ========================================================= --}}
<script>
(function () {
    const labels   = @json($chartLabels);
    const sales    = @json($chartSales);
    const expenses = @json($chartExpenses);
    const credit   = @json($chartCredit);

    const canvas = document.getElementById('smartChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    function drawChart() {
        const width  = canvas.width  = canvas.clientWidth  * window.devicePixelRatio;
        const height = canvas.height = canvas.clientHeight * window.devicePixelRatio;

        ctx.clearRect(0, 0, width, height);
        ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

        const margin = { top: 20, right: 10, bottom: 30, left: 40 };
        const innerWidth  = canvas.clientWidth  - margin.left - margin.right;
        const innerHeight = canvas.clientHeight - margin.top  - margin.bottom;

        const maxValue = Math.max(
            10,
            Math.max(...sales),
            Math.max(...expenses),
            Math.max(...credit)
        );

        const stepX = innerWidth / Math.max(labels.length - 1, 1);

        function yScale(value) {
            return margin.top + innerHeight - (value / maxValue) * innerHeight;
        }

        function drawLine(data, color) {
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.beginPath();
            data.forEach((v, i) => {
                const x = margin.left + i * stepX;
                const y = yScale(v);
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });
            ctx.stroke();
        }

        drawLine(sales,    '#34d399'); // مبيعات
        drawLine(expenses, '#f87171'); // مصروفات
        drawLine(credit,   '#60a5fa'); // مديونيات
    }

    drawChart();
    window.addEventListener('resize', drawChart);
})();
</script>

@endsection
