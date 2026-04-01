@extends('layouts.app')
@section('title', 'Manajemen Pengguna - Swalayan Segar')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-800">Manajemen Pengguna</h1>
        <p class="text-slate-500 mt-1">Kelola data akses pegawai dan hak akses sistem.</p>
    </div>
    <button onclick="openAddModal()" class="bg-emerald-600 text-white px-5 py-2.5 rounded-lg font-bold hover:bg-emerald-700 shadow-sm transition-all flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah User
    </button>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Username</th>
                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Nama Lengkap</th>
                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase">Role / Hak Akses</th>
                    <th class="px-4 py-3 text-xs font-bold text-slate-500 uppercase text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="users-table-body" class="divide-y divide-slate-100 bg-white">
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="add-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Tambah Pengguna Baru</h3>
        <form id="add-form" onsubmit="event.preventDefault(); saveUser();">
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Username (Untuk Login)</label>
                    <input type="text" id="add-username" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
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
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 rounded-lg bg-slate-100 font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" id="btn-save" class="px-4 py-2 rounded-lg bg-emerald-600 font-bold text-white hover:bg-emerald-700 transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-slate-800 mb-4 border-b pb-2">Edit Pengguna</h3>
        <form onsubmit="event.preventDefault(); updateUser();">
            <input type="hidden" id="edit-id">
            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-1">Username (Untuk Login)</label>
                    <input type="text" id="edit-username" required class="w-full px-3 py-2 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 focus:outline-none">
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
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-lg bg-slate-100 font-semibold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" id="btn-update" class="px-4 py-2 rounded-lg bg-emerald-600 font-bold text-white hover:bg-emerald-700 transition shadow-sm">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/pengguna.js'])
@endpush