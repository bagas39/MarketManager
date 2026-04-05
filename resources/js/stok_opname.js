let inventoryData = [];
const messageModalEl = document.getElementById('message-modal');
const messageTitleEl = document.getElementById('message-title');
const messageBodyEl = document.getElementById('message-body');
async function fetchOpnameData() {
    const tbody = document.getElementById('opname-table-body');
    if (!tbody) return;

    try {
        const response = await fetch('/api/stok_opname/data');
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        
        inventoryData = data.items || [];
        
        renderTable();
    } catch (error) {
        console.error("Gagal mengambil data:", error);
        tbody.innerHTML = `<tr><td colspan="5" class="p-3 text-center text-red-500">Gagal memuat data stok.</td></tr>`;
    }
}

function renderTable() {
    const tbody = document.getElementById('opname-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    if (inventoryData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Data barang tidak ditemukan.</td></tr>`;
        return;
    }

    inventoryData.forEach((item, index) => {
        const selisih = item.selisih || 0;
        const selisihClass = selisih < 0 ? 'text-red-600' : (selisih > 0 ? 'text-green-600' : 'text-gray-500');
        
        tbody.innerHTML += `
            <tr class="border-b hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">${item.id_barang}</td>
                <td class="px-6 py-4 text-sm text-gray-700 font-medium">${item.nama_barang}</td>
                <td class="px-6 py-4 text-sm text-center text-gray-600">${item.stok_sistem}</td>
                <td class="px-6 py-4 text-center">
                    <input type="number" 
                           class="w-20 border border-gray-300 rounded px-2 py-1 text-center focus:ring-green-500 focus:border-green-500" 
                           value="${item.stok_fisik}" 
                           oninput="updateSelisih(${index}, this.value)">
                </td>
                <td class="px-6 py-4 text-center font-bold ${selisihClass}" id="selisih-${index}">
                    ${selisih}
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

window.simpanOpname = async function() {
    const saveButton = document.getElementById('save-opname-button');
    
    if (inventoryData.length === 0) {
        showMessage('Peringatan', 'Tidak ada data untuk disimpan.', true);
        return;
    }

    saveButton.disabled = true;
    saveButton.innerText = "Memproses...";

    try {
        const response = await fetch('/api/stok_opname/simpan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
        saveButton.innerText = "Simpan Hasil Opname";
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
    fetchOpnameData();
}

document.addEventListener('DOMContentLoaded', fetchOpnameData);