const tbody = document.getElementById('users-table-body');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
let currentUsers = [];

async function loadUsers() {
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Memuat data...</td></tr>';
    try {
        const response = await fetch('/api/users');
        currentUsers = await response.json();
        renderTable();
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">Gagal memuat data.</td></tr>';
    }
}

function renderTable() {
    tbody.innerHTML = '';
    if (currentUsers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada pengguna.</td></tr>';
        return;
    }

    currentUsers.forEach(user => {
        let roleColor = 'bg-slate-100 text-slate-700';
        if(user.role === 'Owner') roleColor = 'bg-purple-100 text-purple-700';
        if(user.role === 'Supervisor') roleColor = 'bg-blue-100 text-blue-700';
        if(user.role === 'Kasir') roleColor = 'bg-emerald-100 text-emerald-700';

        tbody.innerHTML += `
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-3 text-sm text-slate-600">${user.id}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-800">${user.username}</td>
                <td class="px-4 py-3 text-sm text-slate-600">${user.nama}</td>
                <td class="px-4 py-3 text-sm text-slate-600">
                    <span class="px-2 py-1 text-xs font-bold rounded-full ${roleColor}">${user.role}</span>
                </td>
                <td class="px-4 py-3 text-center space-x-1">
                    <button onclick="openEditModal(${user.id})" class="px-3 py-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-md transition">Edit</button>
                    <button onclick="deleteUser(${user.id}, '${user.username}')" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition">Hapus</button>
                </td>
            </tr>
        `;
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
    const nama = document.getElementById('add-nama').value.trim();
    const role = document.getElementById('add-role').value;

    if(!username || !nama || !role) return alert("Semua kolom wajib diisi!");

    btn.disabled = true; btn.textContent = "Menyimpan...";
    try {
        const res = await fetch('/api/users', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ username, nama, role })
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
    const nama = document.getElementById('edit-nama').value.trim();
    const role = document.getElementById('edit-role').value;

    if(!username || !nama || !role) return alert("Semua kolom wajib diisi!");

    btn.disabled = true; btn.textContent = "Menyimpan...";
    try {
        const res = await fetch(`/api/users/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ username, nama, role })
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