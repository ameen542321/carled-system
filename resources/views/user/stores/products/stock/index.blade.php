@extends('dashboard.app')

@section('title', 'إدارة مخزون المنتج – ' . $product->name)

@section('content')

<div class="max-w-5xl mx-auto py-10">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-8">

        {{-- زر الرجوع --}}
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('user.stores.products.index', $store->id) }}"
   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
    <i class="fa-solid fa-arrow-right text-sm"></i>
    <span class="text-sm font-medium">رجوع</span>
</a>


        <h1 class="text-2xl font-bold text-white">
            إدارة مخزون المنتج – {{ $product->name }}
        </h1>

        <div class="w-32"></div>
    </div>

    {{-- معلومات المنتج --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl mb-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- الكمية الحالية --}}
            <div class="bg-gray-800 border border-gray-700 p-4 rounded-lg text-center">
                <p class="text-gray-400 text-sm">الكمية الحالية</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $product->quantity }}</p>
            </div>

            {{-- الحد الأدنى --}}
            <div class="bg-gray-800 border border-gray-700 p-4 rounded-lg text-center">
                <p class="text-gray-400 text-sm">الحد الأدنى للمخزون</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $product->min_stock }}</p>
            </div>

            {{-- حالة المخزون --}}
            <div class="bg-gray-800 border border-gray-700 p-4 rounded-lg text-center">
                <p class="text-gray-400 text-sm">حالة المخزون</p>

                @if($product->quantity <= $product->min_stock)
                    <p class="text-red-400 font-bold text-xl mt-1">منخفض</p>
                @else
                    <p class="text-green-400 font-bold text-xl mt-1">جيد</p>
                @endif
            </div>

        </div>

    </div>

    {{-- نماذج الزيادة والخصم --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

        {{-- زيادة المخزون --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">
            <h2 class="text-lg font-bold text-white mb-4">زيادة المخزون</h2>

            <form action="{{ route('user.stores.products.stock.increase', [$store->id, $product->id]) }}" method="POST">
                @csrf

                <label class="block text-gray-300 mb-2">الكمية</label>
                <input type="number" name="quantity" min="1"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 mb-4">

                <label class="block text-gray-300 mb-2">ملاحظة (اختياري)</label>
                <input type="text" name="note"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 mb-4">

                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus ml-1"></i>
                    زيادة
                </button>
            </form>
        </div>

        {{-- خصم المخزون --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">
            <h2 class="text-lg font-bold text-white mb-4">خصم المخزون</h2>

            <form action="{{ route('user.stores.products.stock.decrease', [$store->id, $product->id]) }}" method="POST">
                @csrf

                <label class="block text-gray-300 mb-2">الكمية</label>
                <input type="number" name="quantity" min="1"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 mb-4">

                <label class="block text-gray-300 mb-2">ملاحظة (اختياري)</label>
                <input type="text" name="note"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 mb-4">

                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition">
                    <i class="fa-solid fa-minus ml-1"></i>
                    خصم
                </button>
            </form>
        </div>

    </div>

    {{-- سجل الحركات --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl">

        <h2 class="text-lg font-bold text-white mb-4">سجل الحركات</h2>

        <table class="w-full text-right text-gray-300">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2">النوع</th>
                    <th class="py-2">الكمية</th>
                    <th class="py-2">المستخدم</th>
                    <th class="py-2">الملاحظة</th>
                    <th class="py-2">التاريخ</th>
                </tr>
            </thead>

            <tbody>
                @forelse($movements as $move)
                    <tr class="border-b border-gray-800">
                        <td class="py-2">
                            @if($move->type === 'increase')
                                <span class="text-green-400">زيادة</span>
                            @else
                                <span class="text-red-400">خصم</span>
                            @endif
                        </td>

                        <td class="py-2">{{ $move->quantity }}</td>
                        <td class="py-2">{{ $move->user->name ?? '—' }}</td>
                        <td class="py-2">{{ $move->note ?? '—' }}</td>
                        <td class="py-2">{{ $move->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">
                            لا توجد حركات مخزون حتى الآن
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection
