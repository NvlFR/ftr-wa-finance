<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tabungan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        .goal-container { margin-top: 25px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .goal-header { font-weight: bold; font-size: 16px; margin-bottom: 10px; }
        table { width: 100%; }
        td { padding: 4px 0; }
        .progress-bar-container { width: 100%; background-color: #e0e0e0; border-radius: 5px; margin-top: 5px; }
        .progress-bar { height: 18px; background-color: #4CAF50; border-radius: 5px; text-align: center; color: white; line-height: 18px; font-size: 10px; }
        .footer { text-align: center; margin-top: 40px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <h1>🎯 Laporan Progres Tabungan</h1>
    <p>Dibuat pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

    @if(isset($savings) && !$savings->isEmpty())
        @foreach($savings as $saving)
            <div class="goal-container">
                <div class="goal-header">
                    {{ $saving->is_emergency_fund ? '🚨' : '🎯' }} {{ $saving->goal_name }}
                </div>
                <table>
                    <tr>
                        <td>Terkumpul</td>
                        <td style="text-align: right;"><strong>Rp {{ number_format($saving->current_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Target</td>
                        <td style="text-align: right;">Rp {{ number_format($saving->target_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
                @php
                    $progress = $saving->target_amount > 0 ? ($saving->current_amount / $saving->target_amount) * 100 : 100;
                    if ($progress > 100) $progress = 100;
                @endphp
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width:{{ $progress }}%;">
                        {{ number_format($progress, 1) }}%
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <p>Anda belum memiliki tujuan tabungan apapun saat ini.</p>
    @endif

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Bot Keuangan Pribadi Anda.
    </div>
</body>
</html>
