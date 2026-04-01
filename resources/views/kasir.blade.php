@extends('layouts.app')
@section('title', 'Kasir - Swalayan Segar')

@section('content')
<header class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Kasir / Point of Sale</h1>
</header>

<div class="flex flex-wrap lg:flex-nowrap gap-6">
    
    <div class="w-full lg:w-2/3">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Transaksi Baru</h2>
            
            <form id="add-item-form" class="flex items-end space-x-4 mb-6">
                <div class="flex-1">
                    <label for="sku-input" class="block text-sm font-medium text-gray-600">SKU / ID Barang</label>
                    <input type="text" id="sku-input" placeholder="Masukkan SKU atau ID" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                </div>
                <div>
                    <label for="qty-input" class="block text-sm font-medium text-gray-600">Jumlah</label>
                    <input type="number" id="qty-input" value="1" min="1" class="mt-1 block w-20 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition text-center">
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg font-semibold shadow-md hover:bg-emerald-700 transition-colors h-10">
                    Tambah
                </button>
            </form>

            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Produk</th>
                            <th scope="col" class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Harga</th>
                            <th scope="col" class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Jumlah</th>
                            <th scope="col" class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Subtotal</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cart-table-body" class="bg-white divide-y divide-gray-200">
                        <tr id="cart-empty-row">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">Keranjang masih kosong</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
            <h2 class="text-xl font-semibold mb-6 text-gray-700">Ringkasan Pembayaran</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span id="cart-subtotal" class="font-medium">Rp 0</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Pajak (11%)</span>
                    <span id="cart-tax" class="font-medium">Rp 0</span>
                </div>
                <hr class="border-dashed border-slate-300">
                <div class="flex justify-between text-xl font-bold text-gray-800">
                    <span>Total</span>
                    <span id="cart-total" class="text-emerald-600">Rp 0</span>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <div class="space-y-4">
                <div>
                    <label for="payment-amount-input" class="block text-sm font-bold text-gray-600">Jumlah Bayar</label>
                    <input type="text" id="payment-amount-input" placeholder="e.g. 100000" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition text-right text-xl font-bold text-slate-800">
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-800 mt-4">
                    <span>Kembalian</span>
                    <span id="change-amount" class="text-amber-500">Rp 0</span>
                </div>
            </div>

            <button id="checkout-button" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-lg mt-8 shadow-md hover:bg-emerald-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Proses & Cetak Struk
            </button>
        </div>
    </div>
</div>

<div id="message-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 hidden transition-opacity" onclick="hideMessage()">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4 transform transition-all" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 id="message-title" class="text-xl font-bold"></h3>
            <button onclick="hideMessage()" class="text-slate-400 hover:text-slate-600 bg-slate-100 p-1 rounded-md transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <p id="message-body" class="text-slate-600 text-sm leading-relaxed mb-6"></p>
        <button onclick="hideMessage()" class="w-full bg-slate-100 text-slate-800 font-bold py-2.5 rounded-lg hover:bg-slate-200 transition-colors">
            Tutup
        </button>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/kasir.js'])
@endpush