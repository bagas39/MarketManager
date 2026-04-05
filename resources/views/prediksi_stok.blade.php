@extends('layouts.app')

@section('title', 'Prediksi Stok - Swalayan Segar')

@section('content')
<div class="container mx-auto px-4 py-6">
    <header class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Prediksi Stok Habis (Otomatis)</h1>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <div class="lg:col-span-5">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
                <h2 class="text-xl font-black mb-8 text-gray-800 tracking-tight">
                    PARAMETER PREDIKSI
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Pilih Produk</label>
                        <select id="barangId" class="block w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none font-medium text-gray-700">
                            <option value="">Memuat data produk...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-3">Periode Analisis</label>
                        <div class="relative">
                            <input type="number" id="periode" value="30" 
                                class="block w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl outline-none font-medium text-gray-700 focus:ring-2 focus:ring-green-500/20 transition-all">
                            <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-bold text-xs uppercase tracking-widest">Hari</span>
                            </div>
                        </div>
                    </div>

                    <button onclick="hitungPrediksi()" id="btn-hitung" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-5 rounded-2xl transition-all active:scale-95">
                        <span>Hitung Analisis</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7" id="hasilBox">
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 text-center min-h-[400px] flex flex-col items-center justify-center">
                <p class="text-gray-400 font-bold text-sm uppercase tracking-widest">Siap Menganalisis</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/prediksi_stok.js'])
@endpush