@extends('layouts.app')

@section('title', 'Manajemen Stok - Swalayan Segar')

@push('styles')

@section('content')
    <header class="mb-6"><h1 class="text-3xl font-bold text-gray-800">Stok Opname / Perhitungan Fisik Stok</h1></header>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="mb-6 text-gray-600">Lakukan perhitungan fisik jumlah barang di rak, lalu masukkan hasilnya di kolom <strong>'Stok Fisik'</strong> untuk mencocokkan dengan data sistem.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sistem</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Fisik (Input)</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Selisih</th>
                    </tr>
                </thead>
                <tbody id="opname-table-body" class="bg-white divide-y divide-gray-200">
                    <tr id="loading-row">
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">Memuat data stok...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-8 text-right">
            <button id="save-opname-button" 
                    onclick="simpanOpname()"
                    class="bg-green-600 text-white px-8 py-2.5 rounded-lg font-semibold shadow-md hover:bg-green-700 transition-all duration-200 disabled:bg-gray-400">
                Simpan Hasil Opname
            </button>
        </div>
    </div>
        <div id="message-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-sm w-full mx-4 text-center">
            <h3 id="message-title" class="text-xl font-bold mb-2"></h3>
            <p id="message-body" class="text-gray-600 mb-6"></p>
            <button onclick="hideMessage()" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition-all">
                Tutup
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/stok_opname.js'])
@endpush