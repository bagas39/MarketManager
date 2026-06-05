let allProducts = [];
let cart = {};
let currentTotal = 0;
let selectedPaymentMethod = "tunai"; 
let pendingQrNoTransaksi = null;
let pendingInvoiceUrl = null;
let qrPollingInterval = null;

const addItemForm = document.getElementById("add-item-form");
const skuInput = document.getElementById("sku-input");
const qtyInput = document.getElementById("qty-input");
const cartTableBody = document.getElementById("cart-table-body");
const cartEmptyRow = document.getElementById("cart-empty-row");
const cartSubtotalEl = document.getElementById("cart-subtotal");
const cartTaxEl = document.getElementById("cart-tax");
const cartTotalEl = document.getElementById("cart-total");
const checkoutButtonEl = document.getElementById("checkout-button");
const paymentAmountInput = document.getElementById("payment-amount-input");
const changeAmountEl = document.getElementById("change-amount");
const messageModalEl = document.getElementById("message-modal");
const messageTitleEl = document.getElementById("message-title");
const messageBodyEl = document.getElementById("message-body");
const cashSectionEl = document.getElementById("cash-payment-section");
const qrisSectionEl = document.getElementById("qris-payment-section");
const btnPayTunai = document.getElementById("btn-pay-tunai");
const btnPayQris = document.getElementById("btn-pay-qris");
const qrModalEl = document.getElementById("qr-modal");
const qrAmountEl = document.getElementById("qr-amount");
const qrStatusWaiting = document.getElementById("qr-status-waiting");
const qrStatusPaid = document.getElementById("qr-status-paid");

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

