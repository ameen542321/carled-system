@extends('dashboard.app')

@section('title', 'تسجيل مديونية ')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">تسجيل مديونية للموظفين</h1>
    <p class="text-gray-400 text-sm mt-1">قم باختيار الموظف لإضافة أو تحصيل مديونية</p>
</div>

{{-- زر الرجوع --}}
<div class="mb-4">
    <a href="{{ route('accountant.dashboard') }}"
       class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
        ← الرجوع
    </a>
</div>

{{-- صندوق المحتوى --}}
<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 shadow-lg">

    {{-- جدول الموظفين --}}
    <table class="w-full text-gray-300">
        <thead>
            <tr class="border-b border-gray-700 text-gray-400 text-sm">
                <th class="py-2 font-medium">الاسم</th>
                <th class="py-2 font-medium">الدور</th>
                <th class="py-2 font-medium text-center">المديونية</th>
            </tr>
        </thead>

        <tbody>
            @foreach($people as $emp)
                @php
                    $hasDebt = $emp->debts()->where('amount', '>', 0)->exists();
                @endphp

                <tr class="border-b border-gray-700 hover:bg-gray-750 transition">

                    {{-- الاسم --}}
                    <td class="py-3 text-white font-semibold">
                        {{ $emp->name }}
                    </td>

                    {{-- الدور --}}
                     <td class="py-3">
    @if($emp->role === 'accountant')
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-600 text-white">
            محاسب
        </span>
    @else
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-700 text-gray-300">
            موظف
        </span>
    @endif
</td>

                    {{-- زر المديونية --}}
                    <td class="py-3 text-center">
                        <button
                            onclick="openDebtModal({{ $emp->id }}, '{{ $emp->name }}', {{ $hasDebt ? 'true' : 'false' }})"
                            class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-1.5 rounded-lg text-sm shadow">
                            {{ $hasDebt ? 'إضافة / تحصيل' : 'إضافة' }}
                        </button>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

</div>

