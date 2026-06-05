document.addEventListener("DOMContentLoaded",function(){l(),document.getElementById("filter-btn").addEventListener("click",function(){const t=document.getElementById("start-date").value,e=document.getElementById("end-date").value;if(!t||!e){alert("Harap pilih kedua tanggal!");return}l(t,e)});async function l(t="",e=""){try{let n="/api/laporan_keuangan";t&&e&&(n+=`?start_date=${t}&end_date=${e}`);const r=await(await fetch(n)).json();document.getElementById("total-masuk").innerText=`Rp ${r.ringkasan.total_masuk.toLocaleString()}`,document.getElementById("total-keluar").innerText=`Rp ${r.ringkasan.total_keluar.toLocaleString()}`,document.getElementById("saldo-akhir").innerText=`Rp ${r.ringkasan.saldo_akhir.toLocaleString()}`;const o=document.getElementById("laporan-table-body");if(r.detail.length===0){o.innerHTML='<tr><td colspan="4" class="px-4 py-10 text-center text-gray-400 sm:px-6">Tidak ada data untuk periode ini</td></tr>';return}const d=window.escapeHtml||function(a){return String(a??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/'/g,"&#39;").replace(/`/g,"&#96;")};o.innerHTML=r.detail.map(a=>`
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm text-gray-600 sm:px-6 sm:py-4">${d(a.tanggal)}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-800 sm:px-6 sm:py-4 break-words">${d(a.keterangan)}</td>
                    <td class="px-4 py-3 text-center sm:px-6 sm:py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase ${a.tipe==="Masuk"?"bg-emerald-100 text-emerald-700":"bg-red-100 text-red-700"}">
                            ${d(a.tipe)}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold sm:px-6 sm:py-4 ${a.tipe==="Masuk"?"text-emerald-600":"text-red-600"}">
                        Rp ${String(a.jumlah).replace(/\B(?=(\d{3})+(?!\d))/g,",")}
                    </td>
                </tr>
            `).join("")}catch(n){console.error("Error:",n)}}document.getElementById("export-pdf-btn").addEventListener("click",function(){const t=document.getElementById("start-date").value,e=document.getElementById("end-date").value;let n="/laporan_keuangan/export-pdf";t&&e&&(n+=`?start_date=${t}&end_date=${e}`),window.open(n,"_blank")})});
