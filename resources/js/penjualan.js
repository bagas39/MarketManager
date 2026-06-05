const tbody = document.getElementById('sales-table-body');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const pageInfo = document.getElementById('page-info');
const prevButton = document.getElementById('prev-button');
const nextButton = document.getElementById('next-button');

let currentEditItems = [];
let allProducts = [];
let currentPage = 1;
const itemsLimit = 15;
let totalAvailableTransactions = 0;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const escapeHtml = window.escapeHtml || function(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/`/g, '&#96;');
};

function formatIDR(num) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

async function fetchProducts() {
    try {
        const response = await fetch('/api/barang');
        allProducts = await response.json();
    } catch (error) {
        console.error("Gagal load master barang", error);
    }
}

function updatePaginationControls(loadedCount) {
    if (!pageInfo) return;

    const total = totalAvailableTransactions;
    const startItem = total > 0 ? ((currentPage - 1) * itemsLimit) + 1 : 0;
    const endItem = total > 0 ? startItem + loadedCount - 1 : 0;

    pageInfo.textContent = `Menampilkan ${startItem}-${endItem} dari ${total} transaksi`;

    if (prevButton) prevButton.disabled = currentPage <= 1;
    if (nextButton) nextButton.disabled = currentPage * itemsLimit >= total;
}

async function loadSales(page = 1) {
    if (!tbody) return;
    currentPage = page;
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Mencari data transaksi...</td></tr>';
    
    const searchId = searchInput.value.trim();
    const startDate = document.getElementById('start-date')?.value || '';
    const endDate = document.getElementById('end-date')?.value || '';
    const params = new URLSearchParams({ limit: itemsLimit.toString(), page: currentPage.toString() });
    if (searchId) params.append('search_id', searchId);
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    const url = `/api/transaksi_penjualan?${params.toString()}`;

    try {
        const response = await fetch(url);
        const data = await response.json();
        tbody.innerHTML = '';
        totalAvailableTransactions = data.totalAvailableTransactions || 0;
        
        if (data.transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Data transaksi tidak ditemukan.</td></tr>';
            updatePaginationControls(0);
            return;
        }

        data.transactions.forEach(sale => {
            tbody.innerHTML += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 font-semibold text-slate-800">#${escapeHtml(sale.id_penjualan)}</td>
                    <td class="px-4 py-3 text-slate-600">${escapeHtml(sale.tanggal_penjualan)}</td>
                    <td class="px-4 py-3 text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                            ${escapeHtml(sale.nama_kasir)}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold text-emerald-600 text-right">${formatIDR(sale.total_harga)}</td>
                    <td class="px-4 py-3 text-center space-x-1">
                        <button onclick="viewDetail('${String(sale.id_penjualan).replace(/'/g, "\\'")}')" class="inline-flex items-center justify-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">Detail</button>
                        <button onclick="openEditModal('${String(sale.id_penjualan).replace(/'/g, "\\'")}')" class="inline-flex items-center justify-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-600 transition hover:bg-amber-100">Edit</button>
                    </td>
                </tr>
            `;
        });
        updatePaginationControls(data.transactions.length);
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">Gagal terhubung ke server.</td></tr>';
        if (pageInfo) pageInfo.textContent = 'Gagal memuat data transaksi';
    }
}

window.viewDetail = async function(id) {
    const modal = document.getElementById('detail-modal');
    const modalBody = document.getElementById('modal-body');
    modal.classList.remove('hidden'); modal.classList.add('flex');
    document.getElementById('modal-title').textContent = `Detail Transaksi`;
    modalBody.innerHTML = '<div class="text-center py-4 text-slate-500">Memuat detail...</div>';

    try {
        const response = await fetch(`/api/transaksi_detail/${id}`);
        const data = await response.json();
        
        let itemsHtml = '';
            data.items.forEach(item => {
            const hargaSatuan = item.harga_jual ? item.harga_jual : (item.subtotal / item.jumlah);
            const subtotalItem = item.harga_jual ? (item.harga_jual * item.jumlah) : item.subtotal;
            itemsHtml += `
                <div class="flex justify-between items-center py-2 border-b border-slate-100 last:border-0">
                    <div><p class="font-medium text-slate-800">${escapeHtml(item.nama_barang)}</p><p class="text-xs text-slate-500">${escapeHtml(item.jumlah)} x ${formatIDR(hargaSatuan)}</p></div>
                    <div class="font-semibold text-slate-700">${formatIDR(subtotalItem)}</div>
                </div>`;
        });

        modalBody.innerHTML = `
                <div class="mb-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                <div class="flex justify-between mb-1"><span>Kasir:</span> <span class="font-semibold text-slate-800">${escapeHtml(data.header.nama_kasir)}</span></div>
                <div class="flex justify-between"><span>Waktu:</span> <span>${escapeHtml(data.header.tanggal_penjualan)}</span></div>
            </div>
            <h4 class="mb-2 font-bold text-slate-700 border-b pb-2">Rincian Pembelian</h4>
            <div class="mb-4">${itemsHtml}</div>
            <div class="rounded-lg bg-emerald-50 p-3 flex justify-between items-center mt-4">
                <span class="font-bold text-emerald-800">Total Bayar</span>
                <span class="text-xl font-black text-emerald-600">${formatIDR(data.summary.total)}</span>
            </div>`;
    } catch (error) { modalBody.innerHTML = '<div class="text-center py-4 text-red-500">Gagal memuat detail transaksi.</div>'; }
};

window.hideDetailModal = function() {
    document.getElementById('detail-modal').classList.add('hidden');
    document.getElementById('detail-modal').classList.remove('flex');
};

