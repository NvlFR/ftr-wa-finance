<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Hutang & Piutang</title>
    <style>
        /* (Gunakan style CSS yang sama seperti template sebelumnya) */
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
    <h1>⛓️ Laporan Hutang & Piutang Aktif</h1>
    <p>Dibuat pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

    @if(isset($piutang) && !$piutang->isEmpty())
        <h2>Uang Anda di Luar (Piutang)</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Orang</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($piutang as $item)
                    <tr>
                        <td>{{ $item->person_name }}</td>
                        <td class="text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($hutang) && !$hutang->isEmpty())
        <h2>Kewajiban Anda (Hutang)</h2>
        <table>
            <thead>
                <tr>
                    <th>Kepada</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hutang as $item)
                    <tr>
                        <td>{{ $item->person_name }}</td>
                        <td class="text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if((!isset($piutang) || $piutang->isEmpty()) && (!isset($hutang) || $hutang->isEmpty()))
        <p>Selamat! Anda tidak memiliki hutang atau piutang aktif saat ini.</p>
    @endif

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Bot Keuangan Pribadi Anda.
    </div>
</body>
</html>
