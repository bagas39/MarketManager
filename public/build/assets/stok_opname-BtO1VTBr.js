let i=[],o=1,c=1;const E=10,g=document.getElementById("message-modal"),f=document.getElementById("message-title"),$=document.getElementById("message-body"),d=document.getElementById("confirm-modal"),y=document.getElementById("confirm-title"),h=document.getElementById("confirm-body"),l=document.getElementById("history-modal"),p=document.getElementById("history-table-body"),b=document.getElementById("opname-pagination-info"),w=document.getElementById("opname-prev-page"),k=document.getElementById("opname-next-page"),v=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");let u=null;function s(t){return String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;")}async function x(t=1){const e=document.getElementById("opname-table-body");if(e)try{const n=await fetch(`/api/stok_opname/data?page=${t}&limit=${E}`);if(!n.ok)throw new Error(`HTTP error! status: ${n.status}`);const a=await n.json();i=a.items||[],o=a.pagination?.current_page||t,c=a.pagination?.last_page||1,M(),T(a.pagination)}catch(n){console.error("Gagal mengambil data:",n),e.innerHTML='<tr><td colspan="6" class="p-3 text-center text-red-500">Gagal memuat data stok.</td></tr>'}}function T(t){if(!b)return;const e=t?.from??0,n=t?.to??0,a=t?.total??0;b.textContent=a>0?`Menampilkan ${e}-${n} dari ${a} barang (Halaman ${o} dari ${c})`:"Belum ada data barang.",w&&(w.disabled=o<=1),k&&(k.disabled=o>=c)}function M(){const t=document.getElementById("opname-table-body");if(t){if(t.innerHTML="",i.length===0){t.innerHTML='<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Data barang tidak ditemukan.</td></tr>';return}i.forEach((e,n)=>{const a=e.selisih||0,r=a<0?"text-red-600":a>0?"text-green-600":"text-gray-500";t.innerHTML+=`
            <tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-3 md:px-5 md:py-3 text-sm text-gray-900">${s(e.kode_barang||e.id_barang)}</td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-sm text-gray-700 font-medium">${s(e.nama_barang)}</td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-sm text-center text-gray-600">${s(e.stok_sistem)}</td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-center">
                    <input type="number" 
                                   class="w-16 lg:w-14 border border-gray-300 rounded px-1.5 py-1 text-center text-sm focus:ring-green-500 focus:border-green-500" 
                           value="${s(e.stok_fisik)}" 
                           oninput="updateSelisih(${n}, this.value)">
                </td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-center hidden force-md">
                    <input type="text"
                                   class="w-40 lg:w-36 border border-gray-300 rounded px-2 py-1 text-xs lg:text-sm focus:ring-green-500 focus:border-green-500"
                           value="${s(e.keterangan||"")}"
                           placeholder="Alasan perubahan"
                           oninput="updateKeterangan(${n}, this.value)">
                </td>
                        <td class="px-3 py-3 md:px-5 md:py-3 text-center font-bold ${r} hidden force-md text-sm" id="selisih-${n}">
                    ${s(a)}
                </td>
            </tr>
        `})}}window.updateSelisih=function(t,e){const n=parseInt(e)||0,a=i[t].stok_sistem,r=n-a;i[t].stok_fisik=n,i[t].selisih=r;const m=document.getElementById(`selisih-${t}`);m&&(m.innerText=r,m.className=`px-6 py-4 text-center font-bold ${r<0?"text-red-600":r>0?"text-green-600":"text-gray-500"}`)};window.updateKeterangan=function(t,e){i[t].keterangan=e};window.simpanOpname=async function(){const t=document.getElementById("save-opname-button");if(i.length===0){showMessage("Peringatan","Tidak ada data untuk disimpan.",!0);return}if(!(c>1&&!await B("Konfirmasi Simpan",`Anda sedang di halaman ${o} dari ${c}. Simpan perubahan hanya untuk halaman ini?`))){t.disabled=!0,t.innerText="Memproses...";try{(await(await fetch("/api/stok_opname/simpan",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":v},body:JSON.stringify({items:i})})).json()).success?showMessage("Sukses","Hasil stok opname berhasil disimpan. Stok produk telah diperbarui."):showMessage("Gagal","Terjadi kesalahan saat menyimpan data.",!0)}catch(e){console.error("Error:",e),showMessage("Error Sistem","Gagal terhubung ke server.",!0)}finally{t.disabled=!1,t.innerText="Simpan Hasil Opname Halaman Ini"}}};function B(t,e){return!d||!y||!h?(showMessage("Error Sistem","Modal konfirmasi tidak tersedia.",!0),Promise.resolve(!1)):(y.textContent=t,h.textContent=e,d.classList.remove("hidden"),d.classList.add("flex"),new Promise(n=>{u=n}))}window.resolveOpnameConfirm=function(t){u&&(u(t),u=null),d&&(d.classList.add("hidden"),d.classList.remove("flex"))};window.showMessage=function(t,e,n=!1){f.textContent=t,$.textContent=e,f.className=n?"text-xl font-semibold text-red-600":"text-xl font-semibold text-emerald-600",g.classList.remove("hidden"),g.classList.add("flex")};window.hideMessage=function(){g.classList.add("hidden"),g.classList.remove("flex"),x(o)};window.openHistoryModal=async function(){if(!(!l||!p)){p.innerHTML='<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Memuat history...</td></tr>',l.classList.remove("hidden"),l.classList.add("flex");try{const t=await fetch("/api/stok_opname/history?limit=100");if(!t.ok)throw new Error(`HTTP error! status: ${t.status}`);const n=(await t.json()).items||[];if(n.length===0){p.innerHTML='<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat perubahan stok.</td></tr>';return}p.innerHTML=n.map(a=>{const r=Number(a.selisih)||0,m=r<0?"text-red-600":r>0?"text-emerald-600":"text-slate-600";return`
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">${s(a.waktu||"-")}</td>
                    <td class="px-4 py-3 text-slate-700 whitespace-nowrap">${s(a.diubah_oleh||"-")}</td>
                    <td class="px-4 py-3 text-slate-700">
                        <div class="font-semibold">${s(a.nama_barang||"-")}</div>
                        <div class="text-xs text-slate-500">${s(a.kode_barang||"-")}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-slate-700">${s(a.stok_sistem)}</td>
                    <td class="px-4 py-3 text-center text-slate-700">${s(a.stok_fisik)}</td>
                    <td class="px-4 py-3 text-center font-bold ${m}">${s(a.selisih)}</td>
                    <td class="px-4 py-3 text-slate-600">${s(a.keterangan||"-")}</td>
                </tr>
            `}).join("")}catch(t){console.error("Gagal memuat history stok opname:",t),p.innerHTML='<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Gagal memuat history.</td></tr>'}}};window.closeHistoryModal=function(){l&&(l.classList.add("hidden"),l.classList.remove("flex"))};document.addEventListener("DOMContentLoaded",x);window.goToPrevOpnamePage=function(){o>1&&x(o-1)};window.goToNextOpnamePage=function(){o<c&&x(o+1)};
