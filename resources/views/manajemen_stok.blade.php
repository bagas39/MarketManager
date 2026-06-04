@extends('layouts.app')

@section('title', 'Manajemen Stok - Swalayan Segar')

@push('styles')
<style>
    .pagination-button { 
        padding: 0.5rem 1rem; 
        border: 1px solid #D1D5DB; 
        background-color: white; 
        border-radius: 0.375rem; 
        font-weight: 500; 
        color: #374151; 
        transition: background-color 0.2s; 
    }
    .pagination-button:hover:not(:disabled) { background-color: #F9FAFB; }
    .pagination-button:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
@endpush

@section('content')
    @include('components.mobile-header', ['title' => 'Manajemen Stok'])
<header class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Manajemen Stok</h1>
</header>

<div class="mb-6 rounded-lg bg-white p-4 shadow-md lg:p-4">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-center lg:gap-2">
        <div class="md:col-span-3">
            <input type="date" id="start-date" class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-green-500 lg:px-3 lg:py-2" aria-label="Tanggal awal">
        </div>
        <div class="hidden text-center text-gray-500 md:col-span-1 md:block">-</div>
        <div class="md:col-span-3">
            <input type="date" id="end-date" class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-green-500 lg:px-3 lg:py-2" aria-label="Tanggal akhir">
        </div>
        <div class="relative md:col-span-4">
            <input type="text" id="stok-search-input" placeholder="Cari Nama Barang / ID..." class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-green-500 lg:px-3 lg:py-2">
        </div>
        <div class="md:col-span-1">
            <button id="search-btn" class="w-full rounded-lg bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700 lg:px-3 lg:py-2">Cari</button>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <x-table-wrapper class="bg-white rounded-lg shadow-md">
        <thead class="bg-gray-50">
            <tr>
                <x-table-th extra="lg:px-3 lg:py-2">ID/SKU</x-table-th>
                <x-table-th extra="lg:px-3 lg:py-2">Nama Barang</x-table-th>
                <x-table-th :force="true" extra="lg:px-3 lg:py-2">Kategori</x-table-th>
                <x-table-th extra="lg:px-3 lg:py-2">Harga Beli</x-table-th>
                <x-table-th :force="true" align="right" extra="lg:px-3 lg:py-2">Harga Jual</x-table-th>
                <x-table-th align="right" extra="lg:px-3 lg:py-2">Stok</x-table-th>
                <x-table-th align="right" extra="lg:px-3 lg:py-2">Aksi</x-table-th>
            </tr>
        </thead>
        <tbody id="stok-table-body" class="bg-white divide-y divide-gray-200">
            <tr id="loading-row">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500 lg:px-4 lg:py-6">Memuat data stok...</td>
            </tr>
        </tbody>
    </x-table-wrapper>
    
    <div class="flex items-center justify-between border-t border-gray-200 p-4 lg:p-3">
        <div><span id="page-info" class="text-sm text-gray-600">Menampilkan 0-0 dari 0</span></div>
        <div class="flex space-x-2">
            <button id="prev-button" class="pagination-button" disabled>Sebelumnya</button>
            <button id="next-button" class="pagination-button" disabled>Berikutnya</button>
        </div>
    </div>
</div>

<div id="message-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 hidden" onclick="hideMessage()">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 id="message-title" class="text-xl font-semibold text-red-600">Error</h3>
            <button onclick="hideMessage()" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <p id="message-body" class="text-gray-700"></p>
        <button onclick="hideMessage()" class="w-full bg-green-600 text-white font-bold py-2 rounded-lg mt-6 hover:bg-green-700 transition-all">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/manajemen_stok.js'])
@endpush