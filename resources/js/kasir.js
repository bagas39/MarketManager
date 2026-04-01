let allProducts = [];
let cart = {}; 
let currentTotal = 0; 
const addItemForm = document.getElementById('add-item-form');
const skuInput = document.getElementById('sku-input');
const qtyInput = document.getElementById('qty-input');
const cartTableBody = document.getElementById('cart-table-body');
const cartEmptyRow = document.getElementById('cart-empty-row');

const cartSubtotalEl = document.getElementById('cart-subtotal');
const cartTaxEl = document.getElementById('cart-tax');
const cartTotalEl = document.getElementById('cart-total');
const checkoutButtonEl = document.getElementById('checkout-button');

const paymentAmountInput = document.getElementById('payment-amount-input');
const changeAmountEl = document.getElementById('change-amount');

const messageModalEl = document.getElementById('message-modal');
const messageTitleEl = document.getElementById('message-title');
const messageBodyEl = document.getElementById('message-body');

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
}

function formatNumberInput(value) {
    return value.replace(/\D/g, '');
}

function parseFormattedNumber(value) {
    return Number(value.replace(/\D/g, '')) || 0;
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
}

async function fetchProducts() {
    try {
        const response = await fetch('/api/barang');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        allProducts = await response.json();
    } catch (error) {
        showMessage('Error Kritis', 'Gagal memuat data produk dari server.', true);
    }
}

function handleAddItem(event) {
    event.preventDefault();
    const sku = skuInput.value.trim();
    const qty = parseInt(qtyInput.value);

    if (!sku || isNaN(qty) || qty <= 0) {
        showMessage('Input Tidak Valid', 'Pastikan SKU dan Jumlah diisi dengan benar.', true);
        return;
    }

    const product = allProducts.find(p => 
        (p.id_barang || p.idBarang).toString() === sku || 
        (p.nama_barang || p.namaBarang).toLowerCase() === sku.toLowerCase()
    );

    if (!product) {
        showMessage('Produk Tidak Ditemukan', `Produk dengan SKU/ID '${sku}' tidak ditemukan.`, true);
        return;
    }

    const id = product.id_barang ?? product.idBarang;
    const nama = product.nama_barang ?? product.namaBarang;
    const harga = product.harga_jual ?? product.hargaJual;
    let currentStock = product.stok ?? product.stock ?? 0; 
    
    let qtyInCart = cart[id] ? cart[id].jumlah : 0;
    
    if ((qtyInCart + qty) > currentStock) {
        showMessage('Stok Tidak Cukup', `Stok untuk ${nama} tidak mencukupi. Sisa stok: ${currentStock}`, true);
        return;
    }

    if (cart[id]) {
        cart[id].jumlah += qty;
    } else {
        cart[id] = { id_barang: id, nama_barang: nama, harga_jual: harga, jumlah: qty };
    }

    skuInput.value = '';
    qtyInput.value = '1';
    skuInput.focus();
    renderCart();
}

window.removeFromCart = function(id) {
    delete cart[id];
    renderCart();
    if (Object.keys(cart).length === 0) cartEmptyRow.style.display = 'table-row';
};

function renderCart() {
    cartTableBody.innerHTML = '';
    const cartIds = Object.keys(cart);

    if (cartIds.length === 0) {
        cartTableBody.appendChild(cartEmptyRow);
        checkoutButtonEl.disabled = true;
    } else {
        cartEmptyRow.style.display = 'none';
        checkoutButtonEl.disabled = false;
        
        cartIds.forEach(id => {
            const item = cart[id];
            const row = document.createElement('tr');
            row.className = 'hover:bg-slate-50 transition-colors border-b border-slate-100';
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-slate-800">${item.nama_barang}</div>
                    <div class="text-xs text-slate-500">ID: ${item.id_barang}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${formatCurrency(item.harga_jual)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold">${item.jumlah}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-600">${formatCurrency(item.jumlah * item.harga_jual)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button class="text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors text-xs font-semibold" onclick="removeFromCart(${id})">Hapus</button>
                </td>
            `;
            cartTableBody.appendChild(row);
        });
    }
    updateTotals();
}

function updateTotals() {
    let subtotal = 0;
    Object.values(cart).forEach(item => {
        subtotal += item.harga_jual * item.jumlah;
    });
    
    const tax = subtotal * 0.11; 
    const total = subtotal + tax;
    currentTotal = total; 
    
    cartSubtotalEl.textContent = formatCurrency(subtotal);
    cartTaxEl.textContent = formatCurrency(tax);
    cartTotalEl.textContent = formatCurrency(total);

    calculateChange();
}

function calculateChange() {
    const amountPaid = parseFormattedNumber(paymentAmountInput.value);
    let change = 0;
    if (amountPaid > 0 && amountPaid >= currentTotal) {
        change = amountPaid - currentTotal;
    }
    changeAmountEl.textContent = formatCurrency(change);
}

async function submitTransaction() {
    const idKasir = 1; 

    const itemsToSubmit = Object.values(cart).map(item => ({
        id_barang: item.id_barang, 
        jumlah: item.jumlah
    }));

    if (itemsToSubmit.length === 0) {
        showMessage('Keranjang Kosong', 'Tidak ada barang di keranjang.', true);
        return;
    }

    const amountPaid = parseFormattedNumber(paymentAmountInput.value);
    if (amountPaid < currentTotal) {
        showMessage('Pembayaran Kurang', 'Jumlah bayar tidak mencukupi untuk total belanja.', true);
        return;
    }

    checkoutButtonEl.disabled = true;
    checkoutButtonEl.textContent = 'Memproses...';

    try {
        const response = await fetch('/api/transaksi', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                idKasir: idKasir,
                items: itemsToSubmit 
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Transaksi gagal.');
        }

        showMessage('Transaksi Berhasil', `Transaksi ID ${result.id_penjualan} berhasil. Kembalian: ${changeAmountEl.textContent}`);
        resetSistem();
        
    } catch (error) {
        showMessage('Transaksi Gagal', `Terjadi kesalahan: ${error.message}`, true);
    } finally {
        if (Object.keys(cart).length > 0) {
           checkoutButtonEl.disabled = false;
           checkoutButtonEl.textContent = 'Proses & Cetak Struk';
        }
    }
}

function resetSistem() {
    cart = {};
    currentTotal = 0;
    renderCart();
    cartEmptyRow.style.display = 'table-row';
    paymentAmountInput.value = '';
    changeAmountEl.textContent = 'Rp 0';
    skuInput.value = '';
    qtyInput.value = '1';
    fetchProducts();
}

document.addEventListener('DOMContentLoaded', () => {
    fetchProducts();
    skuInput?.focus();
});

addItemForm?.addEventListener('submit', handleAddItem);
checkoutButtonEl?.addEventListener('click', submitTransaction);

paymentAmountInput?.addEventListener('input', (e) => {
    const value = e.target.value;
    const numericValue = formatNumberInput(value);
    
    if (numericValue) {
        e.target.value = new Intl.NumberFormat('id-ID').format(numericValue);
    } else {
        e.target.value = '';
    }
    calculateChange();
});