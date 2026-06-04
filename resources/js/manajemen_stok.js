let currentPage = 1;
const itemsLimit = 15;
let totalAvailableItems = 0;
let debounceTimer;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const tableBody = document.getElementById('stok-table-body');
const loadingRow = document.getElementById('loading-row');
const searchInput = document.getElementById('stok-search-input');
const startDateInput = document.getElementById('start-date');
const endDateInput = document.getElementById('end-date');
const searchBtn = document.getElementById('search-btn');
const prevButton = document.getElementById('prev-button');
const nextButton = document.getElementById('next-button');
const pageInfo = document.getElementById('page-info');
const messageModalEl = document.getElementById('message-modal');
const messageTitleEl = document.getElementById('message-title');
const messageBodyEl = document.getElementById('message-body');

const escapeHtml = window.escapeHtml || function(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/`/g, '&#96;');
};

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
}

window.showMessage = function(title, body) {
    messageTitleEl.textContent = title;
    messageBodyEl.textContent = body;
    messageModalEl.classList.remove('hidden');
    messageModalEl.classList.add('flex');
}

window.hideMessage = function() {
    messageModalEl.classList.add('hidden');
    messageModalEl.classList.remove('flex');
}

async function fetchStok(page) {
    loadingRow.style.display = 'table-row';
    if (page === 1) { tableBody.innerHTML = ''; }
    tableBody.appendChild(loadingRow);
    
    const rawSearch = (searchInput?.value || '').trim();
    const searchNama = rawSearch.replace(/\s+/g, ' ');
    const startDate = startDateInput?.value || '';
    const endDate = endDateInput?.value || '';

    const params = new URLSearchParams({ page: page, limit: itemsLimit });
    if (searchNama) { params.append('search_nama', searchNama); }
    if (startDate) { params.append('start_date', startDate); }
    if (endDate) { params.append('end_date', endDate); }
    
    try {
        const response = await fetch(`/api/manajemen_stok?${params.toString()}`);
        if (!response.ok) {
            const err = await response.json();
            throw new Error(err.details || `HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        totalAvailableItems = data.totalAvailableItems;
        currentPage = page;
        
        renderTable(data.items);
        updatePaginationControls(data.items.length);
    } catch (error) {
        console.error("Error fetching stok:", error);
        loadingRow.style.display = 'none';
        tableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-10 text-center text-red-600">Gagal memuat data: ${escapeHtml(error.message)}</td></tr>`;
        showMessage('Error', `Gagal memuat data stok. ${escapeHtml(error.message)}`);
    }
}

function renderTable(items) {
    loadingRow.style.display = 'none';
    tableBody.innerHTML = '';
    
    if (items.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Tidak ada data barang yang ditemukan.</td></tr>`;
        return;
    }
    
    items.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        const stokClass = item.stok <= 0 ? 'text-red-600 font-semibold' : 'text-gray-700';
        
        row.innerHTML = `
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm font-medium text-gray-900 w-32 lg:w-28">${escapeHtml(item.kode_barang || item.id_barang)}</td>
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm text-gray-700 max-w-[200px] truncate">${escapeHtml(item.nama_barang)}</td>
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm text-gray-700 hidden force-md">${escapeHtml(item.kategori || 'N/A')}</td>
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm text-gray-700 text-right">${formatCurrency(item.harga_beli)}</td>
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm text-gray-700 text-right hidden force-md">${formatCurrency(item.harga_jual)}</td>
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm ${stokClass} text-right">${escapeHtml(item.stok)}</td>
            <td class="px-3 py-2 md:px-5 md:py-3 whitespace-nowrap text-sm text-right">
                ${item.stok <= 0 ? `<button onclick="window.deleteBarang(${item.id})" class="bg-red-500 hover:bg-red-600 text-white px-2.5 py-1 rounded text-xs font-semibold">Hapus</button>` : ''}
            </td>
        `;
        tableBody.appendChild(row);
    });
}

window.deleteBarang = async function(id) {
    if (!id) return;
    if(!confirm('Yakin ingin menghapus barang ini?')) return;

    try {
        const res = await fetch(`/api/barang/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        const json = await res.json();
        if (res.ok && json.success) {
            showMessage('Sukses', json.message || 'Barang berhasil dihapus.');
            fetchStok(currentPage);
        } else {
            showMessage('Gagal', json.message || 'Gagal menghapus barang.');
        }
    } catch (e) {
        console.error('Error deleting barang:', e);
        showMessage('Error Jaringan', e.message || 'Gagal terhubung ke server.');
    }
}

function updatePaginationControls(loadedCount) {
    const startItem = totalAvailableItems > 0 ? ((currentPage - 1) * itemsLimit) + 1 : 0;
    const endItem = startItem + loadedCount - 1;
    pageInfo.textContent = `Menampilkan ${startItem}-${endItem} dari ${totalAvailableItems}`;
    prevButton.disabled = (currentPage === 1);
    nextButton.disabled = (currentPage * itemsLimit >= totalAvailableItems);
}

document.addEventListener('DOMContentLoaded', () => { 
    fetchStok(currentPage);
});

prevButton.addEventListener('click', () => { 
    if (currentPage > 1) { fetchStok(currentPage - 1); } 
});

nextButton.addEventListener('click', () => { 
    if (currentPage * itemsLimit < totalAvailableItems) { fetchStok(currentPage + 1); } 
});

searchInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { 
        fetchStok(1); 
    }, 500);
});

searchInput?.addEventListener('keyup', (e) => {
    if (e.key === 'Enter') fetchStok(1);
});

searchBtn?.addEventListener('click', () => fetchStok(1));
startDateInput?.addEventListener('change', () => fetchStok(1));
endDateInput?.addEventListener('change', () => fetchStok(1));

messageModalEl.addEventListener('click', (e) => { 
    if (e.target === messageModalEl) hideMessage(); 
});