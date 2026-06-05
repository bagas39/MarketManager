<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .summary { width: 100%; margin-bottom: 20px; }
        .summary td { padding: 5px; font-size: 14px; font-weight: bold; }
        table.data { w-full; border-collapse: collapse; width: 100%; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        table.data th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #10B981; }
        .text-red { color: #EF4444; }
        .text-blue { color: #3B82F6; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KEUANGAN - MarketManager</h2>
        <p>Periode: {{ $startDate ? $startDate : 'Awal' }} s/d {{ $endDate ? $endDate : 'Sekarang' }}</p>
    </div>

    <table class="summary">
        <tr>
            <td class="text-green">Total Pemasukan: Rp {{ number_format($totalMasuk, 0, ',', '.') }}</td>
            <td class="text-red">Total Pengeluaran: Rp {{ number_format($totalKeluar, 0, ',', '.') }}</td>
            <td class="text-blue">Saldo Akhir: Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th class="text-center">Tipe</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail as $row)
            <tr>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['keterangan'] }}</td>
                <td class="text-center">{{ $row['tipe'] }}</td>
                <td class="text-right">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>