@extends('dashboard.app')

@section('content')
<div x-data="pos()" class="p-6 min-h-screen bg-[#0f0f0f] text-white">

    <!-- هيدر كارليد -->
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-wide" style="color:#d4af37;">
            CARLED POS
        </h1>

        <div class="text-sm text-gray-300">
            مرحبًا {{ auth()->user()->name }}
        </div>
    </div>

    <!-- البحث عن المنتجات -->
    <div class="mb-4">
        <input
            type="text"
            x-model="search"
            @input="searchProducts"
            placeholder="ابحث عن منتج بالاسم أو الباركود"
            class="w-full border border-gray-700 bg-[#1a1a1a] text-white rounded p-3 focus:outline-none focus:border-[#d4af37]"
        >
    </div>

    <!-- نتائج البحث -->
    <template x-if="results.length > 0">
        <div class="border border-gray-700 rounded p-3 mb-4 bg-[#1a1a1a] shadow-lg">
            <template x-for="product in results" :key="product.id">
                <div
                    class="flex justify-between items-center p-2 border-b border-gray-700 cursor-pointer hover:bg-[#2a2a2a]"
                    @click="addToCart(product)"
                >
                    <span x-text="product.name"></span>
                    <span x-text="product.price + ' ريال'"></span>
                </div>
            </template>
        </div>
    </template>

    <!-- السلة -->
    <div class="bg-[#1a1a1a] p-5 rounded-xl shadow-xl border border-gray-700">
        <h2 class="text-2xl font-semibold mb-4" style="color:#d4af37;">السلة</h2>

        <template x-if="cart.length === 0">
            <p class="text-gray-400">لا توجد منتجات في السلة</p>
        </template>

        <template x-for="item in cart" :key="item.product_id">
            <div class="flex justify-between items-center border-b border-gray-700 py-3">
                <div>
                    <p class="font-semibold" x-text="item.name"></p>
                    <p class="text-sm text-gray-400">
                        السعر: <span x-text="item.price"></span> ريال
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="decrease(item)" class="px-3 py-1 bg-gray-800 rounded hover:bg-gray-700">-</button>
                    <span x-text="item.quantity"></span>
                    <button @click="increase(item)" class="px-3 py-1 bg-gray-800 rounded hover:bg-gray-700">+</button>
                </div>

                <div class="font-bold" x-text="item.total + ' ريال'"></div>
            </div>
        </template>

        <!-- الإجمالي -->
        <div class="text-right mt-4 text-2xl font-bold" style="color:#d4af37;">
            الإجمالي: <span x-text="total"></span> ريال
        </div>

        <!-- الدفع -->
        <div class="mt-4">
            <label class="block mb-1 font-semibold text-gray-300">المبلغ المدفوع</label>
            <input
                type="number"
                x-model="paid"
                class="w-full border border-gray-700 bg-[#1a1a1a] text-white rounded p-3 focus:outline-none focus:border-[#d4af37]"
            >
        </div>

        <!-- زر إتمام البيع -->
        <button
            @click="submitSale"
            class="w-full bg-[#d4af37] text-black p-3 rounded mt-4 text-lg font-bold hover:bg-[#c19d2f] transition"
        >
            إتمام البيع
        </button>

        <!-- رسالة النجاح -->
        <template x-if="successMessage">
            <div class="mt-4 p-3 bg-green-900 text-green-300 rounded" x-text="successMessage"></div>
        </template>

    </div>

</div>

<script>
function pos() {
    return {
        search: '',
        results: [],
        cart: [],
        paid: 0,
        successMessage: '',

        async searchProducts() {
            if (this.search.length < 1) {
                this.results = [];
                return;
            }

            let res = await fetch(`/api/products/search?query=${this.search}`);
            this.results = await res.json();
        },

        addToCart(product) {
            let existing = this.cart.find(i => i.product_id === product.id);

            if (existing) {
                existing.quantity++;
                existing.total = existing.quantity * existing.price;
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    total: product.price,
                });
            }

            this.results = [];
            this.search = '';
        },

        increase(item) {
            item.quantity++;
            item.total = item.quantity * item.price;
        },

        decrease(item) {
            if (item.quantity > 1) {
                item.quantity--;
                item.total = item.quantity * item.price;
            }
        },

        get total() {
            return this.cart.reduce((sum, item) => sum + item.total, 0);
        },

    async submitSale() {
    if (this.cart.length === 0) {
        alert("السلة فارغة");
        return;
    }

    let payload = {
        paid: this.paid,
        items: this.cart.map(i => ({
            product_id: i.product_id,
            price: i.price,
            quantity: i.quantity
        }))
    };

    let res = await fetch('/sales', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    });

    let data = await res.json();

    // عرض رسالة نجاح
    this.successMessage = "تمت عملية البيع بنجاح";

    // تفريغ السلة
    this.cart = [];
    this.paid = 0;

    // فتح صفحة الفاتورة مباشرة
    window.location.href = `/sales/${data.sale_id}`;
}

}
</script>
@endsection
