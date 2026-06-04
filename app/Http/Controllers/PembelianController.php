<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

class PembelianController extends Controller
{
    public function index() { return view('transaksi_pembelian'); }

    private function generateNoPembelian(string $date): string
    {
        $prefix = 'PO-' . str_replace('-', '', $date) . '-';

        $lastNoPembelian = Pembelian::where('no_pembelian', 'like', $prefix . '%')
            ->orderByDesc('no_pembelian')
            ->value('no_pembelian');

        $lastNumber = 0;
        if (is_string($lastNoPembelian) && preg_match('/(\d{4})$/', $lastNoPembelian, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'supplier' => 'required|string',
                'items'    => 'required|array',
                'items.*.id_barang' => 'nullable|integer|exists:barangs,id',
                'items.*.namaBarang' => 'required|string|max:255',
                'items.*.kategori' => 'required|string|max:100',
                'items.*.hargaBeli' => 'required|numeric|min:1',
                'items.*.jumlah' => 'required|integer|min:1'
            ]);

            $total = 0;
            $today = now()->format('Y-m-d');
            $pembelian = null;

            for ($attempt = 0; $attempt < 3; $attempt++) {
                $noPembelian = $this->generateNoPembelian($today);

                try {
                    $pembelian = Pembelian::create([
                        'no_pembelian' => $noPembelian,
                        'nama_supplier' => $data['supplier'],
                        'user_id' => Auth::id() ?? 1,
                        'total_biaya' => 0,
                        'tanggal' => $today
                    ]);
                    break;
                } catch (QueryException $e) {
                    if ((int) ($e->errorInfo[1] ?? 0) !== 1062 || $attempt === 2) {
                        throw $e;
                    }
                }
            }

            if (!$pembelian) {
                throw new \RuntimeException('Gagal membuat nomor pembelian unik.');
            }

            foreach ($data['items'] as $item) {
                $idBarang = isset($item['id_barang']) ? (int)$item['id_barang'] : null;
                $jumlah = (int)$item['jumlah'];
                $hargaBeliInput = (float)$item['hargaBeli'];
                $kategoriInput = trim((string)($item['kategori'] ?? 'Umum'));
                $namaBarangInput = trim((string)$item['namaBarang']);

                if ($namaBarangInput === '') {
                    throw new \InvalidArgumentException('Nama barang tidak boleh kosong.');
                }

                $barang = $idBarang ? Barang::find($idBarang) : null;

                if ($barang) {
                    $barang->stok += $jumlah;
                    $barang->harga_beli = $hargaBeliInput;
                    $barang->kategori = $kategoriInput;
                    $barang->save();
                } else {
                    $lastBarang = Barang::whereDate('created_at', $today)->orderBy('id', 'desc')->first();
                    $urutanBarang = $lastBarang ? ((int) substr($lastBarang->kode_barang, -4)) + 1 : 1;
                    
                    $kodeBarangBaru = 'BRG-' . now()->format('Ymd') . '-' . str_pad($urutanBarang, 4, '0', STR_PAD_LEFT);

                    $barang = Barang::create([
                        'kode_barang' => $kodeBarangBaru,
                        'nama_barang' => $namaBarangInput,
                        'kategori' => $kategoriInput,
                        'harga_beli' => $hargaBeliInput,
                        'harga_jual' => $hargaBeliInput * 1.2,
                        'stok' => $jumlah
                    ]);
                }

                $subtotal = $hargaBeliInput * $jumlah;
                $total += $subtotal;

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $barang->id,
                    'harga_beli' => $hargaBeliInput,
                    'kuantitas' => $jumlah,
                    'subtotal' => $subtotal
                ]);
            }

            $pembelian->update(['total_biaya' => $total]);
            DB::commit();

            return response()->json(['success' => true, 'id_pembelian' => $pembelian->no_pembelian, 'message' => 'Stok berhasil diperbarui!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $search = $request->query('search_supplier');
        $query = Pembelian::orderBy('created_at', 'desc');
        
        if ($search) $query->where('nama_supplier', 'like', "%{$search}%");

        $history = $query->take(10)->get()->map(function($po) {
            return [
                'id_pembelian' => $po->no_pembelian,
                'supplier' => $po->nama_supplier,
                'total_beli' => $po->total_biaya,
                'tanggal_pembelian' => $po->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json(['purchases' => $history]);
    }
}