{{-- ============================= --}}
{{-- مودال المديونية الرئيسي --}}
{{-- ============================= --}}
<div id="debtModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm">

    <div class="bg-gray-800 p-6 rounded-xl w-full max-w-md border border-gray-700 shadow-2xl">

        <h2 class="text-xl font-bold text-white mb-4">
            الموظف: <span id="empName" class="text-pink-400"></span>
        </h2>

        <form id="debtForm" method="POST">
            @csrf

            {{-- مبلغ المديونية --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">مبلغ المديونية</label>
                <input type="number" name="amount" step="0.01"
                       class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-pink-500">
            </div>

            {{-- التاريخ --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">التاريخ</label>
                <input type="date" name="date" value="{{ date('Y-m-d') }}"
                       class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-pink-500">
            </div>

            {{-- الوصف --}}
            <div class="mb-4">
                <label class="text-gray-300 text-sm">الوصف (اختياري)</label>
                <textarea name="description"
                          class="w-full bg-gray-700 text-white rounded-lg p-2 mt-1 focus:ring focus:ring-pink-500"
                          rows="3"></textarea>
            </div>

            {{-- أزرار الإضافة فقط --}}
            <div id="addOnly" class="hidden">
                <button class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg w-full shadow">
                    حفظ المديونية
                </button>
            </div>

            {{-- أزرار الإضافة + التحصيل --}}
            <div id="debtActions" class="hidden space-y-3">

                <button type="submit"
                        class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg w-full shadow">
                    إضافة مديونية
                </button>

                <button type="button"
                        onclick="openCollectModal()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full shadow">
                    تحصيل
                </button>

            </div>

            <button type="button"
                    onclick="closeDebtModal()"
                    class="mt-3 bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg w-full shadow">
                إغلاق
            </button>

        </form>

    </div>
</div>

{{-- ============================= --}}
{{-- مودال التحصيل --}}
{{-- ============================= --}}
<div id="collectModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm">

    <div class="bg-gray-800 p-6 rounded-xl w-full max-w-lg border border-gray-700 shadow-2xl">

        <h2 class="text-xl font-bold text-white mb-4">
            تحصيل مديونية الموظف: <span id="collectEmpName" class="text-blue-400"></span>
        </h2>

        <div id="debtsList" class="space-y-3 text-gray-300">
            <p class="text-gray-400">سيتم تحميل المديونيات...</p>
        </div>

        <button type="button"
                onclick="closeCollectModal()"
                class="mt-4 bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg w-full shadow">
            إغلاق
        </button>

    </div>
</div>

{{-- سكربت --}}
<script>
let currentEmpId = null;

function openDebtModal(empId, empName, hasDebt) {
    currentEmpId = empId;
    document.getElementById('empName').innerText = empName;

    const routeTemplate = "{{ route('accountant.pos.debt.store', ['employee' => 'ID']) }}";
    document.getElementById('debtForm').action = routeTemplate.replace('ID', empId);

    if (hasDebt) {
        document.getElementById('debtActions').classList.remove('hidden');
        document.getElementById('addOnly').classList.add('hidden');
    } else {
        document.getElementById('addOnly').classList.remove('hidden');
        document.getElementById('debtActions').classList.add('hidden');
    }

    document.getElementById('debtModal').classList.remove('hidden');
}

function closeDebtModal() {
    document.getElementById('debtModal').classList.add('hidden');
}

function openCollectModal() {
    document.getElementById('collectModal').classList.remove('hidden');
    document.getElementById('collectEmpName').innerText =
        document.getElementById('empName').innerText;

    const url = "{{ route('accountant.debts.list', ['id' => 'EMP_ID']) }}".replace('EMP_ID', currentEmpId);

    fetch(url)
        .then(res => res.json())
        .then(data => {
            let html = '';

            if (data.length === 0) {
                html = '<p class="text-gray-400">لا توجد مديونيات لهذا الموظف.</p>';
            } else {
                data.forEach(d => {
                    html += `
                        <div class="bg-gray-700 p-3 rounded-lg space-y-2">

                            <div class="flex justify-between">
                                <div>
                                    <div class="text-white font-semibold">${d.amount} ريال</div>
                                    <div class="text-gray-400 text-sm">${d.description ?? 'بدون وصف'}</div>
                                    <div class="text-gray-500 text-xs">${d.date}</div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <button onclick="collectFull(${d.id})"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                        تحصيل كامل
                                    </button>

                                    <button onclick="togglePartial(${d.id})"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                        تحصيل جزئي
                                    </button>
                                </div>
                            </div>

                            <div id="partial-${d.id}" class="hidden mt-2">
                                <input type="number" id="partialAmount-${d.id}"
                                       placeholder="أدخل مبلغ التحصيل"
                                       class="w-full bg-gray-600 text-white p-2 rounded mb-2">

                                <button onclick="collectPartial(${d.id})"
                                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded w-full">
                                    تأكيد التحصيل الجزئي
                                </button>
                            </div>

                        </div>
                    `;
                });
            }

            document.getElementById('debtsList').innerHTML = html;
        });
}

function togglePartial(id) {
    document.getElementById(`partial-${id}`).classList.toggle('hidden');
}

function collectFull(id) {
    window.location.href = "{{ url('accountant/debt/collect/full') }}/" + id;
}

function collectPartial(id) {
    const amount = document.getElementById(`partialAmount-${id}`).value;

    if (!amount || amount <= 0) {
        alert("الرجاء إدخال مبلغ صحيح");
        return;
    }

    window.location.href = "{{ url('accountant/debt/collect/partial') }}/" + id + "/" + amount;
}

function closeCollectModal() {
    document.getElementById('collectModal').classList.add('hidden');
}

function collect(id) {
    window.location.href = "{{ url('accountant/debt/collect') }}/" + id;
}
</script>

{{-- ============================= --}}
{{-- آخر 10 عمليات مديونية --}}
{{-- ============================= --}}
<div class="mt-10 bg-gray-900 border border-gray-700 rounded-xl p-5">


    <h2 class="text-xl font-bold text-white mb-4">  عمليات المديونية</h2>

    @if($lastDebts->count() == 0)
        <p class="text-gray-400">لا توجد عمليات مسجلة.</p>
    @else
        <div class="space-y-3">

          @forelse($lastDebts as $op)
    <div class="bg-gray-800 rounded-lg p-4 mb-3 shadow-md hover:bg-gray-750 transition">

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">

            {{-- الموظف --}}
            <div class="flex items-center gap-2 text-gray-300">
                <span class="text-blue-400 text-lg">👤</span>
                <span class="font-semibold">{{ $op->person->name ?? 'غير معروف' }}</span>
            </div>

            {{-- المبلغ --}}
            @php
                $isAdd = $op->amount > 0;
                $amountColor = $isAdd ? 'text-pink-400' : 'text-green-400';
                $amountIcon  = $isAdd ? '➕' : '✔️';
            @endphp

            <div class="flex items-center gap-2 {{ $amountColor }} font-bold text-lg">
                <span>{{ $amountIcon }}</span>
                {{ number_format($op->amount, 2) }} ريال
            </div>

            {{-- التاريخ --}}
            <div class="flex items-center gap-2 text-gray-400 text-sm">
                <span>📅</span>
                {{ $op->date }}
            </div>

        </div>

        {{-- الوصف إن وجد --}}
        @if(!empty($op->description))
            <div class="mt-3 flex items-start gap-2 text-gray-300 text-sm leading-relaxed">
                <span class="text-yellow-400">📝</span>
                <p>{{ $op->description }}</p>
            </div>
        @endif

    </div>
@empty
    <p class="text-gray-400 text-sm text-center py-4">
        لا توجد عمليات مديونية حتى الآن.
    </p>
@endforelse


        </div>
    @endif

</div>

@endsection
