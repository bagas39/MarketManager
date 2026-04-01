@extends('layouts.app')
@section('title', 'Laporan Transaksi Penjualan')

@section('content')
<h1 class="mb-5 text-2xl font-bold text-slate-800">Laporan Transaksi Penjualan</h1>

<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    
    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
        <input type="date" id="start-date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 md:w-auto">
        <span class="hidden text-slate-500 md:block">-</span>
        <input type="date" id="end-date" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 md:w-auto">
        <input type="text" id="search-input" placeholder="Cari ID Transaksi..." class="w-full flex-1 rounded-lg border border-slate-300 px-3 py-2 text-slate-700 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        <button id="search-btn" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white transition hover:bg-emerald-700">Cari</button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-slate-700">
            <thead class="bg-slate-50 text-left text-slate-600 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 font-semibold">ID Transaksi</th>
                    <th class="px-4 py-3 font-semibold">Tanggal</th>
                    <th class="px-4 py-3 font-semibold">Kasir</th>
                    <th class="px-4 py-3 font-semibold text-right">Total Harga</th>
                    <th class="px-4 py-3 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="sales-table-body" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-500">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4">
        <span id="page-info" class="text-sm text-slate-500">Menampilkan data</span>
        <div class="flex space-x-2">
            <button class="rounded border border-slate-300 px-3 py-1 text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-50">Prev</button>
            <button class="rounded border border-slate-300 px-3 py-1 text-sm text-slate-600 hover:bg-slate-50 disabled:opacity-50">Next</button>
        </div>
    </div>
</div>

<div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-center justify-between border-b pb-3">
            <h3 id="modal-title" class="text-lg font-bold text-slate-800">Detail Transaksi</h3>
            <button onclick="hideDetailModal()" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div id="modal-body" class="max-h-[60vh] overflow-y-auto pr-2 custom-scroll text-sm">
            </div>
        <div class="mt-6 border-t pt-4">
            <button onclick="hideDetailModal()" class="w-full rounded-lg bg-slate-100 px-4 py-2 font-semibold text-slate-700 transition hover:bg-slate-200">Tutup</button>
        </div>
    </div>
</div>

<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex items-center justify-between border-b pb-3">
            <h3 class="text-lg font-bold text-slate-800">Edit Transaksi <span id="edit-modal-title" class="text-emerald-600"></span></h3>
            <button onclick="hideEditModal()" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div class="max-h-[50vh] overflow-y-auto pr-2 custom-scroll">
            <input type="hidden" id="edit-trx-id">
            
            <div class="flex gap-2 mb-4 bg-slate-50 p-3 rounded-lg border border-slate-200 items-end">
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">ID Barang / SKU</label>
                    <input type="number" id="new-item-id" placeholder="Contoh: 1001" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm focus:outline-none focus:border-emerald-500">
                </div>
                <div class="w-20">
                    <label class="text-xs font-semibold text-slate-500 mb-1 block">Qty</label>
                    <input type="number" id="new-item-qty" placeholder="Jml" value="1" min="1" class="w-full px-3 py-1.5 border border-slate-300 rounded text-sm text-center focus:outline-none focus:border-emerald-500">
                </div>
                <button onclick="addNewItemToEdit()" class="bg-emerald-600 text-white px-4 py-1.5 rounded text-sm font-semibold hover:bg-emerald-700 h-[34px] transition-colors">Tambah</button>
            </div>

            <table class="w-full text-left text-sm mb-4">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="p-2 font-semibold">Nama Barang</th>
                        <th class="p-2 font-semibold text-right">Harga</th>
                        <th class="p-2 font-semibold text-center w-24">Jumlah</th>
                        <th class="p-2 font-semibold text-right">Subtotal</th>
                        <th class="p-2 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="edit-items-body" class="divide-y divide-slate-100">
                    </tbody>
            </table>
            
            <div class="text-right">
                <p class="text-xs text-slate-500 uppercase tracking-wide font-bold">Total Transaksi (Edit)</p>
                <p id="edit-total-display" class="text-2xl font-black text-emerald-600">Rp 0</p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
            <button onclick="hideEditModal()" class="px-5 py-2 rounded-lg bg-slate-100 font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
            <button onclick="saveEditTransaction()" id="btn-save-edit" class="px-5 py-2 rounded-lg bg-emerald-600 font-bold text-white hover:bg-emerald-700 transition shadow-sm">Simpan Perubahan</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/penjualan.js'])
@endpush