const escapeHtml =
    window.escapeHtml ||
    function (value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;")
            .replace(/`/g, "&#96;");
    };

function formatCurrency(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
}

function parseFormattedNumber(value) {
    return Number(String(value).replace(/\D/g, "")) || 0;
}

window.showMessage = function (title, body, isError = false) {
    messageTitleEl.textContent = title;
    messageBodyEl.textContent = body;
    messageTitleEl.className = isError
        ? "text-xl font-semibold text-red-600"
        : "text-xl font-semibold text-emerald-600";
    messageModalEl.classList.remove("hidden");
    messageModalEl.classList.add("flex");
};

window.hideMessage = function () {
    messageModalEl.classList.add("hidden");
    messageModalEl.classList.remove("flex");
};

window.setPaymentMethod = function (method) {
    selectedPaymentMethod = method;

    const activeClass = [
        "border-2",
        "border-emerald-500",
        "bg-emerald-50",
        "text-emerald-700",
    ];
    const inactiveClass = [
        "border-2",
        "border-slate-200",
        "bg-white",
        "text-slate-500",
    ];

    if (method === "tunai") {
        btnPayTunai.className = `flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold transition-all ${activeClass.join(" ")}`;
        btnPayQris.className = `flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold transition-all hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 ${inactiveClass.join(" ")}`;
        cashSectionEl.classList.remove("hidden");
        qrisSectionEl.classList.add("hidden");
        checkoutButtonEl.textContent = "Proses & Cetak Struk";
    } else {
        btnPayQris.className = `flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold transition-all border-2 border-blue-500 bg-blue-50 text-blue-700`;
        btnPayTunai.className = `flex items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold transition-all hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-600 ${inactiveClass.join(" ")}`;
        cashSectionEl.classList.add("hidden");
        qrisSectionEl.classList.remove("hidden");
        checkoutButtonEl.textContent = "Bayar via Xendit";
    }

    updateCheckoutButton();
};

function updateCheckoutButton() {
    const hasItems = Object.keys(cart).length > 0;
    if (!hasItems) {
        checkoutButtonEl.disabled = true;
        return;
    }
    if (selectedPaymentMethod === "tunai") {
        const amountPaid = parseFormattedNumber(paymentAmountInput.value);
        checkoutButtonEl.disabled = amountPaid < currentTotal;
    } else {
        checkoutButtonEl.disabled = false;
    }
}

async function fetchProducts() {
    try {
        const response = await fetch("/api/barang");
        if (!response.ok)
            throw new Error(`HTTP error! status: ${response.status}`);
        allProducts = await response.json();
    } catch (error) {
        showMessage(
            "Error Kritis",
            "Gagal memuat data produk dari server.",
            true,
        );
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

    const product = allProducts.find(
        (p) =>
            (p.kode_barang || p.id_barang).toString() === sku ||
            (p.nama_barang || p.namaBarang).toLowerCase() === sku.toLowerCase(),
    );

    if (!product) {
        showMessage(
            "Produk Tidak Ditemukan",
            `Produk dengan SKU/ID '${sku}' tidak ditemukan.`,
            true,
        );
        return;
    }

    const id = product.id_barang ?? product.idBarang;
    const nama = product.nama_barang ?? product.namaBarang;
    const harga = product.harga_jual ?? product.hargaJual;
    const stok = product.stok ?? product.stock ?? 0;

    const qtyInCart = cart[id] ? cart[id].jumlah : 0;
    if (qtyInCart + qty > stok) {
        showMessage(
            "Stok Tidak Cukup",
            `Stok untuk ${nama} tidak mencukupi. Sisa stok: ${stok}`,
            true,
        );
        return;
    }

    if (cart[id]) {
        cart[id].jumlah += qty;
    } else {
        cart[id] = {
            id_barang: id,
            nama_barang: nama,
            harga_jual: harga,
            jumlah: qty,
        };
    }

    skuInput.value = "";
    qtyInput.value = "1";
    skuInput.focus();
    renderCart();
}

window.removeFromCart = function(id) {
    delete cart[id];
    renderCart();
    if (Object.keys(cart).length === 0)
        cartEmptyRow.style.display = "table-row";
};

function renderCart() {
    cartTableBody.innerHTML = "";
    const cartIds = Object.keys(cart);

    if (cartIds.length === 0) {
        cartTableBody.appendChild(cartEmptyRow);
        checkoutButtonEl.disabled = true;
    } else {
        cartEmptyRow.style.display = "none";
        cartIds.forEach((id) => {
            const item = cart[id];
            const row = document.createElement("tr");
            row.className =
                "hover:bg-slate-50 transition-colors border-b border-slate-100";
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-slate-800">${escapeHtml(item.nama_barang)}</div>
                    <div class="text-xs text-slate-500">ID: ${escapeHtml(item.id_barang)}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">${formatCurrency(item.harga_jual)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold">${escapeHtml(item.jumlah)}</span>
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
    Object.values(cart).forEach((item) => {
        subtotal += item.harga_jual * item.jumlah;
    });
    const tax = subtotal * 0.11;
    currentTotal = subtotal + tax;

    cartSubtotalEl.textContent = formatCurrency(subtotal);
    cartTaxEl.textContent = formatCurrency(tax);
    cartTotalEl.textContent = formatCurrency(currentTotal);

    calculateChange();
    updateCheckoutButton();
}

function calculateChange() {
    const amountPaid = parseFormattedNumber(paymentAmountInput.value);
    const change =
        amountPaid > 0 && amountPaid >= currentTotal
            ? amountPaid - currentTotal
            : 0;
    changeAmountEl.textContent = formatCurrency(change);
}

async function submitCashTransaction() {
    const amountPaid = parseFormattedNumber(paymentAmountInput.value);
    if (amountPaid < currentTotal) {
        showMessage(
            "Pembayaran Kurang",
            "Jumlah bayar tidak mencukupi untuk total belanja.",
            true,
        );
        return;
    }

    const itemsToSubmit = Object.values(cart).map((i) => ({
        id_barang: i.id_barang,
        jumlah: i.jumlah,
    }));
    if (itemsToSubmit.length === 0) {
        showMessage("Keranjang Kosong", "Tidak ada barang di keranjang.", true);
        return;
    }

    checkoutButtonEl.disabled = true;
    checkoutButtonEl.textContent = "Memproses...";

    try {
        const response = await fetch("/api/transaksi", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ idKasir: 1, items: itemsToSubmit }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || "Transaksi gagal.");

        showMessage('Transaksi Berhasil', `Transaksi ID ${result.id_penjualan} berhasil. Kembalian: ${changeAmountEl.textContent}`);
        resetSistem();
    } catch (error) {
        showMessage(
            "Transaksi Gagal",
            `Terjadi kesalahan: ${error.message}`,
            true,
        );
    } finally {
        if (Object.keys(cart).length > 0) {
            checkoutButtonEl.disabled = false;
            checkoutButtonEl.textContent = "Proses & Cetak Struk";
        }
    }
}

async function submitQrisTransaction() {
    const itemsToSubmit = Object.values(cart).map((i) => ({
        id_barang: i.id_barang,
        jumlah: i.jumlah,
    }));
    if (itemsToSubmit.length === 0) {
        showMessage("Keranjang Kosong", "Tidak ada barang di keranjang.", true);
        return;
    }

    checkoutButtonEl.disabled = true;
    checkoutButtonEl.textContent = "Memproses...";

    try {
        const response = await fetch("/api/xendit/create-invoice", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ items: itemsToSubmit }),
        });
        const result = await response.json();
        if (!response.ok || !result.success)
            throw new Error(result.message || "Gagal membuat Invoice Xendit.");

        pendingQrNoTransaksi = result.no_transaksi;
        pendingInvoiceUrl = result.invoice_url;

        window.open(result.invoice_url, "_blank");

        showWaitingModal(result.amount);
        startQrPolling(result.no_transaksi);
    } catch (error) {
        showMessage("Gagal Membuat Invoice", error.message, true);
    } finally {
        checkoutButtonEl.disabled = false;
        checkoutButtonEl.textContent = "Bayar via Xendit";
    }
}

function showWaitingModal(amount) {
    qrAmountEl.textContent = formatCurrency(amount);

    qrStatusWaiting.classList.remove("hidden");
    qrStatusPaid.classList.add("hidden");
    if (qrStatusPaid) qrStatusPaid.classList.remove("flex");

    qrModalEl.classList.remove("hidden");
    qrModalEl.classList.add("flex");
}

function hideQrModal() {
    qrModalEl.classList.add("hidden");
    qrModalEl.classList.remove("flex");
}

function startQrPolling(noTransaksi) {
    stopQrPolling();
    qrPollingInterval = setInterval(async () => {
        try {
            const response = await fetch(
                `/api/xendit/qr-status/${noTransaksi}`,
                {
                    headers: { Accept: "application/json" },
                },
            );
            const result = await response.json();
            if (result.status === "paid") {
                stopQrPolling();
                onQrPaymentSuccess(result.no_transaksi);
            }
        } catch (_) {}
    }, 3000);
}

function stopQrPolling() {
    if (qrPollingInterval) {
        clearInterval(qrPollingInterval);
        qrPollingInterval = null;
    }
}

function onQrPaymentSuccess(noTransaksi) {
    if (qrStatusWaiting) qrStatusWaiting.classList.add("hidden");
    if (qrStatusPaid) {
        qrStatusPaid.classList.remove("hidden");
        qrStatusPaid.classList.add("flex");
    }

    setTimeout(() => {
        hideQrModal();
        showMessage(
            "Pembayaran QRIS Berhasil!",
            `Transaksi ${noTransaksi} berhasil dibayar via QRIS.`,
        );
        resetSistem();
    }, 1500);
}

window.cancelQrPayment = async function () {
    stopQrPolling();
    hideQrModal();

    if (pendingQrNoTransaksi) {
        try {
            await fetch(`/api/xendit/cancel-qr/${pendingQrNoTransaksi}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });
        } catch (_) {}
        pendingQrNoTransaksi = null;
        pendingInvoiceUrl = null;
    }
};

window.reopenInvoice = function () {
    if (pendingInvoiceUrl) {
        window.open(pendingInvoiceUrl, "_blank");
    }
};

async function submitTransaction() {
    if (selectedPaymentMethod === "qris") {
        return submitQrisTransaction();
    }
    return submitCashTransaction();
}

function resetSistem() {
    cart = {};
    currentTotal = 0;
    pendingQrNoTransaksi = null;
    pendingInvoiceUrl = null;
    renderCart();
    cartEmptyRow.style.display = "table-row";
    paymentAmountInput.value = "";
    changeAmountEl.textContent = "Rp 0";
    skuInput.value = "";
    qtyInput.value = "1";
    fetchProducts();
}

document.addEventListener("DOMContentLoaded", () => {
    fetchProducts();
    skuInput?.focus();
});

addItemForm?.addEventListener("submit", handleAddItem);
checkoutButtonEl?.addEventListener("click", submitTransaction);

paymentAmountInput?.addEventListener("input", (e) => {
    const numericValue = e.target.value.replace(/\D/g, "");
    e.target.value = numericValue
        ? new Intl.NumberFormat("id-ID").format(numericValue)
        : "";
    calculateChange();
    updateCheckoutButton();
});
