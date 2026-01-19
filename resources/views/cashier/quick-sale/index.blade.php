@extends('dashboard.app')

@section('title', 'البيع السريع')

@section('content')
<div x-data="quickSale()" x-init="init()" class="max-w-full lg:max-w-5xl mx-auto py-4 md:py-10 px-2 md:px-6 text-right" dir="rtl">

    {{-- 🔥 الشريط العلوي --}}
    <div class="flex flex-col md:flex-row items-center justify-between bg-gray-900 border border-gray-800 px-4 py-4 rounded-2xl mb-6 gap-4 shadow-xl">
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <a href="{{ route('accountant.dashboard') }}" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition">← رجوع</a>
            <h1 class="text-lg md:text-xl font-bold text-white">تسجيل بيع جديد</h1>
        </div>
        <div class="text-gray-400 text-sm bg-gray-800/50 px-4 py-2 rounded-lg border border-gray-700 w-full md:w-auto text-center font-sans">
            المحاسب: <span class="font-bold text-blue-400">{{ auth('accountant')->user()->name }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- العمود الأيمن: البحث والسلة --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- البحث المرن --}}
            <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-lg relative">
                <label class="text-gray-400 text-xs mb-2 block font-bold italic">ابحث عن منتج (بالأحرف أو الباركود)</label>
                <input type="text" x-model="search" x-ref="searchInput" @input.debounce.200ms="searchProducts"
                       placeholder="اكتب اسم المنتج..."
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-4 focus:ring-2 focus:ring-blue-500 outline-none transition-all text-right font-bold">

                <div x-show="results.length > 0" class="absolute z-50 left-0 right-0 mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl max-h-64 overflow-y-auto">
                    <template x-for="product in results" :key="product.id">
                        <div @click="addToCart(product)" class="p-4 border-b border-gray-700 hover:bg-gray-700 cursor-pointer flex justify-between items-center transition group">
                            <div class="flex-1">
                                <span class="text-white font-bold block group-hover:text-blue-400" x-text="product.name"></span>
                                <p class="text-gray-500 text-xs mt-1" x-text="product.description || 'لا يوجد وصف للمنتج'"></p>
                            </div>
                            <span class="text-blue-400 font-bold ml-4" x-text="Math.round(product.price) + ' ر.س'"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- السلة --}}
            <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-lg">
                <h2 class="text-white font-bold mb-4 flex items-center gap-2 border-b border-gray-800 pb-2 text-right">🛒 قائمة البيع</h2>
                <div class="space-y-3">
                    <template x-for="item in cart" :key="item.product_id">
                        <div class="flex flex-col bg-gray-800/40 p-3 rounded-xl border border-gray-800 gap-2 text-right">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-white font-bold text-sm" x-text="item.name"></p>
                                    <p class="text-gray-500 text-[11px] mt-1 italic" x-text="item.description || 'بدون وصف'"></p>
                                </div>
                                <button @click="removeItem(item)" class="text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition">🗑️</button>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-700/50 pt-2">
                                <div class="flex items-center bg-gray-900 rounded-lg p-1 border border-gray-700">
                                    <button @click="decrease(item)" class="w-8 h-8 text-white hover:bg-gray-700 rounded-md font-bold">-</button>
                                    <span class="w-10 text-center text-white font-black text-lg" x-text="item.quantity"></span>
                                    <button @click="increase(item)" class="w-8 h-8 text-white hover:bg-gray-700 rounded-md font-bold">+</button>
                                </div>
                                <div class="text-green-400 font-black text-xl" x-text="Math.round(item.total) + ' ر.س'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- العمود الأيسر: الحساب والدفع --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gray-900 border border-gray-800 p-5 rounded-2xl shadow-lg sticky top-6">

                <div class="space-y-4 mb-6">
                    {{-- حقل أجور اليد --}}
                    <div>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">🛠️ أجور اليد (عمل الورشة)</label>
                        <input type="number" step="1" x-model.number="labor_total"
                               @input="labor_total = Math.round(labor_total)"
                               @focus="$event.target.select()"
                               class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-2xl text-center font-black focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    {{-- حقل الوصف والملاحظات --}}
                    <div>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">📝 وصف العمل / ملاحظات</label>
                        <textarea x-model="description"
                                  placeholder="اكتب هنا ما تم عمله في الورشة..."
                                  class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-blue-500 transition-colors text-right font-bold"
                                  rows="2"></textarea>
                    </div>

                    {{-- حقل نسبة الضريبة --}}
                    <div>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">⚖️ نسبة الضريبة على المنتجات</label>
                        <select x-model.number="tax_rate" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 font-black outline-none focus:ring-2 focus:ring-blue-500 text-center">
                            <option value="0">بدون ضريبة (0%)</option>
                            <option value="15">ضريبة القيمة المضافة (15%)</option>
                        </select>
                    </div>

                    {{-- المبلغ المستلم --}}
                    <div>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">💵 المبلغ المستلم من الزبون</label>
                        <input type="number" step="1" x-model.number="paid_amount"
                               @input="paid_amount = Math.round(paid_amount)"
                               @focus="$event.target.select()"
                               class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-2xl text-center font-black focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                {{-- تفاصيل الحساب --}}
                <div class="bg-gray-800/80 rounded-xl p-4 mb-6 space-y-3 border border-gray-700 shadow-inner text-right text-sm">
                    <div class="flex justify-between text-gray-400 font-bold items-center">
                        <span>مجموع المنتجات:</span>
                        <span x-text="Math.round(items_total) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-purple-400 font-bold items-center border-t border-gray-700/30 pt-2">
                        <span>ضريبة المنتجات (<span x-text="tax_rate"></span>%):</span>
                        <span x-text="Math.round(tax_value) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-orange-400 font-bold items-center border-t border-gray-700/30 pt-2">
                        <span>أجور اليد (صافي):</span>
                        <span x-text="Math.round(labor_total) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-xl items-center border-t border-gray-700 pt-2">
                        <span class="text-blue-400 font-black">الإجمالي النهائي:</span>
                        <span class="text-blue-400 text-2xl font-black" x-text="Math.round(final_total) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-yellow-500 font-bold items-center border-t border-gray-700 pt-2">
                        <span>المتبقي (دين):</span>
                        <span class="font-black" x-text="Math.round(Math.max(0, remaining)) + ' ر.س'"></span>
                    </div>
                </div>

                {{-- طرق الدفع --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" @click="sale_type = 'cash'" :class="sale_type === 'cash' ? 'bg-green-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'" class="py-3 rounded-xl text-xs font-black text-white transition-all shadow-lg">كاش</button>
                        <button type="button" @click="cardPayment()" :class="sale_type === 'card' ? 'bg-blue-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'" class="py-3 rounded-xl text-xs font-black text-white transition-all shadow-lg">شبكة</button>
                        <button type="button" @click="sale_type = 'credit'" :class="sale_type === 'credit' ? 'bg-yellow-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'" class="py-3 rounded-xl text-xs font-black text-white transition-all shadow-lg">آجل</button>
                    </div>

                    <div x-show="sale_type === 'credit'" x-transition class="mt-2">
                        <select x-model="employee_id" class="w-full bg-gray-800 border-2 border-yellow-600/50 text-white rounded-xl px-3 py-3 text-sm font-bold outline-none text-right">
                            <option value="">— اختر الموظف لتقييد الدين —</option>
                            <template x-for="person in creditPersons" :key="person.id">
                                <option :value="person.id" x-text="person.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- ✅ خيار إصدار فاتورة ضريبية --}}
                    <div class="flex items-center gap-2 bg-gray-800/50 p-3 rounded-xl border border-gray-700 mt-4">
                        <input type="checkbox" x-model="has_invoice" id="has_invoice" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <label for="has_invoice" class="text-white text-sm font-bold cursor-pointer">إصدار فاتورة ضريبية للمطبوعات</label>
                    </div>
                </div>

                {{-- الفورم --}}
                <form method="POST" action="{{ route('accountant.quick-sale.submit') }}" x-ref="saleForm" class="mt-6">
                    @csrf
                    <input type="hidden" name="items" x-model="items_json">
                    <input type="hidden" name="labor_total" :value="Math.round(labor_total)">
                    <input type="hidden" name="paid_amount" :value="Math.round(paid_amount)">
                    <input type="hidden" name="tax_rate" x-model="tax_rate">
                    <input type="hidden" name="sale_type" x-model="sale_type">
                    <input type="hidden" name="employee_id" x-model="employee_id">
                    <input type="hidden" name="description" x-model="description">
                    <input type="hidden" name="has_invoice" :value="has_invoice ? 1 : 0">

                    <button type="button" @click="prepareForm($refs.saleForm)"
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white py-5 rounded-2xl font-black text-xl transition-all active:scale-95 shadow-2xl">
                        تأكيد العملية ✅
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@section('scripts')
<script>
function quickSale() {
    return {
        search: '', results: [], cart: [], labor_total: 0, paid_amount: 0, tax_rate: 0,
        sale_type: 'cash', employee_id: '', creditPersons: [], description: '', items_json: '', has_invoice: false,
        // إضافة التحقق من وجود رقم ضريبي للمتجر
       hasStoreTaxNumber: {{ auth('accountant')->user()->store->tax_number ? 'true' : 'false' }},

        init() {
            this.loadCreditPersons();
            this.$nextTick(() => { this.$refs.searchInput.focus(); });
        },

        async loadCreditPersons() {
            try {
                let res = await fetch('{{ route('accountant.quick-sale.credit-persons') }}');
                this.creditPersons = await res.json();
            } catch (e) { console.error('Error loading employees'); }
        },

        async searchProducts() {
            if (this.search.length < 1) { this.results = []; return; }
            try {
                let res = await fetch("{{ route('products.search') }}?query=" + encodeURIComponent(this.search));
                this.results = await res.json();
            } catch (e) { console.error('Search error'); }
        },

        addToCart(product) {
            let existing = this.cart.find(i => i.product_id === product.id);
            let price = Math.round(parseFloat(product.price)) || 0;
            if (existing) {
                existing.quantity++;
                existing.total = existing.quantity * price;
            } else {
                this.cart.push({ product_id: product.id, name: product.name, description: product.description || '', price: price, quantity: 1, total: price });
            }
            this.search = ''; this.results = []; this.$refs.searchInput.focus();
        },

        increase(item) { item.quantity++; item.total = item.quantity * item.price; },
        decrease(item) { if (item.quantity > 1) { item.quantity--; item.total = item.quantity * item.price; } },
        removeItem(item) { this.cart = this.cart.filter(i => i.product_id !== item.product_id); },

        get items_total() {
            return this.cart.reduce((sum, item) => sum + (Math.round(item.total) || 0), 0);
        },
        get tax_value() {
            return (this.items_total * this.tax_rate) / 100;
        },
        get final_total() {
            let itemsWithTax = this.items_total + this.tax_value;
            return itemsWithTax + (Math.round(this.labor_total) || 0);
        },
        get remaining() {
            return this.final_total - (Math.round(this.paid_amount) || 0);
        },

        cardPayment() {
            this.sale_type = 'card';
            this.paid_amount = Math.round(this.final_total);
        },

        async prepareForm(form) {
            if (this.cart.length === 0 && Math.round(this.labor_total) <= 0) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى إضافة منتج أو أجور يد.', icon: 'warning', confirmButtonText: 'حسناً' });
            }
            if (this.labor_total > 0 && (!this.description || this.description.trim().length < 3)) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى كتابة وصف العمل في خانة الملاحظات.', icon: 'warning', confirmButtonText: 'حسناً' });
            }
            if (this.sale_type === 'credit' && !this.employee_id) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى اختيار الموظف.', icon: 'warning', confirmButtonText: 'حسناً' });
            }

            // --- منطق إخلاء المسؤولية المضاف ---
            if (this.tax_rate > 0 && !this.hasStoreTaxNumber) {
                const result = await Swal.fire({
                    title: 'إخلاء مسؤولية ضريبية',
                    text: 'أنت بصدد فرض ضريبة بينما المتجر لا يملك رقماً ضريبياً مسجلاً. هل تتحمل مسؤولية هذا الإجراء قانونياً؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، استمرار',
                    cancelButtonText: 'إلغاء'
                });

                if (!result.isConfirmed) return;
            }
            // ---------------------------------

            this.items_json = JSON.stringify(this.cart);
            this.$nextTick(() => form.submit());
        }
    }
}
</script>
@endsection
@endsection
