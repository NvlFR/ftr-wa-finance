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
        $reply .= "`keluar [jumlah] [deskripsi] #[kategori]`\n";
        $reply .= "_Contoh: keluar 25000 ngopi #jajan_\n\n";
        $reply .= "2️⃣ *Pencatatan Hutang/Piutang*\n";
        $reply .= "`hutang [jumlah] [nama] [deskripsi]`\n";
        $reply .= "_Contoh: hutang 50000 Budi untuk makan_\n\n";
        $reply .= "`piutang [jumlah] [nama] [deskripsi]`\n";
        $reply .= "_Contoh: piutang 100000 Andi dipinjam_\n\n";
        $reply .= "3️⃣ *Menandai Lunas*\n";
        $reply .= "`lunas [jumlah] [nama]`\n";
        $reply .= "_Contoh: lunas 50000 Budi_";

        return response()->json(['reply' => $reply]);
    }
}
