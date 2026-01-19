@extends('dashboard.app')
@section('title', 'ارسال اشعار    ')
@section('content')
<div class="container max-w-3xl mx-auto py-6">

    <h2 class="text-2xl font-bold mb-6 text-white">إرسال إشعار</h2>

    @if(session('success'))
        <div class="bg-green-600 text-white p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('user.notifications.send.store') }}"
          class="bg-gray-800 p-6 rounded-lg border border-gray-700">
        @csrf

        {{-- نوع المستهدف --}}
        <div class="mb-4">
            <label class="text-gray-300 font-semibold">إرسال إلى:</label>
            <select name="target_type" id="target_type"
                    class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700">
                <option value="store">متجر واحد</option>
                <option value="stores">عدة متاجر</option>
                <option value="accountant">محاسب واحد</option>
                <option value="accountants">عدة محاسبين</option>
                <option value="admin">المدير العام</option>
            </select>
        </div>

        {{-- اختيار المستهدفين --}}
        <div id="targets_box" class="mb-4">

            {{-- المتاجر --}}
            <div id="stores_box" class="hidden">
                <label class="text-gray-300 font-semibold">اختر المتاجر:</label>
                <select name="target_ids[]" multiple
                        class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700">
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- المحاسبون --}}
            <div id="accountants_box" class="hidden">
                <label class="text-gray-300 font-semibold">اختر المحاسبين:</label>
                <select name="target_ids[]" multiple
                        class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700">
                    @foreach($accountants as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- المدير العام --}}
            <div id="admin_box" class="hidden text-gray-400">
                سيتم إرسال الإشعار إلى المدير العام مباشرة.
            </div>

        </div>

        {{-- العنوان --}}
        <div class="mb-4">
            <label class="text-gray-300 font-semibold">عنوان الإشعار:</label>
            <input type="text" name="title"
                   class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700">
        </div>

        {{-- الرسالة --}}
        <div class="mb-4">
            <label class="text-gray-300 font-semibold">نص الإشعار:</label>
            <textarea name="message" rows="4"
                      class="w-full mt-2 p-2 bg-gray-900 text-gray-200 rounded border border-gray-700"></textarea>
        </div>

        <button class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">
            إرسال الإشعار
        </button>
    </form>
</div>

<script>
    const targetType = document.getElementById('target_type');
    const storesBox = document.getElementById('stores_box');
    const accountantsBox = document.getElementById('accountants_box');
    const adminBox = document.getElementById('admin_box');

    function updateTargets() {
        storesBox.classList.add('hidden');
        accountantsBox.classList.add('hidden');
        adminBox.classList.add('hidden');

        if (targetType.value === 'store' || targetType.value === 'stores') {
            storesBox.classList.remove('hidden');
        }

        if (targetType.value === 'accountant' || targetType.value === 'accountants') {
            accountantsBox.classList.remove('hidden');
        }

        if (targetType.value === 'admin') {
            adminBox.classList.remove('hidden');
        }
    }

    targetType.addEventListener('change', updateTargets);
    updateTargets();
</script>

@endsection
