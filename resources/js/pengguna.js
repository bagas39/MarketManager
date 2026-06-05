const tbody = document.getElementById('users-table-body');
const mobileList = document.getElementById('users-mobile-list');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
let currentUsers = [];

const escapeHtml = window.escapeHtml || function(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/`/g, '&#96;');
};

async function loadUsers() {
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Memuat data...</td></tr>';
    if (mobileList) {
        mobileList.innerHTML = '<div class="rounded-xl border border-slate-200 bg-white p-4 text-slate-400 shadow-sm">Memuat data...</div>';
    }
    try {
        const response = await fetch('/api/users');
        currentUsers = await response.json();
        renderTable();
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">Gagal memuat data.</td></tr>';
        if (mobileList) {
            mobileList.innerHTML = '<div class="rounded-xl border border-red-200 bg-white p-4 text-red-500 shadow-sm">Gagal memuat data.</div>';
        }
    }
}

function roleBadgeClass(role) {
    if (role === 'Owner') return 'bg-purple-100 text-purple-700';
    if (role === 'Supervisor') return 'bg-blue-100 text-blue-700';
    if (role === 'Kasir') return 'bg-emerald-100 text-emerald-700';
    return 'bg-slate-100 text-slate-700';
}

function renderTable() {
    tbody.innerHTML = '';
    if (mobileList) {
        mobileList.innerHTML = '';
    }
    if (currentUsers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada pengguna.</td></tr>';
        if (mobileList) {
            mobileList.innerHTML = '<div class="rounded-xl border border-slate-200 bg-white p-4 text-center text-slate-500 shadow-sm">Belum ada pengguna.</div>';
        }
        return;
    }

    currentUsers.forEach(user => {
        const roleColor = roleBadgeClass(user.role);
        tbody.innerHTML += `
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3 text-sm text-slate-600">${escapeHtml(user.id)}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-800">${escapeHtml(user.username)}</td>
                <td class="px-4 py-3 text-sm text-slate-600">${escapeHtml(user.nama)}</td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    <span class="px-2 py-1 text-xs font-bold rounded-full ${roleColor}">${escapeHtml(user.role)}</span>
                </td>
                <td class="px-4 py-3 text-center space-x-1">
                    <button data-action="edit" data-user-id="${escapeHtml(user.id)}" class="px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-md transition">Edit</button>
                    <button data-action="delete" data-user-id="${escapeHtml(user.id)}" data-username=${JSON.stringify(user.username)} class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">Hapus</button>
                </td>
            </tr>
        `;

        if (mobileList) {
            mobileList.innerHTML += `
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">ID ${escapeHtml(user.id)}</p>
                            <h3 class="mt-1 text-base font-bold text-slate-800 break-words">${escapeHtml(user.username)}</h3>
                            <p class="mt-1 text-sm text-slate-600 break-words">${escapeHtml(user.nama)}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-1 text-xs font-bold ${roleColor}">${escapeHtml(user.role)}</span>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button data-action="edit" data-user-id="${escapeHtml(user.id)}" class="flex-1 rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-600 transition hover:bg-amber-100">Edit</button>
                        <button data-action="delete" data-user-id="${escapeHtml(user.id)}" data-username=${JSON.stringify(user.username)} class="flex-1 rounded-lg bg-red-50 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
                    </div>
                </div>
            `;
        }
    });
}

window.openAddModal = function() {
    document.getElementById('add-form').reset();
    document.getElementById('add-modal').classList.remove('hidden');
    document.getElementById('add-modal').classList.add('flex');
};

window.closeAddModal = function() {
    document.getElementById('add-modal').classList.add('hidden');
    document.getElementById('add-modal').classList.remove('flex');
};

window.saveUser = async function() {
    const btn = document.getElementById('btn-save');
    const username = document.getElementById('add-username').value.trim();
    const password = document.getElementById('add-password').value;
    const nama = document.getElementById('add-nama').value.trim();
    const role = document.getElementById('add-role').value;

    if(!username || !nama || !role || !password) return alert("Semua kolom wajib diisi termasuk password!");

    btn.disabled = true; btn.textContent = "Menyimpan...";
    try {
        const res = await fetch('/api/users', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ username, password, nama, role })
        });
        const data = await res.json();
        if(res.ok && data.success) {
            closeAddModal();
            loadUsers();
            alert("User berhasil ditambahkan!");
        } else {
            alert(data.message || "Gagal menyimpan user.");
        }
    } catch (e) {
        alert("Error jaringan.");
    } finally {
        btn.disabled = false; btn.textContent = "Simpan";
    }
};

window.openEditModal = function(id) {
    const user = currentUsers.find(u => u.id === id);
    if(!user) return;

    document.getElementById('edit-id').value = user.id;
    document.getElementById('edit-username').value = user.username;
    document.getElementById('edit-password').value = '';
    document.getElementById('edit-nama').value = user.nama;
    document.getElementById('edit-role').value = user.role;

    document.getElementById('edit-modal').classList.remove('hidden');
    document.getElementById('edit-modal').classList.add('flex');
};

window.closeEditModal = function() {
    document.getElementById('edit-modal').classList.add('hidden');
    document.getElementById('edit-modal').classList.remove('flex');
};

window.updateUser = async function() {
    const btn = document.getElementById('btn-update');
    const id = document.getElementById('edit-id').value;
    const username = document.getElementById('edit-username').value.trim();
    const password = document.getElementById('edit-password').value;
    const nama = document.getElementById('edit-nama').value.trim();
    const role = document.getElementById('edit-role').value;

    if(!username || !nama || !role) return alert("Username, Nama, dan Role wajib diisi!");

    btn.disabled = true; btn.textContent = "Menyimpan...";
    
    const payload = { username, nama, role };
    if (password !== '') {
        payload.password = password; 
    }

    try {
        const res = await fetch(`/api/users/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if(res.ok && data.success) {
            closeEditModal();
            loadUsers();
            alert("User berhasil diperbarui!");
        } else {
            alert(data.message || "Gagal update user.");
        }
    } catch (e) {
        alert("Error jaringan.");
    } finally {
        btn.disabled = false; btn.textContent = "Update";
    }
};

window.deleteUser = async function(id, username) {
    if(!confirm(`Yakin ingin menghapus user '${username}'?`)) return;

    try {
        const res = await fetch(`/api/users/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        if(res.ok && data.success) {
            loadUsers();
        } else {
            alert(data.message || "Gagal menghapus user.");
        }
    } catch (e) {
        alert("Error jaringan.");
    }
};

document.addEventListener('DOMContentLoaded', loadUsers);

function handleUserActionClick(e) {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-action');
    const userId = btn.getAttribute('data-user-id');
    const username = btn.getAttribute('data-username') || btn.getAttribute('data-username');

    if (action === 'edit') {
        openEditModal(Number(userId));
    } else if (action === 'delete') {
        let uname = username;
        try { uname = JSON.parse(username); } catch (ignored) {  }
        deleteUser(Number(userId), uname);
    }
}

if (tbody) tbody.addEventListener('click', handleUserActionClick);
if (mobileList) mobileList.addEventListener('click', handleUserActionClick);