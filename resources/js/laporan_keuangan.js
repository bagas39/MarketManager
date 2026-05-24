document.addEventListener('DOMContentLoaded', function() {
    fetchLaporan();

    document.getElementById('filter-btn').addEventListener('click', function() {
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        
        if (!start || !end) {
            alert("Harap pilih kedua tanggal!");
            return;
        }
        fetchLaporan(start, end);
    });

    async function fetchLaporan(startDate = '', endDate = '') {
        try {
            let url = '/api/laporan_keuangan';
            if (startDate && endDate) {
                url += `?start_date=${startDate}&end_date=${endDate}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            document.getElementById('total-masuk').innerText = `Rp ${data.ringkasan.total_masuk.toLocaleString()}`;
            document.getElementById('total-keluar').innerText = `Rp ${data.ringkasan.total_keluar.toLocaleString()}`;
            document.getElementById('saldo-akhir').innerText = `Rp ${data.ringkasan.saldo_akhir.toLocaleString()}`;

            const tableBody = document.getElementById('laporan-table-body');
            if (data.detail.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-gray-400 sm:px-6">Tidak ada data untuk periode ini</td></tr>';
                return;
            }

            const escapeHtml = window.escapeHtml || function(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/`/g, '&#96;');
            };

            tableBody.innerHTML = data.detail.map(item => `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm text-gray-600 sm:px-6 sm:py-4">${escapeHtml(item.tanggal)}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-800 sm:px-6 sm:py-4 break-words">${escapeHtml(item.keterangan)}</td>
                    <td class="px-4 py-3 text-center sm:px-6 sm:py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase ${item.tipe === 'Masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'}">
                            ${escapeHtml(item.tipe)}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold sm:px-6 sm:py-4 ${item.tipe === 'Masuk' ? 'text-emerald-600' : 'text-red-600'}">
                        Rp ${String(item.jumlah).replace(/\B(?=(\d{3})+(?!\d))/g, ",")}
                    </td>
                </tr>
            `).join('');
            
        } catch (error) {
            console.error('Error:', error);
        }
    }

    document.getElementById('export-pdf-btn').addEventListener('click', function() {
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;
        
        let url = '/laporan_keuangan/export-pdf';
        if (start && end) {
            url += `?start_date=${start}&end_date=${end}`;
        }
        
        window.open(url, '_blank');
    });
});