<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JsonDataService;

class BarangController extends Controller
{
    protected $db;

    public function __construct(JsonDataService $db)
    {
        $this->db = $db;
    }

    public function getBarang()
    {
        return response()->json($this->db->getBarang());
    }

    public function listStok(Request $request)
    {
        $offset = $request->query('offset', 0);
        $limit = $request->query('limit', 15);
        $searchNama = $request->query('search_nama');

        $allData = $this->db->getBarang();

        if ($searchNama) {
            $allData = array_filter($allData, function ($item) use ($searchNama) {
                $matchNama = stripos(strtolower($item['nama_barang']), strtolower($searchNama)) !== false;
                $matchId = stripos((string)$item['id_barang'], $searchNama) !== false;
                return $matchNama || $matchId;
            });
        }

        $allData = array_values($allData);
        $totalAvailable = count($allData);
        $pagedData = array_slice($allData, $offset, $limit);

        return response()->json([
            'items' => $pagedData,
            'totalAvailableItems' => $totalAvailable
        ]);
    }
}