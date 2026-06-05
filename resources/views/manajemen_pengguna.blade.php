@extends('layouts.app')
@section('title', 'Manajemen Pengguna - MarketManager')

@section('content')
    @include('components.mobile-header', ['title' => 'Manajemen Pengguna'])
<div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 sm:text-3xl">Manajemen Pengguna</h1>
        <p class="mt-1 text-sm text-slate-500 sm:text-base">Kelola data akses pegawai dan hak akses sistem.</p>
    </div>
    <button onclick="openAddModal()" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 sm:px-5 sm:text-base lg:w-auto">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah User
    </button>
</div>

<div class="space-y-3 md:hidden force-md-hidden" id="users-mobile-list">
    <div class="rounded-xl border border-slate-200 bg-white p-4 text-slate-400 shadow-sm">Memuat data...</div>
</div>

    <div class="hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm md:block force-md-block">
        <div class="overflow-x-auto">
            <x-table-wrapper minWidth="680px" class="text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <x-table-th>ID</x-table-th>
                        <x-table-th>Username</x-table-th>
                        <x-table-th>Nama Lengkap</x-table-th>
                        <x-table-th>Role / Hak Akses</x-table-th>
                        <x-table-th align="center">Aksi</x-table-th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="divide-y divide-slate-100 bg-white">
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Memuat data...</td></tr>
                </tbody>
            </x-table-wrapper>
        </div>
    </div>

<div id="add-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-2xl sm:p-6">
        <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Tambah Pengguna Baru</h3>
        <form id="add-form" onsubmit="event.preventDefault(); saveUser();">
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Username (Untuk Login)</label>
                    <input type="text" id="add-username" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Password</label>
                    <input type="password" id="add-password" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" id="add-nama" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Role / Hak Akses</label>
                    <select id="add-role" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                        <option value="Kasir">Kasir</option>
                        <option value="Gudang">Gudang</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Owner">Owner</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeAddModal()" class="rounded-lg bg-slate-100 px-4 py-2 font-semibold text-slate-600 transition hover:bg-slate-200">Batal</button>
                <button type="submit" id="btn-save" class="rounded-lg bg-emerald-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-emerald-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-xl bg-white p-4 shadow-2xl sm:p-6">
        <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Edit Pengguna</h3>
        <form onsubmit="event.preventDefault(); updateUser();">
            <input type="hidden" id="edit-id">
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Username (Untuk Login)</label>
                    <input type="text" id="edit-username" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Password Baru <span class="text-xs font-normal text-slate-400">(Kosongkan jika tidak diubah)</span></label>
                    <input type="password" id="edit-password" class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" id="edit-nama" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Role / Hak Akses</label>
                    <select id="edit-role" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                        <option value="Kasir">Kasir</option>
                        <option value="Gudang">Gudang</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Owner">Owner</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeEditModal()" class="rounded-lg bg-slate-100 px-4 py-2 font-semibold text-slate-600 transition hover:bg-slate-200">Batal</button>
                <button type="submit" id="btn-update" class="rounded-lg bg-emerald-600 px-4 py-2 font-bold text-white shadow-sm transition hover:bg-emerald-700">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pengguna.js'])
@endpush