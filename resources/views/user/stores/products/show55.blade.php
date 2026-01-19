@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">تفاصيل المنتج</h1>

    <a href="{{ route('products.index') }}"
       class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white">
        رجوع
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-6">

    {{-- معلومات المنتج --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- صورة المنتج --}}
        <div>
            <h2 class="text-gray-400 mb-2">صورة المنتج</h2>

            @if($product->image)
                <img src="{{ asset('uploads/products/' . $product->image) }}"
                     class="w-40 h-40 rounded border border-gray-700 object-cover">
            @else
                <img src="https://via.placeholder.com/150"
                     class="w-40 h-40 rounded border border-gray-700 object-cover">
            @endif
        </div>

        {{-- معلومات أساسية --}}
        <div class="space-y-4">

            <div>
                <h2 class="text-gray-400 mb-1">اسم المنتج</h2>
                <p class="text-gray-200 text-lg">{{ $product->name }}</p>
            </div>

            <div>
                <h2 class="text-gray-400 mb-1">الفئة</h2>
                <p class="text-gray-200 text-lg">{{ $product->category->name }}</p>
            </div>

            <div>
                <h2 class="text-gray-400 mb-1">السعر</h2>
                <p class="text-gray-200 text-lg">{{ $product->price }} ريال</p>
            </div>

            @if($product->discount_price)
                <div>
                    <h2 class="text-gray-400 mb-1">السعر بعد الخصم</h2>
                    <p class="text-green-400 text-lg">{{ $product->discount_price }} ريال</p>
                </div>
            @endif

        </div>

    </div>

    <hr class="border-gray-700">

    {{-- المخزون --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 text-center">
            <h3 class="text-gray-400 mb-2">الكمية المتوفرة</h3>
            <p class="text-3xl font-bold text-blue-400">{{ $product->quantity }}</p>
        </div>

        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 text-center">
            <h3 class="text-gray-400 mb-2">الحد الأدنى للمخزون</h3>
            <p class="text-3xl font-bold text-blue-400">{{ $product->min_stock }}</p>
        </div>

        <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 text-center">
            <h3 class="text-gray-400 mb-2">حالة المخزون</h3>

            @if($product->quantity <= 0)
                <span class="px-3 py-1 rounded bg-red-700 text-white text-sm">منتهي</span>
            @elseif($product->quantity <= $product->min_stock)
                <span class="px-3 py-1 rounded bg-yellow-600 text-white text-sm">منخفض</span>
            @else
                <span class="px-3 py-1 rounded bg-green-700 text-white text-sm">متوفر</span>
            @endif

        </div>

    </div>

    <hr class="border-gray-700">

    {{-- وصف المنتج --}}
    <div>
        <h2 class="text-gray-400 mb-2">وصف المنتج</h2>
        <p class="text-gray-300 leading-relaxed">
            {{ $product->description ?: 'لا يوجد وصف لهذا المنتج' }}
        </p>
    </div>

    <hr class="border-gray-700">

    {{-- تواريخ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div>
            <h2 class="text-gray-400 mb-1">تاريخ الإضافة</h2>
            <p class="text-gray-300">{{ $product->created_at }}</p>
        </div>

        <div>
            <h2 class="text-gray-400 mb-1">آخر تحديث</h2>
            <p class="text-gray-300">{{ $product->updated_at }}</p>
        </div>

    </div>

    <hr class="border-gray-700">

    {{-- أزرار الإجراءات --}}
    <div class="flex flex-wrap gap-3">

        {{-- تعديل --}}
        <a href="{{ route('products.edit', $product->id) }}"
           class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 rounded text-white">
            تعديل
        </a>

        {{-- إدارة المخزون --}}
        <a href="{{ route('products.stock', $product->id) }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white">
            إدارة المخزون
        </a>

        {{-- حذف --}}
        <form action="{{ route('products.destroy', $product->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <button class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white">
                حذف
            </button>
        </form>

    </div>

</div>

@endsection
