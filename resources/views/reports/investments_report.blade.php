<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Investasi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        h2 { border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <h1>📈 Laporan Portofolio Investasi</h1>
    <p>Dibuat pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

    @if(isset($portfolio) && !$portfolio->isEmpty())
        <h2>Ringkasan Aset</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Aset (Tipe)</th>
                    <th class="text-right">Jumlah Unit</th>
                    <th class="text-right">Avg. Harga Beli</th>
                    <th class="text-right">Total Modal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($portfolio as $asset)
                    <tr>
                        <td>{{ $asset->asset_name }} ({{ $asset->asset_type }})</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format($asset->total_quantity, 8), '0'), '.') }}</td>
                        <td class="text-right">Rp {{ number_format($asset->total_capital / $asset->total_quantity, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($asset->total_capital, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Anda belum memiliki aset investasi.</p>
    @endif

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Bot Keuangan Pribadi Anda.
    </div>
</body>
</html>
