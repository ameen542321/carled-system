@extends('dashboard.app')


@section('content')

<div class="max-w-2xl mx-auto">

    {{-- العنوان --}}
    <h1 class="text-2xl font-semibold text-white mb-6">
        تعديل المتجر
    </h1>

    {{-- نموذج تعديل المتجر --}}
    <div class="bg-[#1b1d21] border border-[#2a2d31] rounded-xl p-6">
        @include('user.stores.includes.store-form', ['store' => $store])
    </div>

</div>

@endsection
