@extends('layouts.app') @section('title', 'Laporan Keuangan - MarketManager')

@section('content')
    @include('components.mobile-header', ['title' => 'Laporan Keuangan'])
<header class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 sm:text-3xl">Laporan Keuangan</h1>
</header>

<div class="mb-6 flex flex-col gap-3 rounded-xl border border-gray-100 bg-white p-3 shadow-sm sm:gap-4 sm:p-4 lg:flex-row lg:flex-wrap lg:items-end">
    <div class="w-full lg:w-auto">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
        <input type="date" id="start-date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:ring-2 focus:ring-emerald-500 lg:w-auto">
    </div>
    <div class="w-full lg:w-auto">
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
        <input type="date" id="end-date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:ring-2 focus:ring-emerald-500 lg:w-auto">
    </div>
    <button id="filter-btn" class="w-full rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 sm:px-6 lg:w-auto lg:py-2">
        Cari Laporan
    </button>
    <button id="export-pdf-btn" class="w-full rounded-lg bg-red-500 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-red-600 sm:px-6 lg:w-auto lg:py-2">
        Download PDF
    </button>
</div>

<div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-3 md:gap-6">
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-emerald-500 sm:p-6">
        <p class="text-sm text-gray-500 font-medium uppercase">Pemasukan</p>
        <p id="total-masuk" class="text-xl font-bold text-emerald-600 sm:text-2xl">Memuat...</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-red-500 sm:p-6">
        <p class="text-sm text-gray-500 font-medium uppercase">Pengeluaran</p>
        <p id="total-keluar" class="text-xl font-bold text-red-600 sm:text-2xl">Memuat...</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500 sm:p-6">
        <p class="text-sm text-gray-500 font-medium uppercase">Saldo Akhir</p>
        <p id="saldo-akhir" class="text-xl font-bold text-blue-600 sm:text-2xl">Memuat...</p>
    </div>
</div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md">
        <x-table-wrapper minWidth="720px">
            <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase">
                <tr>
                    <x-table-th>Tanggal</x-table-th>
                    <x-table-th>Keterangan</x-table-th>
                    <x-table-th align="center">Tipe</x-table-th>
                    <x-table-th align="right">Jumlah</x-table-th>
                </tr>
            </thead>
            <tbody id="laporan-table-body" class="bg-white divide-y divide-gray-100">
            </tbody>
        </x-table-wrapper>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/laporan_keuangan.js'])
@endpush