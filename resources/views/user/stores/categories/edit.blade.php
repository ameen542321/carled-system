@extends('dashboard.app')

@section('title', ($is_main_category ? 'تعديل النشاط – متجر ' : 'تعديل القسم – متجر ') . $store->name)

@section('content')

<div class="max-w-3xl mx-auto py-10">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-10">

        {{-- زر الرجوع --}}
        <a href="{{ route('user.stores.categories.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع</span>
        </a>

        {{-- عنوان الصفحة --}}
        <h1 class="text-2xl font-bold text-white">
            {{ $is_main_category ? 'تعديل النشاط' : 'تعديل القسم' }}
        </h1>

        <div class="w-20"></div>

    </div>

    {{-- النموذج --}}
    <div class="bg-gray-900 border border-gray-800 p-8 rounded-xl shadow-lg">

        <form action="{{ route('user.stores.categories.update', [$store->id, $category->id]) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- نوع القسم (نشاط / قسم عادي) --}}
            <input type="hidden" name="is_main_category" value="{{ $is_main_category }}">

            {{-- الاسم --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-medium">
                    {{ $is_main_category ? 'اسم النشاط' : 'اسم القسم' }}
                </label>

                <input type="text" name="name"
                       value="{{ old('name', $category->name) }}"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition">

                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- الوصف --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-medium">الوصف</label>
                <textarea name="description" rows="4"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition">{{ old('description', $category->description) }}</textarea>
            </div>

            {{-- الحالة --}}
            <div class="mb-6">
                <label class="block text-gray-300 mb-2 font-medium">الحالة</label>
                <select name="status"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2 focus:border-blue-500 focus:ring-blue-500 transition">
                    <option value="active" {{ old('status', $category->status) == 'active' ? 'selected' : '' }}>مفعل</option>
                    <option value="inactive" {{ old('status', $category->status) == 'inactive' ? 'selected' : '' }}>غير مفعل</option>
                </select>
            </div>

            {{-- الأزرار --}}
            <div class="flex items-center justify-between mt-10">

                {{-- زر الحفظ --}}
                <button type="submit"
                        class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition shadow-md">
                    <i class="fa-solid fa-floppy-disk ml-2"></i>
                    حفظ التعديلات
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
