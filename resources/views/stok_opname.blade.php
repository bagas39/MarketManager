@extends('layouts.app')

@section('title', 'Manajemen Stok - MarketManager')

@push('styles')

@section('content')
    @include('components.mobile-header', ['title' => 'Stok Opname'])
    <header class="mb-6"><h1 class="text-3xl font-bold text-gray-800">Stok Opname / Perhitungan Fisik Stok</h1></header>
    <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 lg:p-4">
        <p class="mb-6 text-gray-600">Lakukan perhitungan fisik jumlah barang di rak, lalu masukkan hasilnya di kolom <strong>'Stok Fisik'</strong> untuk mencocokkan dengan data sistem.</p>
            <div class="">
                <x-table-wrapper>
                    <thead class="bg-gray-50">
                        <tr>
                            <x-table-th extra="lg:px-3 lg:py-2">Kode Produk</x-table-th>
                            <x-table-th extra="lg:px-3 lg:py-2">Nama Produk</x-table-th>
                            <x-table-th align="center" extra="lg:px-3 lg:py-2">Stok Sistem</x-table-th>
                            <x-table-th align="center" extra="lg:px-3 lg:py-2">Stok Fisik (Input)</x-table-th>
                            <x-table-th :force="true" align="center">Keterangan</x-table-th>
                            <x-table-th :force="true" align="center">Selisih</x-table-th>
                        </tr>
                    </thead>
                    <tbody id="opname-table-body" class="bg-white divide-y divide-gray-200">
                        <tr id="loading-row">
                            <td colspan="6" class="px-3 py-6 md:px-6 md:py-10 text-center text-gray-500">Memuat data stok...</td>
                        </tr>
                    </tbody>
                </x-table-wrapper>
            </div>
        <div class="mt-4 flex flex-col gap-3 border-t border-gray-200 pt-4 lg:flex-row lg:items-center lg:justify-between lg:gap-2">
            <p id="opname-pagination-info" class="text-sm text-gray-600">Memuat pagination...</p>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <button id="opname-prev-page" type="button" onclick="goToPrevOpnamePage()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Sebelumnya
                </button>
                <button id="opname-next-page" type="button" onclick="goToNextOpnamePage()" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Berikutnya
                </button>
            </div>
        </div>
        <div class="mt-8 flex flex-col justify-end gap-3 sm:flex-row">
            <button id="open-history-button"
                    onclick="openHistoryModal()"
                    class="w-full bg-slate-700 px-6 py-2.5 font-semibold text-white shadow-md transition-all duration-200 hover:bg-slate-800 sm:w-auto">
                Lihat History Perubahan
            </button>
            <button id="save-opname-button" 
                    onclick="simpanOpname()"
                    class="w-full bg-green-600 px-8 py-2.5 font-semibold text-white shadow-md transition-all duration-200 hover:bg-green-700 disabled:bg-gray-400 sm:w-auto">
                Simpan Hasil Opname Halaman Ini
            </button>
        </div>
    </div>
    <div id="history-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-5xl w-full mx-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-slate-800">History Stok Opname</h3>
                <button onclick="closeHistoryModal()" class="text-slate-500 hover:text-slate-700 font-bold text-xl leading-none">&times;</button>
            </div>
            <div class="overflow-auto border border-slate-200 rounded-lg">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-center">Stok Sistem</th>
                            <th class="px-4 py-3 text-center">Stok Fisik</th>
                            <th class="px-4 py-3 text-center">Selisih</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="divide-y divide-slate-100 bg-white">
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Memuat history...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <button onclick="closeHistoryModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2 rounded-lg font-semibold">Tutup</button>
            </div>
        </div>
    </div>
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl mx-4">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 id="confirm-title" class="text-xl font-bold text-slate-800"></h3>
                <button onclick="resolveOpnameConfirm(false)" class="rounded-md bg-slate-100 px-2 py-1 text-slate-400 transition-colors hover:text-slate-600">
                    x
                </button>
            </div>
            <p id="confirm-body" class="mb-6 text-sm leading-relaxed text-slate-600"></p>
            <div class="grid grid-cols-2 gap-3">
                <button onclick="resolveOpnameConfirm(false)"
                        class="w-full rounded-lg bg-slate-100 py-2.5 font-bold text-slate-800 transition-colors hover:bg-slate-200">
                    Batal
                </button>
                <button onclick="resolveOpnameConfirm(true)"
                        class="w-full rounded-lg bg-emerald-600 py-2.5 font-bold text-white transition-colors hover:bg-emerald-700">
                    Simpan
                </button>
            </div>
        </div>
    </div>
    <div id="message-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm" onclick="hideMessage()">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl mx-4" onclick="event.stopPropagation()">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 id="message-title" class="text-xl font-bold"></h3>
                <button onclick="hideMessage()" class="rounded-md bg-slate-100 px-2 py-1 text-slate-400 transition-colors hover:text-slate-600">
                    x
                </button>
            </div>
            <p id="message-body" class="mb-6 text-sm leading-relaxed text-slate-600"></p>
            <button onclick="hideMessage()" 
                    class="w-full rounded-lg bg-slate-100 py-2.5 font-bold text-slate-800 transition-colors hover:bg-slate-200">
                Tutup
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/stok_opname.js'])
@endpush
