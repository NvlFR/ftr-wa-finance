<?php
// app/Http/Controllers/Api/WebhookController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $phone = $request->input('phone');
        $message = trim($request->input('message'));
        $parts = explode(' ', $message);
        $keyword = strtolower($parts[0]);

        switch ($keyword) {
            case 'masuk':
            case 'keluar':
                return $this->handleTransaction($phone, $message);

            case 'hutang':
            case 'piutang':
                return $this->handleDebt($phone, $message);

            case 'lunas':
                return $this->handleMarkAsPaid($phone, $message);
            // case '/laporan':
            //     return $this->handleReport($phone, $message);


            case '/bantuan':
            case '/help':
                return $this->showHelp();

            default:
                return response()->json(['reply' => '❌ Perintah tidak dikenal. Ketik "/bantuan" untuk melihat daftar perintah.']);
        }
    }

    private function handleTransaction($phone, $message)
    {
        $parts = explode(' ', $message, 3);
        if (count($parts) < 3) {
            return response()->json(['reply' => '❌ Format salah. Contoh: keluar 50000 makan siang #makanan']);
        }

        $type = strtolower($parts[0]) === 'masuk' ? 'pemasukan' : 'pengeluaran';
        $amount = $parts[1];
        $fullDescription = $parts[2];

        if (!is_numeric($amount)) {
            return response()->json(['reply' => '❌ Jumlah harus angka.']);
        }

        // Cek dan ekstrak kategori
        $category = null;
        $description = $fullDescription;
        if (str_contains($fullDescription, '#')) {
            $descParts = explode('#', $fullDescription, 2);
            $description = trim($descParts[0]);
            $category = trim($descParts[1]);
        }

        Transaction::create([
            'user_phone' => $phone,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'category' => $category,
        ]);

        $formattedAmount = number_format($amount, 0, ',', '.');
        $reply = "✅ Berhasil mencatat **{$type}** sebesar *Rp {$formattedAmount}* untuk '{$description}'";
        if ($category) {
            $reply .= " dengan kategori *#{$category}*.";
        }

        return response()->json(['reply' => $reply]);
    }

    private function handleDebt($phone, $message)
    {
        $parts = explode(' ', $message, 4);
        if (count($parts) < 4) {
            return response()->json(['reply' => '❌ Format salah. Contoh: hutang 50000 Budi untuk makan']);
        }

        $type = strtolower($parts[0]); // hutang atau piutang
        $amount = $parts[1];
        $personName = $parts[2];
        $description = $parts[3];

        if (!is_numeric($amount)) {
            return response()->json(['reply' => '❌ Jumlah harus angka.']);
        }

        Debt::create([
            'user_phone' => $phone,
            'type' => $type,
            'person_name' => $personName,
            'amount' => $amount,
            'description' => $description,
        ]);

        $formattedAmount = number_format($amount, 0, ',', '.');
        $action = $type === 'hutang' ? 'dari' : 'kepada';
        $reply = "✅ Berhasil mencatat **{$type}** sebesar *Rp {$formattedAmount}* {$action} *{$personName}* untuk '{$description}'.";

        return response()->json(['reply' => $reply]);
    }

    private function handleMarkAsPaid($phone, $message)
    {
        $parts = explode(' ', $message, 3);
         if (count($parts) < 3) {
            return response()->json(['reply' => '❌ Format salah. Contoh: lunas 50000 Budi']);
        }

        $amount = $parts[1];
        $personName = $parts[2];

        $debt = Debt::where('user_phone', $phone)
            ->where('person_name', 'like', '%' . $personName . '%')
            ->where('amount', $amount)
            ->where('status', 'belum lunas')
            ->first();

        if ($debt) {
            $debt->status = 'lunas';
            $debt->save();

            $formattedAmount = number_format($debt->amount, 0, ',', '.');
            return response()->json(['reply' => "✅ Berhasil! Catatan {$debt->type} dari/kepada *{$debt->person_name}* sebesar *Rp {$formattedAmount}* telah ditandai **LUNAS**. 👍"]);
        } else {
            return response()->json(['reply' => "❌ Tidak ditemukan catatan hutang/piutang yang cocok untuk *{$personName}* sebesar *{$amount}*."]);
        }
    }

    private function showHelp()
    {
    $reply = "🤖 *Bantuan Bot Keuangan* 🤖\n\n";
    $reply .= "Berikut adalah format perintah yang tersedia:\n\n";
    $reply .= "1️⃣ *Pencatatan Transaksi*\n";
    $reply .= "`masuk [jumlah] [deskripsi] #[kategori]`\n";
    $reply .= "`keluar [jumlah] [deskripsi] #[kategori]`\n\n";

    $reply .= "2️⃣ *Pencatatan Hutang/Piutang*\n";
    $reply .= "`hutang [jumlah] [nama] [deskripsi]`\n";
    $reply .= "`piutang [jumlah] [nama] [deskripsi]`\n";
    $reply .= "`lunas [jumlah] [nama]`\n\n";

    $reply .= "3️⃣ *Melihat Laporan* (Baru!)\n";
    $reply .= "`/laporan` (Laporan hari ini)\n";
    $reply .= "`/laporan minggu`\n";
    $reply .= "`/laporan bulan`\n";
    $reply .= "`/laporan bulan #makanan` (Filter per kategori)\n";

    return response()->json(['reply' => $reply]);
    }
    // Tambahkan metode baru ini di dalam class WebhookController

