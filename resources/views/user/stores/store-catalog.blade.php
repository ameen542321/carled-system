@extends('dashboard.app')

@section('title', 'لوحة تحكم المتجر – ' . $store->name)

@section('content')

<div class="max-w-7xl mx-auto py-10 space-y-10">

    {{-- العنوان --}}
    <h1 class="text-3xl font-bold text-white">لوحة تحكم المتجر</h1>
{{-- روابط الوصول السريع --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    {{-- الأقسام --}}
    <a href="{{ route('user.stores.categories.index', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-layer-group text-orange-400 text-4xl"></i>
            <div>
                <h2 class="text-xl font-bold text-white">الأقسام</h2>
                <p class="text-gray-400 mt-1">إدارة أقسام المنتجات</p>
            </div>
        </div>
    </a>

    {{-- المنتجات --}}
    <a href="{{ route('user.stores.products.index', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-box text-purple-400 text-4xl"></i>
            <div>
                <h2 class="text-xl font-bold text-white">المنتجات</h2>
                <p class="text-gray-400 mt-1">إضافة وتعديل المنتجات</p>
            </div>
        </div>
    </a>

    {{-- سلة المحذوفات --}}
    <a href="{{ route('user.stores.products.trash', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-trash text-red-400 text-4xl"></i>
            <div>
                <h2 class="text-xl font-bold text-white">سلة المحذوفات</h2>
                <p class="text-gray-400 mt-1">المنتجات المحذوفة</p>
            </div>
        </div>
    </a>

</div>

    {{-- البطاقات --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

        {{-- عدد الأقسام --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center">
            <h3 class="text-gray-400 text-sm">عدد الأقسام</h3>
            <p class="text-3xl font-bold text-blue-400">{{ $categoriesCount }}</p>
        </div>

        {{-- عدد المنتجات --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center">
            <h3 class="text-gray-400 text-sm">عدد المنتجات</h3>
            <p class="text-3xl font-bold text-purple-400">{{ $productsCount }}</p>
        </div>

        {{-- منخفض المخزون --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center">
            <h3 class="text-gray-400 text-sm">منتجات منخفضة</h3>
            <p class="text-3xl font-bold text-yellow-400">{{ $lowStockCount }}</p>
        </div>

        {{-- سلة المحذوفات --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center">
            <h3 class="text-gray-400 text-sm">محذوفات</h3>
            <p class="text-3xl font-bold text-red-400">{{ $trashedCount }}</p>
        </div>

    </div>

    {{-- آخر المنتجات --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">آخر المنتجات المضافة</h2>

        <table class="w-full text-right">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800 text-sm">
                    <th class="py-3">المنتج</th>
                    <th class="py-3">القسم</th>
                    <th class="py-3">السعر</th>
                    <th class="py-3">الكمية</th>
                </tr>
            </thead>

            <tbody class="text-gray-300 text-sm">
                @forelse($latestProducts as $product)
                    <tr class="border-b border-gray-800">
                        <td class="py-3">{{ $product->name }}</td>
                        <td class="py-3">{{ $product->category->name ?? '—' }}</td>
                        <td class="py-3">{{ $product->price }} ر.س</td>
                        <td class="py-3">{{ $product->quantity }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">
                            لا توجد منتجات مضافة حديثًا
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- منخفض المخزون --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">منتجات منخفضة المخزون</h2>

        <table class="w-full text-right">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800 text-sm">
                    <th class="py-3">المنتج</th>
                    <th class="py-3">الكمية</th>
                    <th class="py-3">الحد الأدنى</th>
                </tr>
            </thead>

            <tbody class="text-gray-300 text-sm">
                @forelse($lowStockProducts as $product)
                    <tr class="border-b border-gray-800">
                        <td class="py-3">{{ $product->name }}</td>
                        <td class="py-3">{{ $product->quantity }}</td>
                        <td class="py-3">{{ $product->min_stock }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">
                            لا توجد منتجات منخفضة المخزون
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- سجل الحركات --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">آخر حركات المخزون</h2>

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
                @forelse($latestMovements as $move)
                    <tr class="border-b border-gray-800">
                        <td class="py-3">{{ $move->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-3">
                            @if($move->type === 'increase')
                                <span class="px-3 py-1 rounded bg-green-700 text-white text-xs">إضافة</span>
                            @else
                                <span class="px-3 py-1 rounded bg-red-700 text-white text-xs">خصم</span>
                            @endif
                        </td>
                        <td class="py-3">{{ $move->quantity }}</td>
                        <td class="py-3">{{ $move->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">
                            لا توجد حركات مخزون
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
