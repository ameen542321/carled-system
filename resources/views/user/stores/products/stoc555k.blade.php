@extends('dashboard.app')

@section('title', 'إدارة مخزون المنتج – ' . $product->name)

@section('content')

<div class="max-w-6xl mx-auto py-10 space-y-8">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-4">

        {{-- زر الرجوع إلى الكتالوج --}}
        <a href="{{ route('user.stores.catalog', $product->store_id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع إلى الكتالوج</span>
        </a>

        <h1 class="text-2xl font-bold text-white">
            إدارة مخزون المنتج
        </h1>

        <div class="w-32"></div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 space-y-8">

        {{-- معلومات المنتج --}}
        <div class="flex items-center gap-6">

            {{-- صورة --}}
            <div>
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}"
                         class="w-24 h-24 rounded border border-gray-700 object-cover">
                @else
                    <img src="{{ asset('images/default-product.png') }}"
                         class="w-24 h-24 rounded border border-gray-700 object-cover">
                @endif
            </div>

            {{-- معلومات --}}
            <div class="space-y-2">
                <h2 class="text-xl text-gray-200 font-semibold">{{ $product->name }}</h2>
                <p class="text-gray-400">القسم: {{ $product->category->name ?? '—' }}</p>
                <p class="text-gray-400">سعر البيع: {{ $product->price }} ر.س</p>
                @if(!is_null($product->cost_price))
                    <p class="text-gray-400">سعر التكلفة: {{ $product->cost_price }} ر.س</p>
                @endif
            </div>

        </div>

        <hr class="border-gray-800">

        {{-- المخزون الحالي --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-gray-950 border border-gray-800 rounded-lg p-4 text-center">
                <h3 class="text-gray-400 mb-2 text-sm">الكمية الحالية</h3>
                <p class="text-3xl font-bold text-blue-400">{{ $product->quantity }}</p>
            </div>

            <div class="bg-gray-950 border border-gray-800 rounded-lg p-4 text-center">
                <h3 class="text-gray-400 mb-2 text-sm">الحد الأدنى للمخزون</h3>
                <p class="text-3xl font-bold text-blue-400">{{ $product->min_stock ?? 0 }}</p>
            </div>

            <div class="bg-gray-950 border border-gray-800 rounded-lg p-4 text-center">
                <h3 class="text-gray-400 mb-2 text-sm">حالة المخزون</h3>

                @if($product->quantity <= 0)
                    <span class="px-3 py-1 rounded bg-red-700 text-white text-xs">منتهي</span>
                @elseif($product->quantity <= ($product->min_stock ?? 0))
                    <span class="px-3 py-1 rounded bg-yellow-600 text-white text-xs">منخفض</span>
                @else
                    <span class="px-3 py-1 rounded bg-green-700 text-white text-xs">متوفر</span>
                @endif

            </div>

        </div>

        <hr class="border-gray-800">

        {{-- زيادة المخزون --}}
        <div class="bg-gray-950 border border-gray-800 rounded-lg p-6">

            <h2 class="text-xl text-gray-200 mb-4 font-semibold">زيادة المخزون</h2>

            <form action="{{ route('stores.products.stock.increase', $product->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-2 text-gray-300 text-sm">الكمية المراد إضافتها</label>
                    <input type="number" name="add_quantity" min="1"
                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200
                                  focus:border-blue-500 focus:ring-blue-500"
                           placeholder="مثال: 20">
                </div>

                <div>
                    <label class="block mb-2 text-gray-300 text-sm">ملاحظة (اختياري)</label>
                    <input type="text" name="add_note"
                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200
                                  focus:border-blue-500 focus:ring-blue-500"
                           placeholder="سبب الإضافة">
                </div>

                <button class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded text-white font-semibold text-sm">
                    إضافة
                </button>

            </form>

        </div>

        {{-- خصم المخزون --}}
        <div class="bg-gray-950 border border-gray-800 rounded-lg p-6">

            <h2 class="text-xl text-gray-200 mb-4 font-semibold">خصم من المخزون</h2>

            <form action="{{ route('stores.products.stock.decrease', $product->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block mb-2 text-gray-300 text-sm">الكمية المراد خصمها</label>
                    <input type="number" name="subtract_quantity" min="1"
                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200
                                  focus:border-blue-500 focus:ring-blue-500"
                           placeholder="مثال: 5">
                </div>

                <div>
                    <label class="block mb-2 text-gray-300 text-sm">ملاحظة (اختياري)</label>
                    <input type="text" name="subtract_note"
                           class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200
                                  focus:border-blue-500 focus:ring-blue-500"
                           placeholder="سبب الخصم">
                </div>

                <button class="bg-red-600 hover:bg-red-700 px-6 py-2 rounded text-white font-semibold text-sm">
                    خصم
                </button>

            </form>

        </div>

        <hr class="border-gray-800">

        {{-- سجل الحركات --}}
        <div>
            <h2 class="text-xl text-gray-200 mb-4 font-semibold">سجل حركات المخزون</h2>

            <div class="bg-gray-950 border border-gray-800 rounded-lg p-4">

                <table class="w-full text-right">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-800 text-sm">
                            <th class="py-3">التاريخ</th>
                            <th class="py-3">النوع</th>
                            <th class="py-3">الكمية</th>
                            <th class="py-3">ملاحظة</th>
                        </tr>
                    </thead>

                    <tbody class="text-gray-300 text-sm">

                        @forelse($stockMovements as $movement)
                            <tr class="border-b border-gray-800">
                                <td class="py-3">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                <td class="py-3">
                                    @if($movement->type === 'increase')
                                        <span class="px-3 py-1 rounded bg-green-700 text-white text-xs">
                                            إضافة
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded bg-red-700 text-white text-xs">
                                            خصم
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    {{ $movement->type === 'increase' ? '+' : '-' }}{{ $movement->quantity }}
                                </td>
                                <td class="py-3">{{ $movement->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">
                                    لا توجد حركات مخزون حتى الآن
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>

            </div>
        </div>

    </div>

</div>

@endsection
