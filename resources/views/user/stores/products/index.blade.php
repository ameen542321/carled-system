@extends('dashboard.app')

@section('title', 'المنتجات – متجر ' . $store->name)

@section('content')

<div class="max-w-7xl mx-auto py-10 space-y-8">

    {{-- الهيدر --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        {{-- زر الرجوع --}}
        <a href="{{ route('user.stores.catalog', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 transition">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع</span>
        </a>

        {{-- العنوان --}}
        <h1 class="text-2xl font-bold text-white text-center md:text-right flex-1">
            المنتجات
        </h1>

        {{-- زر إضافة منتج --}}
       <a href="{{ route('user.stores.products.create', [
    'store' => $store->id,
    'category_id' => request('category_id')
]) }}"

           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition shadow-sm">
            <i class="fa-solid fa-plus"></i>
            <span>إضافة منتج</span>
        </a>

    </div>

    {{-- البحث والفلترة --}}
    <form method="GET"
          action="{{ route('user.stores.products.index', $store->id) }}"
          class="bg-gray-900 border border-gray-800 p-6 rounded-xl space-y-6">

        @php
            $mainCategories = $categories->where('is_main_category', 1);
            $normalCategories = $categories->where('is_main_category', 0);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- البحث --}}
            <div class="space-y-1">
                <label class="text-gray-300 text-sm">بحث</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="ابحث عن منتج..."
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
            </div>

            {{-- القسم --}}
            <div class="space-y-1">
                <label class="text-gray-300 text-sm">القسم</label>
                <select name="category_id"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">كل الأقسام</option>

                    {{-- الأنشطة --}}
                    @if($mainCategories->isNotEmpty())
                        <optgroup label="الأنشطة">
                            @foreach($mainCategories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    {{-- الأقسام --}}
                    @if($normalCategories->isNotEmpty())
                        <optgroup label="الأقسام">
                            @foreach($normalCategories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                </select>
            </div>

            {{-- الحالة --}}
            <div class="space-y-1">
                <label class="text-gray-300 text-sm">الحالة</label>
                <select name="status"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">الكل</option>
                    <option value="active" @selected(request('status') == 'active')>مفعل</option>
                    <option value="inactive" @selected(request('status') == 'inactive')>غير مفعل</option>
                </select>
            </div>

            {{-- الأزرار --}}
            <div class="flex items-end gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">
                    <i class="fa-solid fa-magnifying-glass ml-1"></i>
                    بحث
                </button>

                <a href="{{ route('user.stores.products.index', $store->id) }}"
                   class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg w-full text-center">
                    <i class="fa-solid fa-rotate-left ml-1"></i>
                    إعادة
                </a>
            </div>

        </div>

    </form>

    {{-- جدول المنتجات --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-x-auto">

        <table class="min-w-full text-white">
            <thead class="bg-gray-800 sticky top-0 z-10">
                <tr class="text-gray-300 text-sm">
                    <th class="py-3 px-4 text-right">المنتج</th>
                    <th class="py-3 px-4 text-right">القسم</th>
                    <th class="py-3 px-4 text-right">السعر</th>
                    <th class="py-3 px-4 text-right">الكمية</th>
                    <th class="py-3 px-4 text-right">الحالة</th>
                    <th class="py-3 px-4 text-right">العمليات</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $product)

                    @php
                        $lowStock = $product->quantity <= $product->min_stock;
                    @endphp

                    <tr class="border-b border-gray-800 text-sm
                        {{ $lowStock ? 'bg-red-900/20 hover:bg-red-900/30' : 'hover:bg-gray-800' }}">

                        <td class="py-3 px-4">{{ $product->name }}</td>

                        <td class="py-3 px-4">
                            {{ $product->category->name ?? '—' }}

                           
                        </td>

                        <td class="py-3 px-4">{{ number_format($product->price, 2) }} ر.س</td>

                        <td class="py-3 px-4 font-bold {{ $lowStock ? 'text-red-400' : 'text-green-400' }}">
                            {{ $product->quantity }}
                        </td>

                        <td class="py-3 px-4">
                            @if($product->status === 'active')
                                <span class="text-green-400">مفعل</span>
                            @else
                                <span class="text-yellow-400">غير مفعل</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 flex items-center gap-4">

                            {{-- إدارة المخزون --}}
                            <a href="{{ route('user.stores.products.stock', [$store->id, $product->id]) }}"
                               class="text-purple-400 hover:text-purple-300">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </a>

                            {{-- تعديل --}}
                            <a href="{{ route('user.stores.products.edit', [$store->id, $product->id]) }}"
                               class="text-blue-400 hover:text-blue-300">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            {{-- تفعيل/تعطيل --}}
                            <form action="{{ route('user.stores.products.toggle-status', [$store->id, $product->id]) }}"
                                  method="POST">
                                @csrf
                                @method('PUT')

                                @if($product->status === 'active')
                                    <button class="text-yellow-400 hover:text-yellow-300">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                @else
                                    <button class="text-green-400 hover:text-green-300">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                @endif
                            </form>

                            {{-- حذف --}}
                            <form action="{{ route('user.stores.products.destroy', [$store->id, $product->id]) }}"
                                  method="POST"
                                  onsubmit="return confirm('هل أنت متأكد من حذف المنتج؟');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-400 hover:text-red-300">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>

                        </td>

                    </tr>
                @endforeach
            </tbody>

        </table>
<div class="mt-6">
    {{ $products->links() }}
</div>

    </div>

    {{-- سلة المحذوفات --}}
    <div class="flex justify-center">
        <a href="{{ route('user.stores.products.trash', $store->id) }}"
           class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition flex items-center gap-3 shadow-sm">
            <i class="fa-solid fa-trash-can text-lg"></i>
            <span class="text-sm font-medium">سلة المحذوفات</span>
            <span class="bg-white text-red-600 text-xs font-bold px-2 py-1 rounded-full">
                {{ $trashedCount }}
            </span>
        </a>
    </div>

</div>

@endsection
