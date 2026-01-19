@extends('dashboard.app')

@section('title', 'إعداد المتجر الجديد')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center shadow-2xl shadow-blue-900/40 transform -rotate-3">
                <i class="fa-solid fa-store text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">إضافة متجر جديد</h1>
                <p class="text-slate-400 text-sm mt-1 flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    إعداد البيانات وفقاً لهيكلية النظام
                </p>
            </div>
        </div>

        <a href="{{ route('user.stores.index') }}" 
           class="inline-flex items-center gap-3 bg-slate-800/50 hover:bg-slate-700 text-slate-300 px-6 py-3 rounded-2xl border border-slate-700 transition-all group font-semibold backdrop-blur-sm">
            <i class="fa-solid fa-chevron-right transition-transform group-hover:translate-x-1"></i>
            <span>رجوع للقائمة</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        {{-- الجانب الأيسر: النموذج --}}
        <div class="lg:col-span-8 space-y-8">
            <form action="{{ route('user.stores.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                {{-- بطاقة الهوية التجارية (الأعمدة: name, description, logo) --}}
                <div class="bg-slate-900/40 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-md">
                    <div class="p-8 border-b border-slate-800 bg-gradient-to-r from-blue-600/5 to-transparent">
                        <h2 class="text-white text-xl font-bold flex items-center gap-3">
                            <i class="fa-solid fa-id-card text-blue-500"></i>
                            الهوية التجارية
                        </h2>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-slate-300 text-sm font-semibold mr-1">اسم المتجر (name)</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="اسم المتجر الرسمي"
                                       class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-blue-500 transition-all outline-none" required>
                                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-slate-300 text-sm font-semibold mr-1">شعار المتجر (logo)</label>
                                <input type="file" name="logo" 
                                       class="w-full bg-slate-950/50 border border-slate-700 text-slate-400 rounded-2xl px-5 py-2.5 focus:border-blue-500 transition-all outline-none">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">الوصف (description)</label>
                            <textarea name="description" rows="3" placeholder="وصف مختصر لنشاط المتجر..." 
                                      class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3 focus:border-blue-500 transition-all outline-none">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- بطاقة البيانات القانونية (الأعمدة: tax_number, commercial_registration, phone, address, bank_accounts) --}}
                <div class="bg-slate-900/40 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-md">
                    <div class="p-8 border-b border-slate-800 bg-gradient-to-r from-emerald-600/5 to-transparent">
                        <h2 class="text-white text-xl font-bold flex items-center gap-3">
                            <i class="fa-solid fa-file-contract text-emerald-500"></i>
                            البيانات القانونية والبنكية
                        </h2>
                    </div>
                    
                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">الرقم الضريبي (tax_number)</label>
                            <input type="text" name="tax_number" value="{{ old('tax_number') }}" placeholder="الرقم الضريبي للمنشأة"
                                   class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none font-mono">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">السجل التجاري (commercial_registration)</label>
                            <input type="text" name="commercial_registration" value="{{ old('commercial_registration') }}" placeholder="رقم السجل التجاري"
                                   class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">رقم الهاتف (phone)</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="05xxxxxxxx"
                                   class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none text-left" dir="ltr">
                        </div>

                        <div class="space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">العنوان (address)</label>
                            <input type="text" name="address" value="{{ old('address') }}" placeholder="المدينة، الحي، الشارع"
                                   class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3.5 focus:border-emerald-500 outline-none">
                        </div>

                        <div class="md:col-span-2 space-y-2">
                            <label class="text-slate-300 text-sm font-semibold mr-1">الحسابات البنكية (bank_accounts)</label>
                            <textarea name="bank_accounts" rows="3" placeholder="أدخل أسماء البنوك وأرقام الآيبان الخاصة بالمتجر..." 
                                      class="w-full bg-slate-950/50 border border-slate-700 text-white rounded-2xl px-5 py-3 focus:border-emerald-500 transition-all outline-none italic">{{ old('bank_accounts') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- زر الإرسال --}}
                <button type="submit" class="w-full h-16 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-500 text-white rounded-2xl font-black text-lg shadow-2xl transition-all flex items-center justify-center gap-3">
                    <i class="fa-solid fa-circle-check"></i>
                    تأكيد وإنشاء المتجر
                </button>
            </form>
        </div>

        {{-- الجانب الأيمن: معلومات مساعدة --}}
        <div class="lg:col-span-4 space-y-8">
            <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-8 backdrop-blur-md">
                <h3 class="text-white font-bold text-lg mb-6">
                    <i class="fa-solid fa-shield-halved text-blue-500 mr-2"></i> بيانات دقيقة
                </h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">
                    جميع الحقول أعلاه مرتبطة مباشرة بقاعدة بيانات النظام. يرجى التأكد من صحة الحسابات البنكية لضمان استلام التحويلات بشكل سليم.
                </p>
                <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-2xl">
                    <span class="text-blue-400 text-xs font-bold uppercase tracking-wider">نصيحة</span>
                    <p class="text-slate-300 text-xs mt-1">رقم الهاتف والعنوان سيظهران في ترويسة الفاتورة المطبوعة للعملاء.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection