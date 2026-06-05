let o=[];const b=document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");let x=[];const l=window.escapeHtml||function(e){return String(e??"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#39;").replace(/`/g,"&#96;")};function m(e){return new Intl.NumberFormat("id-ID",{style:"currency",currency:"IDR",minimumFractionDigits:0}).format(e)}const r=(e,t)=>{const a=document.getElementById("msg-title");if(!a){alert(`${e}

${t}`);return}a.textContent=e,e.toLowerCase().includes("sukses")?a.className="font-bold text-xl mb-2 text-emerald-600":e.toLowerCase().includes("error")||e.toLowerCase().includes("gagal")?a.className="font-bold text-xl mb-2 text-red-600":a.className="font-bold text-xl mb-2 text-slate-800",document.getElementById("msg-body").textContent=t,document.getElementById("msg-modal").classList.remove("hidden")};async function f(){try{const e=await fetch("/api/barang");e.ok&&(x=await e.json())}catch(e){console.error("Gagal load master barang",e)}}document.getElementById("add-item-form")?.addEventListener("submit",function(e){e.preventDefault();const t=document.getElementById("item-name").value.trim(),a=document.getElementById("item-category").value.trim(),s=parseFloat(document.getElementById("item-price").value),n=parseInt(document.getElementById("item-qty").value);if(!t){r("Validasi Gagal","Nama Barang / SKU wajib diisi");return}if(!a){r("Validasi Gagal","Kategori wajib diisi");return}if(!Number.isFinite(s)||s<=0){r("Validasi Gagal","Harga beli harus lebih dari 0");return}if(!Number.isInteger(n)||n<=0){r("Validasi Gagal","Qty harus bilangan bulat lebih dari 0");return}let i=x.find(c=>c.kode_barang&&c.kode_barang.toString()===t||c.nama_barang&&c.nama_barang.toLowerCase()===t.toLowerCase()),d=i?i.id_barang:null,h=i?i.nama_barang:t;const u=o.find(c=>c.id_barang===d&&d!==null);u?(u.jumlah+=n,u.kategori=a,u.hargaBeli=s):o.push({id_barang:d,namaBarang:h,kategori:a,hargaBeli:s,jumlah:n}),this.reset(),g()});window.removeItem=function(e){o.splice(e,1),g()};function g(){const e=document.getElementById("purchase-list-body"),t=document.getElementById("submit-purchase-btn"),a=document.getElementById("total-display");if(!e)return;e.innerHTML="";let s=0;if(o.length===0){e.innerHTML='<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400 italic">Belum ada item ditambahkan</td></tr>',t.disabled=!0,a.textContent=m(0);return}t.disabled=!1,o.forEach((n,i)=>{const d=n.hargaBeli*n.jumlah;s+=d,e.innerHTML+=`
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="px-4 py-3">
                    <p class="font-medium text-slate-800">${l(n.namaBarang)}</p>
                    <p class="text-xs text-slate-500">ID: ${n.id_barang?l(n.id_barang):'<span class="text-emerald-500 font-semibold italic">Barang Baru</span>'}</p>
                </td>
                <td class="px-4 py-3 text-slate-600">${l(n.kategori)}</td>
                <td class="px-4 py-3 text-right text-slate-600">${m(n.hargaBeli)}</td>
                <td class="px-4 py-3 text-center">
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-bold">${l(n.jumlah)}</span>
                </td>
                <td class="px-4 py-3 text-right font-bold text-emerald-600">${m(d)}</td>
                <td class="px-4 py-3 text-center">
                    <button onclick="removeItem(${i})" class="text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors text-xs font-semibold">
                        Hapus
                    </button>
                </td>
            </tr>`}),a.textContent=m(s)}document.getElementById("submit-purchase-btn")?.addEventListener("click",async function(){const e=document.getElementById("supplier-input").value;if(!e)return r("Validasi Gagal","Supplier wajib diisi");this.disabled=!0,this.innerHTML="Menyimpan...";try{const t=await fetch("/pembelian/store",{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json","X-CSRF-TOKEN":b},body:JSON.stringify({supplier:e,items:o})}),a=await t.json();t.ok&&a.success?(r("Sukses",`Transaksi Berhasil!
Nomor Faktur (PO): #${a.id_pembelian}
Stok di Manajemen Stok telah bertambah.`),o=[],document.getElementById("supplier-input").value="",g(),p()):r("Gagal",a.message||"Gagal menyimpan")}catch(t){r("Error Jaringan",t.message)}finally{this.innerHTML="Simpan Transaksi",o.length>0&&(this.disabled=!1)}});async function p(){const e=document.getElementById("history-container"),t=document.getElementById("filter-supplier")?.value||"";if(e){e.innerHTML='<p class="text-center text-slate-500 py-4 text-sm">Memuat data...</p>';try{const s=await(await fetch(`/pembelian/history?search_supplier=${t}`)).json();e.innerHTML="",s.purchases&&s.purchases.length>0?s.purchases.forEach(n=>{const i=n.tanggal_pembelian?n.tanggal_pembelian.split(" ")[0]:"-";e.innerHTML+=`
                    <div class="bg-white border border-slate-200 p-4 rounded-lg hover:border-emerald-500 hover:shadow-md transition-all cursor-default group mb-3">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-slate-800 text-sm group-hover:text-emerald-600 transition-colors">#${l(n.id_pembelian)}</span>
                            <span class="text-[10px] uppercase font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">${l(i)}</span>
                        </div>
                        <div class="text-xs text-slate-500 mb-1">Supplier</div>
                        <div class="text-sm font-semibold text-slate-700 mb-2 truncate">${l(n.supplier)}</div>
                        <div class="border-t border-slate-100 pt-2 flex justify-between items-center">
                            <div>
                                <span class="text-xs text-slate-400">Total</span>
                                <span class="text-sm font-bold text-emerald-600 ml-2">${m(n.total_beli)}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="deletePurchase('${l(n.id_pembelian)}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs font-semibold">Hapus</button>
                            </div>
                        </div>
                    </div>`}):e.innerHTML='<div class="text-center text-slate-400 py-10"><span class="text-sm">Tidak ada riwayat pembelian</span></div>'}catch{e.innerHTML='<p class="text-red-500 text-center py-4 text-sm">Gagal memuat history.</p>'}}}window.deletePurchase=async function(e){if(e&&confirm(`Hapus pembelian ${e}? Tindakan ini akan mengurangi stok sesuai item yang ada.`))try{const t=await fetch(`/pembelian/${encodeURIComponent(e)}`,{method:"DELETE",headers:{Accept:"application/json","X-CSRF-TOKEN":b}}),a=await t.json();t.ok&&a.success?(r("Sukses",a.message||"Pembelian berhasil dihapus."),p()):r("Gagal",a.message||"Gagal menghapus pembelian.")}catch(t){r("Error Jaringan",t.message)}};document.getElementById("refresh-history-btn")?.addEventListener("click",p);document.addEventListener("DOMContentLoaded",()=>{f(),document.getElementById("history-container")&&p()});
