@extends('layouts.app')

@section('title', 'Transaksi Pembelian - Swalayan Segar')

@section('content')
<header class="mb-6 border-b border-gray-200 pb-4">
    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Transaksi Pembelian (Inbound)</h1>
    <p class="text-gray-500 mt-2">Catat barang masuk dari supplier ke gudang.</p>
</header>

<div class="flex flex-col xl:flex-row gap-6">
    <div class="w-full xl:w-2/3 space-y-6">
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Detail Supplier & Gudang</h2>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nama Supplier</label>
                    <input type="text" id="supplier-input" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition-all placeholder-gray-400" placeholder="Contoh: PT. Makmur">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">ID Gudang</label>
                    <input type="number" id="gudang-input" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition-all" value="1">
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Input Barang</h2>
            <form id="add-item-form" class="grid grid-cols-12 gap-4 items-end">
                <div class="col-span-6">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Barang / SKU</label>
                    <input type="text" id="item-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-green-500 transition-all" placeholder="Contoh: Indomie Goreng" required>
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Harga Beli</label>
                    <input type="number" id="item-price" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-green-500 transition-all" placeholder="Rp" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Qty</label>
                    <input type="number" id="item-qty" value="1" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-green-500 text-center transition-all" required>
                </div>
                <div class="col-span-1">
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 font-bold shadow-sm transition-all flex justify-center items-center h-[42px]">+</button>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Daftar Barang Masuk</h2>
            <div class="overflow-hidden border border-gray-200 rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 font-bold border-b">
                        <tr>
                            <th class="px-4 py-3">Nama Barang</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="purchase-list-body" class="divide-y divide-gray-100 bg-white">
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">Belum ada item ditambahkan</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-6 flex justify-between items-center border-t border-gray-100 pt-4">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Pembelian</p>
                    <p id="total-display" class="text-3xl font-bold text-gray-800 tracking-tight">Rp 0</p>
                </div>
                <button id="submit-purchase-btn" class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-green-700 shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>Simpan Transaksi</button>
            </div>
        </div>
    </div>

    <div class="w-full xl:w-1/3">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 h-[calc(100vh-8rem)] sticky top-6 flex flex-col">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Riwayat Pembelian</h2>
            <div class="space-y-3 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <input type="text" id="filter-supplier" placeholder="Cari Supplier..." class="w-full px-3 py-2 border border-gray-300 rounded text-sm bg-white focus:outline-none focus:ring-1 focus:ring-green-500 transition">
                <button id="refresh-history-btn" class="w-full bg-gray-200 text-gray-700 py-2 rounded text-sm font-semibold hover:bg-gray-300 transition-colors shadow-sm">Refresh Data</button>
            </div>
            <div id="history-container" class="flex-1 overflow-y-auto custom-scroll space-y-3 pr-2">
                <div class="text-center text-gray-400 mt-4 text-sm">Memuat data...</div>
            </div>
        </div>
    </div>
</div>

<div id="msg-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm transition-opacity">
    <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full transform transition-all scale-100">
        <h3 id="msg-title" class="font-bold text-xl mb-2"></h3>
        <p id="msg-body" class="text-gray-600 mb-6 leading-relaxed"></p>
        <button onclick="document.getElementById('msg-modal').classList.add('hidden')" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-bold shadow-md transition-all active:scale-95">Tutup</button>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pembelian.js'])
@endpush