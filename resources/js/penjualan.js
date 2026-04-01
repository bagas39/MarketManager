const tbody = document.getElementById('sales-table-body');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const pageInfo = document.getElementById('page-info');

let currentEditItems = [];
let allProducts = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

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

async function loadSales() {
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Mencari data transaksi...</td></tr>';
    
    const searchId = searchInput.value.trim();
    let url = '/api/transaksi_penjualan';
    if (searchId) url += `?search_id=${searchId}`;

    try {
        const response = await fetch(url);
        const data = await response.json();
        tbody.innerHTML = '';
        
        if (data.transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Data transaksi tidak ditemukan.</td></tr>';
            pageInfo.textContent = 'Menampilkan 0 data';
            return;
        }

        data.transactions.forEach(sale => {
            tbody.innerHTML += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 font-semibold text-slate-800">#${sale.id_penjualan}</td>
                    <td class="px-4 py-3 text-slate-600">${formatDate(sale.tanggal_penjualan)}</td>
                    <td class="px-4 py-3 text-slate-600">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                            ${sale.nama_kasir}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold text-emerald-600 text-right">${formatIDR(sale.total_harga)}</td>
                    <td class="px-4 py-3 text-center space-x-1">
                        <button onclick="viewDetail(${sale.id_penjualan})" class="inline-flex items-center justify-center rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">Detail</button>
                        <button onclick="openEditModal(${sale.id_penjualan})" class="inline-flex items-center justify-center rounded-md bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-600 transition hover:bg-amber-100">Edit</button>
                    </td>
                </tr>
            `;
        });
        pageInfo.textContent = `Menampilkan ${data.transactions.length} dari ${data.totalAvailableTransactions} transaksi`;
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-6 text-center text-red-500">Gagal terhubung ke server.</td></tr>';
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
                    <div><p class="font-medium text-slate-800">${item.nama_barang}</p><p class="text-xs text-slate-500">${item.jumlah} x ${formatIDR(hargaSatuan)}</p></div>
                    <div class="font-semibold text-slate-700">${formatIDR(subtotalItem)}</div>
                </div>`;
        });

        modalBody.innerHTML = `
            <div class="mb-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                <div class="flex justify-between mb-1"><span>Kasir:</span> <span class="font-semibold text-slate-800">${data.header.nama_kasir}</span></div>
                <div class="flex justify-between"><span>Waktu:</span> <span>${formatDate(data.header.tanggal_penjualan)}</span></div>
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
    const id = parseInt(idInput.value);
    const qty = parseInt(qtyInput.value);

    if(!id || !qty || qty < 1) return alert("Isi ID dan Jumlah dengan benar");

    const product = allProducts.find(p => p.id_barang === id);
    if (!product) return alert(`Barang dengan ID ${id} tidak ditemukan di master data!`);

    const existIndex = currentEditItems.findIndex(i => i.id_barang === id);
    if(existIndex !== -1) {
        currentEditItems[existIndex].jumlah += qty;
    } else {
        currentEditItems.push({ 
            id_barang: id, 
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

searchBtn?.addEventListener('click', loadSales);
searchInput?.addEventListener('keyup', (e) => { if (e.key === 'Enter') loadSales(); });

if (tbody) {
    fetchProducts();
    loadSales();
}