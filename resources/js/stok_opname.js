let inventoryData = [];
let currentPage = 1;
let lastPage = 1;
const itemsPerPage = 10;
const messageModalEl = document.getElementById('message-modal');
const messageTitleEl = document.getElementById('message-title');
const messageBodyEl = document.getElementById('message-body');
const confirmModalEl = document.getElementById('confirm-modal');
const confirmTitleEl = document.getElementById('confirm-title');
const confirmBodyEl = document.getElementById('confirm-body');
const historyModalEl = document.getElementById('history-modal');
const historyTableBodyEl = document.getElementById('history-table-body');
const paginationInfoEl = document.getElementById('opname-pagination-info');
const prevPageButtonEl = document.getElementById('opname-prev-page');
const nextPageButtonEl = document.getElementById('opname-next-page');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
let confirmResolver = null;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
async function fetchOpnameData(page = 1) {
    const tbody = document.getElementById('opname-table-body');
    if (!tbody) return;

    try {
        const response = await fetch(`/api/stok_opname/data?page=${page}&limit=${itemsPerPage}`);
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        
        inventoryData = data.items || [];
        currentPage = data.pagination?.current_page || page;
        lastPage = data.pagination?.last_page || 1;
        
        renderTable();
        updatePaginationControls(data.pagination);
    } catch (error) {
        console.error("Gagal mengambil data:", error);
        tbody.innerHTML = `<tr><td colspan="6" class="p-3 text-center text-red-500">Gagal memuat data stok.</td></tr>`;
    }
}

function updatePaginationControls(pagination) {
    if (!paginationInfoEl) return;

    const from = pagination?.from ?? 0;
    const to = pagination?.to ?? 0;
    const total = pagination?.total ?? 0;

    paginationInfoEl.textContent = total > 0
        ? `Menampilkan ${from}-${to} dari ${total} barang (Halaman ${currentPage} dari ${lastPage})`
        : 'Belum ada data barang.';

    if (prevPageButtonEl) prevPageButtonEl.disabled = currentPage <= 1;
    if (nextPageButtonEl) nextPageButtonEl.disabled = currentPage >= lastPage;
}

function renderTable() {
    const tbody = document.getElementById('opname-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    if (inventoryData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Data barang tidak ditemukan.</td></tr>`;
        return;
    }

    inventoryData.forEach((item, index) => {
        const selisih = item.selisih || 0;
        const selisihClass = selisih < 0 ? 'text-red-600' : (selisih > 0 ? 'text-green-600' : 'text-gray-500');
        
                tbody.innerHTML += `
            <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-3 md:px-5 md:py-3 text-sm text-gray-900">${escapeHtml(item.kode_barang || item.id_barang)}</td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-sm text-gray-700 font-medium">${escapeHtml(item.nama_barang)}</td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-sm text-center text-gray-600">${escapeHtml(item.stok_sistem)}</td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-center">
                    <input type="number" 
                                   class="w-16 lg:w-14 border border-gray-300 rounded px-1.5 py-1 text-center text-sm focus:ring-green-500 focus:border-green-500" 
                           value="${escapeHtml(item.stok_fisik)}" 
                           oninput="updateSelisih(${index}, this.value)">
                </td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-center hidden force-md">
                    <input type="text"
                                   class="w-40 lg:w-36 border border-gray-300 rounded px-2 py-1 text-xs lg:text-sm focus:ring-green-500 focus:border-green-500"
                           value="${escapeHtml(item.keterangan || '')}"
                           placeholder="Alasan perubahan"
                           oninput="updateKeterangan(${index}, this.value)">
                </td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-center font-bold ${selisihClass} hidden force-md text-sm" id="selisih-${index}">
                    ${escapeHtml(selisih)}
                </td>
            </tr>
        `;
    });
}

window.updateSelisih = function(index, fisikValue) {
    const fisik = parseInt(fisikValue) || 0;
    const sistem = inventoryData[index].stok_sistem;
    const selisih = fisik - sistem;

    inventoryData[index].stok_fisik = fisik;
    inventoryData[index].selisih = selisih;

    const selisihEl = document.getElementById(`selisih-${index}`);
    if (selisihEl) {
        selisihEl.innerText = selisih;
        selisihEl.className = `px-6 py-4 text-center font-bold ${selisih < 0 ? 'text-red-600' : (selisih > 0 ? 'text-green-600' : 'text-gray-500')}`;
    }
}

window.updateKeterangan = function(index, keteranganValue) {
    inventoryData[index].keterangan = keteranganValue;
}

