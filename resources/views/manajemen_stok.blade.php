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

<div class="mb-6 flex flex-col gap-4 rounded-lg bg-white p-4 shadow-md lg:flex-row lg:items-center lg:justify-end lg:space-x-4">
    <div class="relative w-full lg:w-72">
        <input type="text" id="stok-search-input" placeholder="Cari Nama Barang / ID..." class="w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-green-500">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-5 h-5 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <x-table-wrapper minWidth="760px" class="bg-white rounded-lg shadow-md">
        <thead class="bg-gray-50">
            <tr>
                <x-table-th>ID/SKU</x-table-th>
                <x-table-th>Nama Barang</x-table-th>
                <x-table-th :force="true">Kategori</x-table-th>
                <x-table-th>Harga Beli</x-table-th>
                <x-table-th :force="true" align="right">Harga Jual</x-table-th>
                <x-table-th align="right">Stok</x-table-th>
            </tr>
        </thead>
        <tbody id="stok-table-body" class="bg-white divide-y divide-gray-200">
            <tr id="loading-row">
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat data stok...</td>
            </tr>
        </tbody>
    </x-table-wrapper>
    
    <div class="p-4 flex items-center justify-between border-t border-gray-200">
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