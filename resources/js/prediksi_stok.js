let barangData = []; 
async function fetchBarangPrediksi() {
    const selectBarang = document.getElementById("barangId");
    if (!selectBarang) return;

    try {
        const response = await fetch('/api/prediksi_stok/barang');
        
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

        const data = await response.json();
        
        barangData = data || []; 
        renderDropdownBarang();
    } catch (error) {
        console.error("Gagal mengambil data barang:", error);
        selectBarang.innerHTML = `<option value="">Gagal memuat produk</option>`;
    }
}

function renderDropdownBarang() {
    const selectBarang = document.getElementById("barangId");
    if (!selectBarang) return;

    selectBarang.innerHTML = '<option value="">-- Pilih Nama Produk --</option>';

    if (barangData.length === 0) {
        selectBarang.innerHTML = '<option value="">Data barang kosong</option>';
        return;
    }

    barangData.forEach(item => {
        const option = document.createElement("option");
        option.value = item.id_barang;
        option.textContent = item.nama_barang;
        selectBarang.appendChild(option);
    });
}

window.hitungPrediksi = async function() {
    const barangId = document.getElementById("barangId").value;
    const periode = document.getElementById("periode").value;
    const hasilBox = document.getElementById("hasilBox");

    if (!barangId) {
        alert("Silakan pilih produk terlebih dahulu!");
        return;
    }
    if (!periode || periode < 1) {
        alert("Masukkan jumlah hari analisis yang valid (minimal 1 hari)!");
        return;
    }

    hasilBox.innerHTML = `
        <div class="text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-2"></div>
            <p class="text-sm text-gray-500">Menganalisis data ${periode} hari terakhir...</p>
        </div>
    `;

    try {
        const response = await fetch('/api/prediksi_stok/stok', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                barangId: barangId,
                periode: parseInt(periode)
            })
        });

        if (!response.ok) throw new Error("Gagal mengambil hasil prediksi");

        const data = await response.json();

        renderHasilAnalisis(data, periode);

    } catch (error) {
        console.error("Error Prediksi:", error);
        hasilBox.innerHTML = `<p class="text-red-500 text-center">Terjadi kesalahan saat menghitung prediksi.</p>`;
    }
}

function renderHasilAnalisis(data, periode) {
    const hasilBox = document.getElementById("hasilBox");
    hasilBox.innerHTML = "";

    const stokSekarang = parseInt(data.stok_saat_ini);
    const totalTerjual = parseInt(data.total_terjual);
    const sisaHari = data.hari_bertahan;
    
    let statusText = "";
    let statusColorClass = "";
    let mainContent = ""; 
    let footerContent = ""; 

    if (stokSekarang > 0 && totalTerjual === 0) {
        statusText = "DATA TERBATAS";
        statusColorClass = "bg-slate-500 text-white";
        
        mainContent = `
            <div class="flex flex-col items-center justify-center py-10">
                <div class="p-6 bg-red-50 border border-red-100 rounded-2xl w-full">
                    <p class="text-red-600 text-xs font-bold uppercase tracking-tight text-center">
                        ⚠ Produk ini tidak memiliki data penjualan dalam ${periode} hari terakhir
                    </p>
                    <p class="text-red-400 text-[10px] mt-2 font-medium text-center uppercase tracking-wide">
                        Gunakan periode yang lebih panjang atau pastikan transaksi sudah tercatat.
                    </p>
                </div>
            </div>
        `;
        
        footerContent = ""; 
    }
    else {
        const isStokHabis = stokSekarang === 0;
        const isKritis = sisaHari < 7 && sisaHari > 0;
        
        if (isStokHabis) {
            statusText = "STOK HABIS";
            statusColorClass = "bg-red-600 text-white";
        } else {
            statusText = isKritis ? "RE-STOCK" : "STOK AMAN";
            statusColorClass = isKritis ? "bg-amber-500 text-white" : "bg-emerald-500 text-white";
        }

        let displayHari = isStokHabis ? "0" : (sisaHari > 365 ? "> 365" : Math.round(sisaHari));
        let displayColor = isStokHabis ? "text-red-600" : "text-slate-800";

        mainContent = `
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Estimasi Stok Habis Dalam</p>
            <div class="flex items-center justify-center gap-3">
                <span class="text-8xl font-black ${displayColor} tracking-tighter">${displayHari}</span>
                <span class="text-2xl font-black text-slate-400 uppercase">Hari</span>
            </div>
        `;

        footerContent = `
            <div class="mt-6 pt-6 border-t border-slate-200 w-full text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    ℹ Prediksi dihitung dari total ${totalTerjual} unit terjual
                </span>
            </div>
        `;
    }

    const content = `
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 fade-in w-full flex flex-col gap-8">
            <div class="flex justify-between items-start w-full">
                <div class="space-y-1">
                    <p class="text-lg font-black uppercase tracking-tight text-slate-900">HASIL ANALISIS</p>
                    <h3 class="text-2xl font-bold text-slate-500">${data.nama_barang}</h3>
                    <p class="text-sm text-slate-400 font-medium">Tren <span class="text-slate-600">${periode} hari</span> terakhir</p>
                </div>
                <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider ${statusColorClass} shadow-sm">
                    ${statusText}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Stok Saat Ini</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black ${stokSekarang === 0 ? 'text-red-600' : 'text-slate-800'}">${stokSekarang}</span>
                        <span class="text-slate-400 text-xs font-bold uppercase">Unit</span>
                    </div>
                </div>
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Rata-rata terjual</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800">${data.rata_rata_harian}</span>
                        <span class="text-slate-400 text-xs font-bold uppercase">Unit/Hari</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100 text-center">
                ${mainContent}
                ${footerContent}
            </div>
        </div>
    `;

    hasilBox.innerHTML = content;
}

document.addEventListener('DOMContentLoaded', fetchBarangPrediksi);