window.simpanOpname = async function() {
    const saveButton = document.getElementById('save-opname-button');
    
    if (inventoryData.length === 0) {
        showMessage('Peringatan', 'Tidak ada data untuk disimpan.', true);
        return;
    }

    if (lastPage > 1) {
        const confirmSave = await showConfirm(
            'Konfirmasi Simpan',
            `Anda sedang di halaman ${currentPage} dari ${lastPage}. Simpan perubahan hanya untuk halaman ini?`
        );
        if (!confirmSave) {
            return;
        }
    }

    saveButton.disabled = true;
    saveButton.innerText = "Memproses...";

    try {
        const response = await fetch('/api/stok_opname/simpan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ items: inventoryData })
        });

        const result = await response.json();

        if (result.success) {
            showMessage(
                'Sukses', 
                'Hasil stok opname berhasil disimpan. Stok produk telah diperbarui.'
            );
        } else {
            showMessage('Gagal', 'Terjadi kesalahan saat menyimpan data.', true);
        }
    } catch (error) {
        console.error("Error:", error);
        showMessage('Error Sistem', 'Gagal terhubung ke server.', true);
    } finally {
        saveButton.disabled = false;
        saveButton.innerText = "Simpan Hasil Opname Halaman Ini";
    }
}

function showConfirm(title, body) {
    if (!confirmModalEl || !confirmTitleEl || !confirmBodyEl) {
        showMessage('Error Sistem', 'Modal konfirmasi tidak tersedia.', true);
        return Promise.resolve(false);
    }

    confirmTitleEl.textContent = title;
    confirmBodyEl.textContent = body;
    confirmModalEl.classList.remove('hidden');
    confirmModalEl.classList.add('flex');

    return new Promise((resolve) => {
        confirmResolver = resolve;
    });
}

window.resolveOpnameConfirm = function(value) {
    if (confirmResolver) {
        confirmResolver(value);
        confirmResolver = null;
    }

    if (confirmModalEl) {
        confirmModalEl.classList.add('hidden');
        confirmModalEl.classList.remove('flex');
    }
}

window.showMessage = function(title, body, isError = false) {
    messageTitleEl.textContent = title;
    messageBodyEl.textContent = body;
    messageTitleEl.className = isError 
        ? 'text-xl font-semibold text-red-600' 
        : 'text-xl font-semibold text-emerald-600';
    messageModalEl.classList.remove('hidden');
    messageModalEl.classList.add('flex');
}

window.hideMessage = function() {
    messageModalEl.classList.add('hidden');
    messageModalEl.classList.remove('flex');
    fetchOpnameData(currentPage);
}

window.openHistoryModal = async function() {
    if (!historyModalEl || !historyTableBodyEl) return;

    historyTableBodyEl.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Memuat history...</td></tr>';
    historyModalEl.classList.remove('hidden');
    historyModalEl.classList.add('flex');

    try {
        const response = await fetch('/api/stok_opname/history?limit=100');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const items = data.items || [];

        if (items.length === 0) {
            historyTableBodyEl.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat perubahan stok.</td></tr>';
            return;
        }

        historyTableBodyEl.innerHTML = items.map((item) => {
            const selisih = Number(item.selisih) || 0;
            const selisihClass = selisih < 0 ? 'text-red-600' : (selisih > 0 ? 'text-emerald-600' : 'text-slate-600');

            return `
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">${escapeHtml(item.waktu || '-')}</td>
                    <td class="px-4 py-3 text-slate-700 whitespace-nowrap">${escapeHtml(item.diubah_oleh || '-')}</td>
                    <td class="px-4 py-3 text-slate-700">
                        <div class="font-semibold">${escapeHtml(item.nama_barang || '-')}</div>
                        <div class="text-xs text-slate-500">${escapeHtml(item.kode_barang || '-')}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700">${escapeHtml(item.stok_sistem)}</td>
                    <td class="px-4 py-3 text-center text-slate-700">${escapeHtml(item.stok_fisik)}</td>
                    <td class="px-4 py-3 text-center font-bold ${selisihClass}">${escapeHtml(item.selisih)}</td>
                    <td class="px-4 py-3 text-slate-600">${escapeHtml(item.keterangan || '-')}</td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        console.error('Gagal memuat history stok opname:', error);
        historyTableBodyEl.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Gagal memuat history.</td></tr>';
    }
}

window.closeHistoryModal = function() {
    if (!historyModalEl) return;
    historyModalEl.classList.add('hidden');
    historyModalEl.classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', fetchOpnameData);

window.goToPrevOpnamePage = function() {
    if (currentPage > 1) {
        fetchOpnameData(currentPage - 1);
    }
}

window.goToNextOpnamePage = function() {
    if (currentPage < lastPage) {
        fetchOpnameData(currentPage + 1);
    }
}