window.openEditModal = async function(id) {
    const modal = document.getElementById('edit-modal');
    document.getElementById('edit-trx-id').value = id;
    document.getElementById('edit-modal-title').textContent = `#${id}`;
    document.getElementById('edit-items-body').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-slate-500">Memuat...</td></tr>';
    
    modal.classList.remove('hidden'); modal.classList.add('flex');

    try {
        const response = await fetch(`/api/transaksi_detail/${id}`);
        const data = await response.json();
        
        currentEditItems = data.items.map(i => ({
            id_barang: i.id_barang || i.ID_BARANG, 
            nama_barang: i.nama_barang || i.NAMA_BARANG,
            jumlah: i.jumlah || i.JUMLAH,
            harga_jual: i.harga_jual || (i.subtotal / i.jumlah)
        }));
        renderEditItems();
    } catch (error) {
        alert("Gagal memuat data transaksi");
    }
};

window.hideEditModal = function() {
    document.getElementById('edit-modal').classList.add('hidden');
    document.getElementById('edit-modal').classList.remove('flex');
};

window.renderEditItems = function() {
    const tbody = document.getElementById('edit-items-body');
    tbody.innerHTML = '';
    let total = 0;

    if (currentEditItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-red-500 text-xs">Transaksi tidak boleh kosong. Tambahkan item.</td></tr>';
    }

    currentEditItems.forEach((item, index) => {
        const subtotal = item.harga_jual * item.jumlah;
        total += subtotal;
        tbody.innerHTML += `
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="p-2 text-sm font-medium text-slate-700">${item.nama_barang}</td>
                <td class="p-2 text-sm text-right text-slate-600">${formatIDR(item.harga_jual)}</td>
                <td class="p-2 text-center">
                    <input type="number" value="${item.jumlah}" min="1" onchange="updateItemQty(${index}, this.value)" class="w-16 border border-slate-300 rounded px-2 py-1 text-center focus:outline-none focus:border-emerald-500 text-sm">
                </td>
                <td class="p-2 text-sm font-semibold text-emerald-600 text-right">${formatIDR(subtotal)}</td>
                <td class="p-2 text-center">
                    <button onclick="removeEditItem(${index})" class="text-red-500 hover:bg-red-50 px-2 py-1 rounded transition">&times;</button>
                </td>
            </tr>`;
    });
    document.getElementById('edit-total-display').textContent = formatIDR(total);
};

window.updateItemQty = function(index, val) {
    const newVal = parseInt(val);
    if(newVal < 1) { alert("Jumlah minimal 1"); renderEditItems(); return; }
    currentEditItems[index].jumlah = newVal;
    renderEditItems();
};

window.removeEditItem = function(index) {
    currentEditItems.splice(index, 1);
    renderEditItems();
};

window.addNewItemToEdit = function() {
    const idInput = document.getElementById('new-item-id');
    const qtyInput = document.getElementById('new-item-qty');
    const searchValue = idInput.value.trim();
    const qty = parseInt(qtyInput.value);

    if(!searchValue || !qty || qty < 1) return alert("Isi kode barang dan jumlah dengan benar");

    const normalizedSearch = searchValue.toLowerCase();
    const product = allProducts.find(p => {
        const idBarang = String(p.id_barang ?? '').toLowerCase();
        const kodeBarang = String(p.kode_barang ?? '').toLowerCase();
        const namaBarang = String(p.nama_barang ?? '').toLowerCase();
        return idBarang === normalizedSearch || kodeBarang === normalizedSearch || namaBarang === normalizedSearch;
    });
    if (!product) return alert(`Barang dengan kode/ID ${searchValue} tidak ditemukan di master data!`);

    const itemId = product.id_barang ?? product.ID_BARANG ?? product.id;
    const existIndex = currentEditItems.findIndex(i => String(i.id_barang) === String(itemId));
    if(existIndex !== -1) {
        currentEditItems[existIndex].jumlah += qty;
    } else {
        currentEditItems.push({ 
            id_barang: itemId, 
            nama_barang: product.nama_barang, 
            harga_jual: product.harga_jual,
            jumlah: qty 
        });
    }

    idInput.value = ''; qtyInput.value = '1';
    renderEditItems();
};

window.saveEditTransaction = async function() {
    const id = document.getElementById('edit-trx-id').value;
    if (currentEditItems.length === 0) return alert("Tidak bisa menyimpan transaksi kosong.");

    const btn = document.getElementById('btn-save-edit');
    btn.disabled = true; btn.textContent = "Menyimpan...";

    try {
        const response = await fetch(`/api/transaksi/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ items: currentEditItems })
        });
        
        const result = await response.json();
        if(result.success) {
            hideEditModal();
            loadSales();
            alert("Berhasil memperbarui transaksi!");
        } else {
            alert(result.message || "Gagal update.");
        }
    } catch (err) {
        alert("Terjadi kesalahan jaringan.");
    } finally {
        btn.disabled = false; btn.textContent = "Simpan Perubahan";
    }
};

searchBtn?.addEventListener('click', () => loadSales(1));
document.getElementById('start-date')?.addEventListener('change', () => loadSales(1));
document.getElementById('end-date')?.addEventListener('change', () => loadSales(1));
searchInput?.addEventListener('keyup', (e) => { if (e.key === 'Enter') loadSales(1); });
prevButton?.addEventListener('click', () => {
    if (currentPage > 1) loadSales(currentPage - 1);
});
nextButton?.addEventListener('click', () => {
    if (currentPage * itemsLimit < totalAvailableTransactions) loadSales(currentPage + 1);
});

if (tbody) {
    fetchProducts();
    loadSales(1);
}