<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        h1 { text-align: center; margin-bottom: 20px; }
        h2 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-green { color: green; }
        .text-red { color: red; }
        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Laporan Keuangan</h1>
        <p>Periode Laporan: {{ $periodName ?? 'Bulan Ini' }}</p>
        <p>Dibuat pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

        @if(isset($summary))
            <h2>Ringkasan Arus Kas</h2>
            <table>
                <tr>
                    <td>Total Pemasukan</td>
                    <td class="text-right text-green">Rp {{ number_format($summary['pemasukan'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Pengeluaran</td>
                    <td class="text-right text-red">Rp {{ number_format($summary['pengeluaran'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Selisih (Cash Flow)</th>
                    <th class="text-right">Rp {{ number_format($summary['selisih'], 0, ',', '.') }}</th>
                </tr>
            </table>
        @endif

        @if(isset($transactions) && !$transactions->isEmpty())
            <h2>Detail Transaksi</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Deskripsi</th>
                        <th>Kategori</th>
                        <th class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $trx)
                        <tr>
                            <td>{{ $trx->created_at->format('d-m-Y') }}</td>
                            <td>{{ ucfirst($trx->type) }}</td>
                            <td>{{ $trx->description }}</td>
                            <td>{{ $trx->category ?? '-' }}</td>
                            <td class="text-right @if($trx->type == 'pemasukan') text-green @else text-red @endif">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            Laporan ini dibuat secara otomatis oleh Bot Keuangan Pribadi Anda.
        </div>
    </div>
</body>
</html>
