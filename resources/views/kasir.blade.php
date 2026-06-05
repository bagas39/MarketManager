@extends('layouts.app')
@section('title', 'Kasir - MarketManager')

@section('content')
<header class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Kasir / Point of Sale</h1>
</header>

    @include('components.mobile-header', ['title' => 'Kasir'])

<div class="flex flex-col gap-6 lg:flex-row">

    <div class="w-full lg:w-2/3">
        <div class="rounded-lg bg-white p-4 shadow-md sm:p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Transaksi Baru</h2>

            <form id="add-item-form" class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:space-x-4">
                <div class="flex-1">
                    <label for="sku-input" class="block text-sm font-medium text-gray-600">SKU / ID Barang</label>
                    <input type="text" id="sku-input" placeholder="Masukkan SKU atau ID" class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label for="qty-input" class="block text-sm font-medium text-gray-600">Jumlah</label>
                    <input type="number" id="qty-input" value="1" min="1" class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-center shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 sm:w-20">
                </div>
                <button type="submit" class="h-10 rounded-lg bg-emerald-600 px-6 py-2 font-semibold text-white shadow-md transition-colors hover:bg-emerald-700 sm:w-auto w-full">
                    Tambah
                </button>
            </form>

                <x-table-wrapper minWidth="720px" class="divide-y divide-gray-200 text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <x-table-th>Produk</x-table-th>
                            <x-table-th>Harga</x-table-th>
                            <x-table-th>Jumlah</x-table-th>
                            <x-table-th>Subtotal</x-table-th>
                            <x-table-th align="right">Aksi</x-table-th>
                        </tr>
                    </thead>
                    <tbody id="cart-table-body" class="bg-white divide-y divide-gray-200">
                        <tr id="cart-empty-row">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 italic">Keranjang masih kosong</td>
                        </tr>
                    </tbody>
                </x-table-wrapper>
        </div>
    </div>

    <div class="w-full lg:w-1/3">
        <div class="sticky top-8 rounded-lg bg-white p-4 shadow-md sm:p-6">
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

            <div class="mb-5">
                <p class="text-sm font-bold text-gray-600 mb-2">Metode Pembayaran</p>
                <div class="grid grid-cols-2 gap-2">
                    <button id="btn-pay-tunai" onclick="setPaymentMethod('tunai')"
                        class="flex items-center justify-center gap-2 rounded-lg border-2 border-emerald-500 bg-emerald-50 py-2.5 text-sm font-semibold text-emerald-700 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Tunai
                    </button>
                    <button id="btn-pay-qris" onclick="setPaymentMethod('qris')"
                        class="flex items-center justify-center gap-2 rounded-lg border-2 border-slate-200 bg-white py-2.5 text-sm font-semibold text-slate-500 transition-all hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 18.75h.75v.75h-.75v-.75ZM18 13.5h.75v.75H18v-.75ZM18 18.75h.75v.75H18v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                        </svg>
                        QRIS
                    </button>
                </div>
            </div>

            <div id="cash-payment-section" class="space-y-4">
                <div>
                    <label for="payment-amount-input" class="block text-sm font-bold text-gray-600">Jumlah Bayar</label>
                    <input type="text" id="payment-amount-input" placeholder="e.g. 100000" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition text-right text-xl font-bold text-slate-800">
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-800 mt-4">
                    <span>Kembalian</span>
                    <span id="change-amount" class="text-amber-500">Rp 0</span>
                </div>
            </div>

            <div id="qris-payment-section" class="hidden">
                <div class="rounded-lg bg-blue-50 border border-blue-200 p-3 text-sm text-blue-700">
                    <p class="font-semibold mb-1">Pembayaran QRIS</p>
                    <p class="text-xs text-blue-600">Klik tombol di bawah untuk generate QR Code. Pelanggan scan menggunakan GoPay, OVO, Dana, atau aplikasi m-banking.</p>
                </div>
            </div>

            <button id="checkout-button" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-lg mt-6 shadow-md hover:bg-emerald-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
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

<div id="qr-modal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4" onclick="event.stopPropagation()">

        <div class="text-center mb-5">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Menunggu Pembayaran</h3>
            <p class="text-sm text-slate-500 mt-1">Halaman pembayaran Xendit sudah dibuka di tab baru</p>
        </div>

        <div class="bg-slate-50 rounded-xl p-4 text-center mb-5">
            <p class="text-xs text-slate-500 uppercase tracking-wide font-semibold mb-1">Total Pembayaran</p>
            <p id="qr-amount" class="text-2xl font-bold text-emerald-600">Rp 0</p>
        </div>

        <div id="qr-status-waiting" class="flex items-center justify-center gap-2 text-sm text-slate-500 mb-4">
            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menunggu konfirmasi pembayaran...
        </div>


        <div id="qr-status-paid" class="hidden items-center justify-center gap-2 text-sm font-semibold text-emerald-600 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Pembayaran Diterima!
        </div>

        <p class="text-xs text-center text-slate-400 mb-5">
            Tab Xendit tertutup?
            <button onclick="reopenInvoice()" class="text-blue-500 hover:underline font-medium">Buka kembali</button>
        </p>

        <div class="space-y-2">

            <button id="btn-cancel-qr" onclick="cancelQrPayment()"
                class="w-full bg-slate-100 text-slate-700 font-semibold py-2.5 rounded-lg hover:bg-slate-200 transition-colors text-sm">
                Batal Transaksi
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/kasir.js'])
@endpush
