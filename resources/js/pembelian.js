let items = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
let allProducts = [];

function formatIDR(num) {
    return new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', minimumFractionDigits:0}).format(num);
}

const modal = (title, body) => {
    const titleEl = document.getElementById('msg-title');
    if (!titleEl) {
        alert(`${title}\n\n${body}`);
        return;
    }
    titleEl.textContent = title;
    
    if (title.toLowerCase().includes('sukses')) {
        titleEl.className = 'font-bold text-xl mb-2 text-emerald-600';
    } else if (title.toLowerCase().includes('error') || title.toLowerCase().includes('gagal')) {
        titleEl.className = 'font-bold text-xl mb-2 text-red-600';
    } else {
        titleEl.className = 'font-bold text-xl mb-2 text-slate-800';
    }
    
    document.getElementById('msg-body').textContent = body;
    document.getElementById('msg-modal').classList.remove('hidden');
};

async function fetchProducts() {
    try {
        const response = await fetch('/api/barang');
        if(response.ok) {
            allProducts = await response.json();
        }
    } catch (error) {
        console.error("Gagal load master barang", error);
    }
}

document.getElementById('add-item-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const nameOrSku = document.getElementById('item-name').value.trim();
    const price = parseFloat(document.getElementById('item-price').value);
    const qty = parseInt(document.getElementById('item-qty').value);

    if (!nameOrSku || isNaN(price) || isNaN(qty) || qty <= 0) {
        modal("Input Tidak Valid", "Pastikan semua kolom diisi dengan benar.");
        return;
    }

    let product = allProducts.find(p => 
        p.id_barang.toString() === nameOrSku || 
        p.nama_barang.toLowerCase() === nameOrSku.toLowerCase()
    );

    let id, name;

    if (product) {
        id = product.id_barang;
        name = product.nama_barang;
    } else {
        id = Math.floor(Math.random() * 9000) + 2000; 
        name = nameOrSku;
    }

    const exist = items.find(i => i.id_barang === id || i.namaBarang.toLowerCase() === name.toLowerCase());
    
    if(exist) {
        exist.jumlah += qty;
        exist.hargaBeli = price;
    } else {
        items.push({ id_barang: id, namaBarang: name, hargaBeli: price, jumlah: qty });
    }
    
    this.reset();
    document.getElementById('item-qty').value = '1';
    document.getElementById('item-name').focus();
    renderCart();
});

window.removeItem = function(idx) {
    items.splice(idx, 1);
    renderCart();
};

function renderCart() {
    const body = document.getElementById('purchase-list-body');
    const btnSubmit = document.getElementById('submit-purchase-btn');
    const displayTotal = document.getElementById('total-display');
    
    if(!body) return;
    
    body.innerHTML = '';
    let total = 0;
    
    if(items.length === 0) {
        body.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 italic">Belum ada item ditambahkan</td></tr>';
        btnSubmit.disabled = true;
        displayTotal.textContent = formatIDR(0);
        return;
    }

    btnSubmit.disabled = false;
    items.forEach((item, idx) => {
        const sub = item.hargaBeli * item.jumlah;
        total += sub;
        body.innerHTML += `
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">${item.namaBarang}</p>
                    <p class="text-xs text-slate-500">ID: ${item.id_barang}</p>
                </td>
                <td class="px-4 py-3 text-right text-slate-600">${formatIDR(item.hargaBeli)}</td>
                <td class="px-4 py-3 text-center">
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold">${item.jumlah}</span>
                </td>
                <td class="px-4 py-3 text-right font-bold text-emerald-600">${formatIDR(sub)}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="removeItem(${idx})" class="text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors text-xs font-semibold">
                        Hapus
                    </button>
                </td>
            </tr>`;
    });
    displayTotal.textContent = formatIDR(total);
}

document.getElementById('submit-purchase-btn')?.addEventListener('click', async function() {
    const sup = document.getElementById('supplier-input').value;
    const gudang = document.getElementById('gudang-input').value;

    if(!sup || !gudang) return modal("Validasi Gagal", "Supplier & Gudang wajib diisi");

    this.disabled = true;
    this.innerHTML = "Menyimpan...";

    try {
        const res = await fetch('/pembelian/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ supplier: sup, idGudang: parseInt(gudang), items: items })
        });
        
        const json = await res.json();
        
        if(res.ok && json.success) {
            modal("Sukses", `Transaksi Berhasil!\nNomor Faktur (PO): #${json.id_pembelian}\nStok di Manajemen Stok telah bertambah.`);
            items = [];
            document.getElementById('supplier-input').value = '';
            renderCart();
            loadHistory();
        } else {
            modal("Gagal", json.message || "Gagal menyimpan");
        }
    } catch(e) {
        modal("Error Jaringan", e.message);
    } finally {
        this.innerHTML = "Simpan Transaksi";
        if(items.length > 0) this.disabled = false;
    }
});

async function loadHistory() {
    const container = document.getElementById('history-container');
    const search = document.getElementById('filter-supplier')?.value || '';
    if(!container) return;

    container.innerHTML = '<p class="text-center text-slate-500 py-4 text-sm">Memuat data...</p>';
    
    try {
        const res = await fetch(`/pembelian/history?search_supplier=${search}`);
        const data = await res.json();
        container.innerHTML = '';
        
        if(data.purchases && data.purchases.length > 0) {
            data.purchases.forEach(p => {
                const date = new Date(p.tanggal_pembelian).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'});
                container.innerHTML += `
                    <div class="bg-white border border-slate-200 p-4 rounded-lg hover:border-emerald-500 hover:shadow-md transition-all cursor-default group mb-3">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition-colors">#${p.id_pembelian}</span>
                            <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">${date}</span>
                        </div>
                        <div class="text-xs text-slate-500 mb-1">Supplier</div>
                        <div class="text-sm font-semibold text-slate-700 mb-2 truncate">${p.supplier}</div>
                        <div class="border-t border-slate-100 pt-2 flex justify-between items-center">
                            <span class="text-xs text-slate-400">Total</span>
                            <span class="text-sm font-bold text-emerald-600">${formatIDR(p.total_beli)}</span>
                        </div>
                    </div>`;
            });
        } else {
            container.innerHTML = `<div class="text-center text-slate-400 py-10"><span class="text-sm">Tidak ada riwayat pembelian</span></div>`;
        }
    } catch(e) {
        container.innerHTML = '<p class="text-red-500 text-center py-4 text-sm">Gagal memuat history.</p>';
    }
}

document.getElementById('refresh-history-btn')?.addEventListener('click', loadHistory);

document.addEventListener('DOMContentLoaded', () => {
    fetchProducts();
    if(document.getElementById('history-container')) {
        loadHistory();
    }
});