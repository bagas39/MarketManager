let currentPage = 1;
const itemsLimit = 15;
let totalAvailableItems = 0;
let debounceTimer;

const tableBody = document.getElementById('stok-table-body');
const loadingRow = document.getElementById('loading-row');
const searchInput = document.getElementById('stok-search-input');
const prevButton = document.getElementById('prev-button');
const nextButton = document.getElementById('next-button');
const pageInfo = document.getElementById('page-info');
const messageModalEl = document.getElementById('message-modal');
const messageTitleEl = document.getElementById('message-title');
const messageBodyEl = document.getElementById('message-body');

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
    
    const searchNama = searchInput.value.trim();
    const params = new URLSearchParams({ page: page, limit: itemsLimit });
    if (searchNama) { params.append('search_nama', searchNama); }
    
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
        tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-600">Gagal memuat data: ${error.message}</td></tr>`;
        showMessage('Error', `Gagal memuat data stok. ${error.message}`);
    }
}

function renderTable(items) {
    loadingRow.style.display = 'none';
    tableBody.innerHTML = '';
    
    if (items.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Tidak ada data barang yang ditemukan.</td></tr>`;
        return;
    }
    
    items.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        const stokClass = item.stok <= 0 ? 'text-red-600 font-semibold' : 'text-gray-700';
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.kode_barang || item.id_barang}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${item.nama_barang}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${item.kategori || 'N/A'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${formatCurrency(item.harga_beli)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">${formatCurrency(item.harga_jual)}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm ${stokClass}">${item.stok}</td>
        `;
        tableBody.appendChild(row);
    });
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

messageModalEl.addEventListener('click', (e) => { 
    if (e.target === messageModalEl) hideMessage(); 
});