@extends('dashboard.app')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">بحث عن منتج</h1>
    <p class="text-gray-400 text-sm mt-1">ابحث عن المنتجات باستخدام الاسم  </p>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-xl p-6">

    {{-- مربع البحث --}}
    <div class="mb-6 relative">
        <label class="text-gray-300 text-sm mb-2 block">ابحث عن منتج</label>

        <input type="text" id="searchInput"
               placeholder="اكتب اسم المنتج  ..."
               class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-12 pr-4 py-3 focus:border-blue-500 focus:ring-blue-500 transition">

        {{-- أيقونة البحث --}}
        <i class="fa-solid fa-magnifying-glass text-gray-500 absolute left-4 top-11 text-lg"></i>

        {{-- لودر صغير --}}
        <div id="loader" class="hidden absolute right-4 top-11">
            <i class="fa-solid fa-spinner fa-spin text-blue-400 text-lg"></i>
        </div>
    </div>

    {{-- نتائج البحث --}}
    <div id="results" class="mt-6 space-y-4"></div>

</div>

<script>
let timer = null;

document.getElementById('searchInput').addEventListener('keyup', function () {
    let query = this.value;
    let loader = document.getElementById('loader');
    let resultsBox = document.getElementById('results');

    clearTimeout(timer);

    // إظهار اللودر
    loader.classList.remove('hidden');

    timer = setTimeout(() => {

        fetch(`/accountant/pos/search?query=` + query)
            .then(response => response.json())
            .then(data => {

                loader.classList.add('hidden');
                resultsBox.innerHTML = '';

                if (data.length === 0) {
                    resultsBox.innerHTML = `
                        <div class="text-center py-6 text-gray-500">
                            <i class="fa-solid fa-circle-xmark text-3xl mb-2"></i>
                            <p>لا توجد نتائج</p>
                        </div>
                    `;
                    return;
                }

                data.forEach(product => {

                    let stockColor =
                        product.quantity <= 5 ? 'text-red-400' :
                        product.quantity <= 20 ? 'text-yellow-400' :
                        'text-green-400';

                    resultsBox.innerHTML += `
                        <div class="bg-gray-900 border border-gray-700 p-4 rounded-lg flex justify-between items-center hover:border-blue-500 transition">

                            <div>
                                <h3 class="text-white font-bold text-lg">${product.name}</h3>

                                <p class="text-gray-400 text-sm mt-1">
                                    <i class="fa-solid fa-tag text-blue-400 ml-1"></i>
                                    السعر: <span class="text-blue-300">${product.price} ر.س</span>
                                </p>

                                <p class="text-gray-400 text-sm mt-1">
                                    <i class="fa-solid fa-boxes-stacked ${stockColor} ml-1"></i>
                                    الكمية: <span class="${stockColor}">${product.quantity}</span>
                                </p>
                            </div>

                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                                <i class="fa-solid fa-cart-plus ml-1"></i>
                                إضافة للبيع
                            </button>

                        </div>
                    `;
                });
            });

    }, 300); // تأخير بسيط لتحسين الأداء
});
</script>

@endsection
