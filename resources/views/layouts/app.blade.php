<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Swalayan Segar')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>

    @vite(['resources/css/app.css'])
    
    @stack('styles')
</head>
<body class="bg-gray-100">

    <div class="flex h-screen overflow-hidden">
        
        <nav class="w-64 h-full bg-gray-900 text-white flex flex-col fixed">
            <div class="px-6 py-5 border-b border-gray-700">
                <div class="text-2xl font-bold">SWALAYAN SEGAR</div>
                <div class="text-m text-gray-200 mt-1 font-normal">
                    Halo, {{ session('user_name') ?? 'Guest' }} <br>
                    <span class="text-xs text-gray-400">({{ session('user_role') ?? '-' }})</span>
                </div>
            </div>
            
            <ul class="flex-1 py-4 space-y-2">
                @if(in_array(session('user_role'), ['Kasir', 'Supervisor']))
                <li>
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('/') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h12A2.25 2.25 0 0 0 20.25 14.25V3m-16.5 0h16.5m-16.5 0v6.75A2.25 2.25 0 0 0 6 12h12a2.25 2.25 0 0 0 2.25-2.25V3" /></svg>
                        <span>Kasir</span>
                    </a>
                </li>
                @endif

                @if(in_array(session('user_role'), ['Supervisor']))
                <li>
                    <a href="{{ url('/transaksi_penjualan') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('transaksi_penjualan') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                        <span>Transaksi Penjualan</span>
                    </a>
                </li>
                @endif

                @if(in_array(session('user_role'), ['Kasir', 'Gudang', 'Supervisor']))
                <li>
                    <a href="{{ url('/manajemen_stok') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('manajemen_stok') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        <span>Manajemen Stok</span>
                    </a>
                </li>
                @endif

                @if(in_array(session('user_role'), ['Gudang', 'Supervisor']))
                <li>
                    <a href="{{ url('/transaksi_pembelian') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('transaksi_pembelian') ? 'bg-green-600 text-white font-semibold shadow-md' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296A3.745 3.745 0 0 1 16.5 21a3.745 3.745 0 0 1-12 21a3.745 3.745 0 0 1-2.863-1.636A3.745 3.745 0 0 1 7.5 21a3.745 3.745 0 0 1-2.863-1.636A3.745 3.745 0 0 1 3 18.068A3.745 3.745 0 0 1 1.5 15c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296A3.745 3.745 0 0 1 7.5 3a3.745 3.745 0 0 1 2.863 1.636A3.745 3.745 0 0 1 12 3a3.745 3.745 0 0 1 2.863 1.636A3.745 3.745 0 0 1 16.5 3a3.745 3.745 0 0 1 2.863 1.636A3.745 3.745 0 0 1 21 5.932A3.745 3.745 0 0 1 22.5 9c0 1.268-.63 2.39-1.593 3.068A3.745 3.745 0 0 1 21 12Z" /></svg>
                        <span>Transaksi Pembelian</span>
                    </a>
                </li>
                @endif
                
                @if(in_array(session('user_role'), ['Owner']))
                <li>
                    <a href="{{ url('/laporan_keuangan') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('laporan_keuangan') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h15.75c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125H4.125c-.621 0-1.125-.504-1.125-1.125v-6.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125v-6.75a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6.375v6.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12c0-1.657-1.343-3-3-3s-3 1.343-3 3h6Z" /></svg>
                        <span>Laporan Keuangan</span>
                    </a>
                </li>
                @endif

                @if(in_array(session('user_role'), ['Owner', 'Supervisor']))
                <li>
                    <a href="{{ url('/manajemen_pengguna') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('manajemen_pengguna') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span>Manajemen Pengguna</span>
                    </a>
                </li>
                @endif
                
            </ul>
            
            <div class="p-4 border-t border-gray-800 mt-auto">
                <a href="{{ url('/logout') }}" class="flex items-center space-x-3 px-6 py-3 text-red-400 hover:bg-gray-800 rounded-lg transition">
                    <span>Keluar</span>
                </a>
            </div>
        </nav>

        <main class="flex-1 ml-64 h-full overflow-hidden bg-gray-50">
            <div class="h-full overflow-y-auto p-6 md:p-8">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    
</body>
</html>