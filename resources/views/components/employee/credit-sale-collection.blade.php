@props(['employee', 'modalId' => 'creditSaleCollectionModal'])

<div id="{{ $modalId }}"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">

    <div class="w-full max-w-3xl px-4">
        <div class="bg-gray-900 border border-gray-800 shadow-xl rounded-xl p-8">

            {{-- العنوان + زر الإغلاق --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-100">
                    تحصيل البيع الآجل — {{ $employee->name }}
                </h2>

                <button type="button"
                        onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition">
                    إغلاق
                </button>
            </div>

            {{-- المحتوى --}}
            <div class="space-y-6">

                @php
                    $pendingSales = $employee->creditSales()->where('status', 'pending')->get();
                @endphp

                @if($pendingSales->isEmpty())

                    <div class="text-center py-10 text-gray-400 bg-gray-800 border border-gray-700 rounded-lg">
                        لا توجد عمليات بيع آجل غير محصّلة.
                    </div>

                @else

                    <div class="space-y-6">

                        @foreach($pendingSales as $sale)

                            {{-- بطاقة العملية --}}
                            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">

                                {{-- معلومات العملية --}}
                                <div class="flex justify-between items-center">
                                    <div class="space-y-1">
                                        <p class="text-gray-100 font-semibold text-lg">
                                            المتبقي: {{ number_format($sale->remaining_amount, 2) }} ريال
                                        </p>

                                        <p class="text-sm text-gray-400">
                                            التاريخ: {{ $sale->date }}
                                        </p>

                                        @if($sale->description)
                                            <p class="text-sm text-gray-500">
                                                {{ $sale->description }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- زر فتح خيارات التحصيل --}}
                                    <button onclick="toggleCreditActions({{ $sale->id }})"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition">
                                        خيارات التحصيل
                                    </button>
                                </div>

                                {{-- خيارات التحصيل --}}
                                <div id="credit-actions-{{ $sale->id }}" class="hidden space-y-3">

                                    {{-- تحصيل كامل --}}
                                    <a href="{{ route('employees.credit-sale.collect.full', [$employee->id, $sale->id]) }}"
                                       class="block bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg transition">
                                        تحصيل كامل
                                    </a>

                                    {{-- تحصيل جزئي --}}
                                    <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 space-y-3">

                                        <div class="relative">
                                            <input type="number"
                                                   id="creditPartialAmount-{{ $sale->id }}"
                                                   placeholder="مبلغ التحصيل الجزئي"
                                                   class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2 text-sm">
                                            <i class="fa-solid fa-money-bill text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                        </div>

                                        <button onclick="collectCreditPartial({{ $employee->id }}, {{ $sale->id }})"
                                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded-lg transition text-sm">
                                            تأكيد التحصيل الجزئي
                                        </button>

                                    </div>

                                </div>

                                {{-- سجلات التحصيل الجزئي --}}
                                @if(is_array($sale->partial_payments) && count($sale->partial_payments) > 0)

                                    <div class="space-y-2">

                                        @foreach($sale->partial_payments as $payment)
                                            <div class="bg-gray-900 border border-gray-700 rounded-lg p-3 text-sm">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-300">تحصيل جزئي</span>
                                                    <span class="text-yellow-400 font-semibold">
                                                        {{ number_format($payment['amount'], 2) }} ريال
                                                    </span>
                                                </div>
                                                <div class="text-gray-500 text-xs mt-1">
                                                    {{ $payment['date'] }}
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>

                                @endif

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>
    </div>
</div>

<script>
function toggleCreditActions(id) {
    document.getElementById('credit-actions-' + id).classList.toggle('hidden');
}

function collectCreditPartial(employeeId, saleId) {
    const amount = document.getElementById('creditPartialAmount-' + saleId).value;

    if (!amount || amount <= 0) {
        alert("الرجاء إدخال مبلغ صحيح");
        return;
    }

    window.location.href = `/employees/${employeeId}/credit-sale/${saleId}/collect-partial/${amount}`;
}
</script>
