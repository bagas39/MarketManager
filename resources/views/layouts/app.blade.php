<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MarketManager')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
    <style>
        .force-md { display: none; }
        body.force-md-active .force-md { display: table-cell !important; }
        body.force-md-active .force-md-block { display: block !important; }
        body.force-md-active .force-md-hidden { display: none !important; }
        @media (min-width: 769px) {
            .force-md { display: table-cell !important; }
            .force-md-block { display: block !important; }
            .force-md-hidden { display: none !important; }
        }
    </style>

    @vite(['resources/css/app.css'])
    
    @stack('styles')
</head>
<body class="bg-gray-100">

    <div class="flex min-h-screen overflow-x-hidden">
        
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

        <nav id="sidebar" class="fixed inset-y-0 left-0 z-40 flex h-full w-64 -translate-x-full flex-col bg-gray-900 text-white transition-transform duration-300 lg:translate-x-0">
            <div class="flex items-start justify-between border-b border-gray-700 px-6 py-5 lg:justify-start">
                <div>
                    <div class="text-2xl font-bold">MarketManager</div>
                    <div class="mt-1 text-sm font-normal text-gray-200">
                        Halo, {{ Auth::check() ? Auth::user()->name : 'Guest' }} <br>
                        <span class="text-xs text-gray-400">({{ Auth::check() ? Auth::user()->role : '-' }})</span>
                    </div>
                </div>
                <button id="sidebar-close-button" type="button" class="rounded-lg bg-gray-800 p-2 text-gray-200 hover:bg-gray-700 lg:hidden" aria-label="Tutup menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <ul class="flex-1 py-4 space-y-2">
                @if(in_array(Auth::user()?->role, ['Kasir', 'Supervisor']))
                <li>
                    <a href="{{ url('/') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('/') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h12A2.25 2.25 0 0 0 20.25 14.25V3m-16.5 0h16.5m-16.5 0v6.75A2.25 2.25 0 0 0 6 12h12a2.25 2.25 0 0 0 2.25-2.25V3" /></svg>
                        <span>Kasir</span>
                    </a>
                </li>
                @endif

                @if(in_array(Auth::user()?->role, ['Supervisor']))
                <li>
                    <a href="{{ url('/transaksi_penjualan') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('transaksi_penjualan') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                        <span>Transaksi Penjualan</span>
                    </a>
                </li>
                @endif

                @if(in_array(Auth::user()?->role, ['Kasir', 'Gudang', 'Supervisor']))
                <li>
                    <a href="{{ url('/manajemen_stok') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('manajemen_stok') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        <span>Manajemen Stok</span>
                    </a>
                </li>
                @endif

                @if(in_array(Auth::user()?->role, ['Gudang', 'Supervisor']))
                <li>
                    <a href="{{ url('/transaksi_pembelian') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('transaksi_pembelian') ? 'bg-green-600 text-white font-semibold shadow-md' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296A3.745 3.745 0 0 1 16.5 21a3.745 3.745 0 0 1-12 21a3.745 3.745 0 0 1-2.863-1.636A3.745 3.745 0 0 1 7.5 21a3.745 3.745 0 0 1-2.863-1.636A3.745 3.745 0 0 1 3 18.068A3.745 3.745 0 0 1 1.5 15c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296A3.745 3.745 0 0 1 7.5 3a3.745 3.745 0 0 1 2.863 1.636A3.745 3.745 0 0 1 12 3a3.745 3.745 0 0 1 2.863 1.636A3.745 3.745 0 0 1 16.5 3a3.745 3.745 0 0 1 2.863 1.636A3.745 3.745 0 0 1 21 5.932A3.745 3.745 0 0 1 22.5 9c0 1.268-.63 2.39-1.593 3.068A3.745 3.745 0 0 1 21 12Z" /></svg>
                        <span>Transaksi Pembelian</span>
                    </a>
                </li>
                @endif
                
                @if(in_array(Auth::user()?->role, ['Owner']))
                <li>
                    <a href="{{ url('/laporan_keuangan') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('laporan_keuangan') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h15.75c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125H4.125c-.621 0-1.125-.504-1.125-1.125v-6.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125v-6.75a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6.375v6.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12c0-1.657-1.343-3-3-3s-3 1.343-3 3h6Z" /></svg>
                        <span>Laporan Keuangan</span>
                    </a>
                </li>
                @endif

                @if(in_array(Auth::user()?->role, ['Owner', 'Supervisor']))
                <li>
                    <a href="{{ url('/manajemen_pengguna') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('manajemen_pengguna') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span>Manajemen Pengguna</span>
                    </a>
                </li>
                @endif
                
                @if(in_array(Auth::user()?->role, ['Gudang', 'Supervisor']))
                <li>
                    <a href="{{ url('/stok_opname') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('stok_opname') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .415.162.798.425 1.087.262.288.625.463 1.025.463s.763-.175 1.025-.463C15.938 5.462 16.1 5.079 16.1 4.664c0-.231-.035-.454-.1-.664m-5.8 0A4.26 4.26 0 0 0 9.124 3.75c-1.132.094-1.977 1.057-1.977 2.192v12.75A2.25 2.25 0 0 0 9.375 21h.375" />
                        </svg>
                        <span>Stok Opname</span>
                    </a>
                </li>
                @endif

                @if(in_array(Auth::user()?->role, ['Gudang', 'Supervisor']))
                <li>
                    <a href="{{ url('/prediksi_stok') }}" class="flex items-center space-x-3 px-6 py-3 rounded-lg mx-3 transition-colors {{ Request::is('prediksi_stok') ? 'bg-green-600 text-white font-semibold' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span>Prediksi Stok</span>
                    </a>
                </li>
                @endif

            </ul>
            
            <div class="p-4 border-t border-gray-800 mt-auto">
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-6 py-3 text-red-400 hover:bg-gray-800 rounded-lg transition text-left">
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </nav>

        <main class="flex-1 bg-gray-50 lg:ml-64">
            @unless(View::hasSection('has_mobile_header'))
            <div class="border-b border-gray-200 bg-white px-4 py-3 shadow-sm lg:hidden">
                <button id="sidebar-open-button" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50" aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5" /></svg>
                    Menu
                </button>
            </div>
            @endunless
            <div class="overflow-y-auto p-4 sm:p-6 md:p-8 pb-24">
                @yield('content')
                <div class="h-24 lg:hidden" aria-hidden="true"></div>
            </div>
        </main>
    </div>

    @vite(['resources/js/app.js'])
    @stack('scripts')
    
</body>
</html>