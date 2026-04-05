@extends('layouts.app') @section('title', 'Laporan Keuangan - Swalayan Segar')

@section('content')
<header class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Laporan Keuangan</h1>
</header>

<div class="mb-6 flex flex-wrap gap-4 items-end bg-white p-4 rounded-xl shadow-sm border border-gray-100">
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
        <input type="date" id="start-date" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none transition">
    </div>
    <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
        <input type="date" id="end-date" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 outline-none transition">
    </div>
    <button id="filter-btn" class="bg-emerald-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-emerald-700 transition shadow-sm">
        Cari Laporan
    </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-emerald-500">
        <p class="text-sm text-gray-500 font-medium uppercase">Pemasukan</p>
        <p id="total-masuk" class="text-2xl font-bold text-emerald-600 ">Memuat...</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
        <p class="text-sm text-gray-500 font-medium uppercase">Pengeluaran</p>
        <p id="total-keluar" class="text-2xl font-bold text-red-600 ">Memuat...</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
        <p class="text-sm text-gray-500 font-medium uppercase">Saldo Akhir</p>
        <p id="saldo-akhir" class="text-2xl font-bold text-blue-600 ">Memuat...</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 text-gray-500 text-xs font-bold uppercase">
            <tr>
                <th class="px-6 py-3 text-left">Tanggal</th>
                <th class="px-6 py-3 text-left">Keterangan</th>
                <th class="px-6 py-3 text-center">Tipe</th>
                <th class="px-6 py-3 text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody id="laporan-table-body" class="bg-white divide-y divide-gray-100">
            </tbody>
    </table>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/laporan_keuangan.js'])
@endpush