private function handleReport($phone, $message)
{
    $parts = explode(' ', $message);
    // Defaultnya adalah laporan 'hari ini' jika tidak ada parameter lain
    $period = $parts[1] ?? 'hari';
    $category = null;

    // Cek apakah ada filter kategori, contoh: /laporan bulan #makanan
    if (str_contains($message, '#')) {
        $categoryParts = explode('#', $message, 2);
        $category = trim($categoryParts[1]);
        // Ambil periode dari bagian sebelum '#'
        $periodParts = explode(' ', trim($categoryParts[0]));
        $period = $periodParts[1] ?? 'bulan'; // Default ke bulan jika ada kategori
    }

    $query = Transaction::where('user_phone', $phone);

    // Filter berdasarkan periode waktu
    switch (strtolower($period)) {
        case 'hari':
        case 'today':
            $query->whereDate('created_at', today());
            $periodName = 'Hari Ini';
            break;
        case 'minggu':
        case 'week':
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $periodName = 'Minggu Ini';
            break;
        case 'bulan':
        case 'month':
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $periodName = 'Bulan Ini';
            break;
        default:
            return response()->json(['reply' => 'Periode tidak valid. Gunakan: hari, minggu, bulan.']);
    }

    // Filter berdasarkan kategori jika ada
    if ($category) {
        $query->where('category', $category);
        $periodName .= " (Kategori: #{$category})";
    }

    // Ambil semua transaksi yang cocok
    $transactions = $query->orderBy('created_at', 'desc')->get();

    if ($transactions->isEmpty()) {
        return response()->json(['reply' => "Tidak ada data transaksi untuk *{$periodName}*. 🤔"]);
    }

    // Hitung total
    $totalPemasukan = $transactions->where('type', 'pemasukan')->sum('amount');
    $totalPengeluaran = $transactions->where('type', 'pengeluaran')->sum('amount');
    $selisih = $totalPemasukan - $totalPengeluaran;

    // Format balasan
    $reply = "📊 *Laporan Keuangan {$periodName}*\n\n";
    $reply .= "💰 *Total Pemasukan:*\nRp " . number_format($totalPemasukan, 0, ',', '.') . "\n\n";
    $reply .= "💸 *Total Pengeluaran:*\nRp " . number_format($totalPengeluaran, 0, ',', '.') . "\n\n";
    $reply .= "⚖️ *Selisih (Cash Flow):*\nRp " . number_format($selisih, 0, ',', '.') . "\n";
    $reply .= "-----------------------------------\n";
    $reply .= "*5 Transaksi Terakhir:*\n";

    // Tampilkan 5 transaksi terakhir dari periode tersebut
    foreach ($transactions->take(5) as $trx) {
        $emoji = $trx->type == 'pemasukan' ? '🟢' : '🔴';
        $amountFormatted = number_format($trx->amount, 0, ',', '.');
        $reply .= "{$emoji} Rp {$amountFormatted} - {$trx->description}\n";
    }

    return response()->json(['reply' => $reply]);
}
}
