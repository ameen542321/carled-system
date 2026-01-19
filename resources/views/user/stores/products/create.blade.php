@extends('dashboard.app')

@section('title', 'إضافة منتج – متجر ' . $store->name)

@section('content')

<div class="max-w-4xl mx-auto py-10">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-8">

        {{-- زر الرجوع إلى المنتجات --}}
        <a href="{{ route('user.stores.products.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع إلى المنتجات</span>
        </a>

        {{-- عنوان الصفحة --}}
        <h1 class="text-2xl font-bold text-white">
            إضافة منتج جديد
        </h1>

        <div class="w-32"></div>
    </div>

    {{-- النموذج --}}
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl">

        <form action="{{ route('user.stores.products.store', $store->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            @php
                $mainCategories = $categories->where('is_main_category', 1);
                $normalCategories = $categories->where('is_main_category', 0);
            @endphp

            {{-- القسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">القسم</label>
                <select name="category_id"
        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">

    {{-- الأنشطة --}}
    @if($mainCategories->isNotEmpty())
        <optgroup label="الأنشطة">
            @foreach($mainCategories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $selectedCategory) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </optgroup>
    @endif

    {{-- الأقسام --}}
    @if($normalCategories->isNotEmpty())
        <optgroup label="الأقسام">
            @foreach($normalCategories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $selectedCategory) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </optgroup>
    @endif

</select>

                @error('category_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الاسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">اسم المنتج</label>
                <input type="text" name="name"
                       value="{{ old('name') }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- سعر البيع --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">سعر البيع</label>
                <input type="number" step="0.01" name="price"
                       value="{{ old('price') }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- سعر التكلفة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">سعر التكلفة</label>
                <input type="number" step="0.01" name="cost_price"
                       value="{{ old('cost_price') }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @error('cost_price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الكمية --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الكمية</label>
                <input type="number" name="quantity"
                       value="{{ old('quantity') }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @error('quantity') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الحد الأدنى للمخزون --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الحد الأدنى للمخزون</label>
                <input type="number" name="min_stock"
                       value="{{ old('min_stock', 1) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @error('min_stock') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- الوصف --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الوصف</label>
                <textarea name="description" rows="4"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">{{ old('description') }}</textarea>
            </div>

            {{-- الحالة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">الحالة</label>
                <select name="status"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                    <option value="active">مفعل</option>
                    <option value="inactive">غير مفعل</option>
                </select>
            </div>

            {{-- الصورة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">صورة المنتج</label>
                <input type="file" name="image"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @error('image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- خيار البقاء في صفحة الإضافة --}}
            <div class="flex items-center gap-2 mt-4 mb-6">
                <input type="checkbox" name="stay_here" id="stay_here"
                       class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-blue-500">
                <label for="stay_here" class="text-gray-300 text-sm">
                    البقاء في صفحة إضافة المنتجات بعد الحفظ
                </label>
            </div>

            {{-- زر الإرسال --}}
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fa-solid fa-plus ml-1"></i>
                إضافة المنتج
            </button>

        </form>

    </div>

</div>

@endsection
