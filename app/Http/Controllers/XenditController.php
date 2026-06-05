<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XenditController extends Controller
{
    public function __construct(private XenditService $xenditService) {}

    public function createInvoice(Request $request)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.id_barang'  => 'required|integer|exists:barangs,id',
            'items.*.jumlah'     => 'required|integer|min:1',
        ], [
            'items.*.id_barang.exists' => 'Salah satu barang tidak valid.',
            'items.*.jumlah.min'       => 'Jumlah barang minimal 1.',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $today = now()->format('Y-m-d');
            $urutan = Transaksi::whereDate('created_at', $today)->count() + 1;
            $noTransaksi = 'TRX-' . now()->format('Ymd') . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $barang = Barang::find($item['id_barang']);
                if ($barang->stok < $item['jumlah']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi! (Sisa: {$barang->stok})");
                }
                $itemSubtotal = $barang->harga_jual * $item['jumlah'];
                $subtotal += $itemSubtotal;
                $itemsData[] = compact('barang', 'item', 'itemSubtotal');
            }

            $total = (int) round($subtotal * 1.11);

            $transaksi = Transaksi::create([
                'no_transaksi'   => $noTransaksi,
                'user_id'        => Auth::id() ?? 1,
                'total_harga'    => $total,
                'tanggal'        => $today,
                'payment_method' => 'qris',
                'status'         => 'pending',
            ]);

            foreach ($itemsData as $data) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id'    => $data['barang']->id,
                    'kuantitas'    => $data['item']['jumlah'],
                    'subtotal'     => $data['itemSubtotal'],
                ]);
            }

            $description = "Pembayaran Kasir - {$noTransaksi}";
            $xenditResp  = $this->xenditService->createInvoice($noTransaksi, $total, $description);

            if (empty($xenditResp['invoice_url'])) {
                $errMsg = $xenditResp['message'] ?? ($xenditResp['error'] ?? 'Xendit tidak merespons dengan benar.');
                throw new \Exception("Gagal membuat Invoice Xendit: {$errMsg}");
            }

            $transaksi->update(['xendit_qr_id' => $xenditResp['id']]);

            DB::commit();

            return response()->json([
                'success'      => true,
                'no_transaksi' => $noTransaksi,
                'invoice_url'  => $xenditResp['invoice_url'],
                'amount'       => $total,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function checkStatus(string $noTransaksi)
    {
        $transaksi = Transaksi::where('no_transaksi', $noTransaksi)
            ->where('payment_method', 'qris')
            ->first();

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        return response()->json([
            'success'      => true,
            'status'       => $transaksi->status,
            'no_transaksi' => $transaksi->no_transaksi,
        ]);
    }

    public function cancelQr(string $noTransaksi)
    {
        $transaksi = Transaksi::where('no_transaksi', $noTransaksi)
            ->where('status', 'pending')
            ->first();

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Transaksi pending tidak ditemukan.'], 404);
        }

        DB::beginTransaction();
        try {
            $transaksi->detailTransaksis()->delete();
            $transaksi->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function webhook(Request $request)
    {
        $callbackToken = $request->header('x-callback-token');
        $expectedToken = config('services.xendit.webhook_token');

        if ($expectedToken && $callbackToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload    = $request->all();
        $status     = $payload['status'] ?? '';
        $externalId = $payload['external_id'] ?? null; 

        if ($status !== 'PAID' || !$externalId) {
            return response()->json(['message' => 'Event ignored.'], 200);
        }

        return $this->processPayment($externalId);
    }

    private function processPayment(string $noTransaksi)
    {
        DB::beginTransaction();
        try {
            $transaksi = Transaksi::where('no_transaksi', $noTransaksi)
                ->where('status', 'pending')
                ->with('detailTransaksis')
                ->lockForUpdate()
                ->first();

            if (!$transaksi) {
                DB::rollBack();
                return response()->json(['message' => 'Transaksi tidak ditemukan atau sudah diproses.'], 200);
            }

            foreach ($transaksi->detailTransaksis as $detail) {
                $barang = Barang::where('id', $detail->barang_id)->lockForUpdate()->first();
                if ($barang->stok < $detail->kuantitas) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi saat pembayaran!");
                }
                $barang->stok -= $detail->kuantitas;
                $barang->save();
            }

            $transaksi->update(['status' => 'paid']);

            DB::commit();
            return response()->json(['success' => true, 'no_transaksi' => $transaksi->no_transaksi]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xendit processPayment error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
