let g=[];const f=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content"),p=window.escapeHtml||function(t){return String(t??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;").replace(/'/g,"&#39;").replace(/`/g,"&#96;")};async function k(){const t=document.getElementById("barangId");if(t)try{const e=await fetch("/api/prediksi_stok/barang");if(!e.ok)throw new Error(`HTTP error! status: ${e.status}`);g=await e.json()||[],h()}catch(e){console.error("Gagal mengambil data barang:",e),t.innerHTML='<option value="">Gagal memuat produk</option>',r()}}function r(){const t=document.getElementById("barangId"),e=document.getElementById("btn-hitung");!t||!e||(e.disabled=!t.value)}function h(){const t=document.getElementById("barangId");if(t){if(t.innerHTML='<option value="">-- Pilih Nama Produk --</option>',g.length===0){t.innerHTML='<option value="">Data barang kosong</option>',r();return}g.forEach(e=>{const a=document.createElement("option");a.value=e.id_barang,a.textContent=e.nama_barang,t.appendChild(a)}),r()}}window.hitungPrediksi=async function(){const t=document.getElementById("barangId").value,e=30,a=document.getElementById("hasilBox");if(t){a.innerHTML=`
        <div class="text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto mb-2"></div>
            <p class="text-sm text-gray-500">Menganalisis data ${e} hari terakhir...</p>
        </div>
    `;try{const n=await fetch("/api/prediksi_stok/stok",{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":f},body:JSON.stringify({barangId:t})});if(!n.ok)throw new Error("Gagal mengambil hasil prediksi");const s=await n.json();v(s)}catch(n){console.error("Error Prediksi:",n),a.innerHTML='<p class="text-red-500 text-center">Terjadi kesalahan saat menghitung prediksi.</p>'}}};function v(t){const e=document.getElementById("hasilBox");e.innerHTML="";const a=parseInt(t.stok_saat_ini),n=parseInt(t.total_terjual),s=t.hari_bertahan,x=t.periode_analisis||30;let i="",l="",o="",d="";if(t.can_predict===!1||n===0)i="DATA TIDAK CUKUP",l="bg-red-600 text-white",o=`
            <div class="flex flex-col items-center justify-center py-10">
                <div class="p-6 bg-red-50 border border-red-100 rounded-2xl w-full">
                    <p class="text-red-600 text-sm font-bold tracking-tight text-center">
                        ${p(t.message||"Data riwayat penjualan tidak cukup untuk diprediksi")}
                    </p>
                    <p class="text-red-400 text-[10px] mt-2 font-medium text-center uppercase tracking-wide">
                        Prediksi hanya menggunakan riwayat penjualan 30 hari terakhir.
                    </p>
                </div>
            </div>
        `,d="";else{const c=a===0,u=s<7&&s>0;c?(i="STOK HABIS",l="bg-red-600 text-white"):(i=u?"RE-STOCK":"STOK AMAN",l=u?"bg-amber-500 text-white":"bg-emerald-500 text-white");let m=c?"0":s>365?"> 365":Math.round(s);o=`
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Estimasi Stok Habis Dalam</p>
            <div class="flex items-center justify-center gap-3">
                <span class="text-8xl font-black ${c?"text-red-600":"text-slate-800"} tracking-tighter">${m}</span>
                <span class="text-2xl font-black text-slate-400 uppercase">Hari</span>
            </div>
        `,d=`
            <div class="mt-6 pt-6 border-t border-slate-200 w-full text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Prediksi dihitung dari total ${n} unit terjual
                </span>
            </div>
        `}const b=`
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 fade-in w-full flex flex-col gap-8">
            <div class="flex justify-between items-start w-full">
                <div class="space-y-1">
                    <p class="text-lg font-black uppercase tracking-tight text-slate-900">HASIL ANALISIS</p>
                    <h3 class="text-2xl font-bold text-slate-500">${p(t.nama_barang)}</h3>
                    <p class="text-sm text-slate-400 font-medium">Tren <span class="text-slate-600">${p(x)} hari</span> terakhir</p>
                </div>
                <span class="px-4 py-1.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider ${l} shadow-sm">
                    ${i}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Stok Saat Ini</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black ${a===0?"text-red-600":"text-slate-800"}">${a}</span>
                        <span class="text-slate-400 text-xs font-bold uppercase">Unit</span>
                    </div>
                </div>
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-2">Rata-rata terjual</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-800">${t.rata_rata_harian}</span>
                        <span class="text-slate-400 text-xs font-bold uppercase">Unit/Hari</span>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100 text-center">
                ${o}
                ${d}
            </div>
        </div>
    `;e.innerHTML=b}document.addEventListener("DOMContentLoaded",()=>{k(),document.getElementById("barangId")?.addEventListener("change",r),r()});
