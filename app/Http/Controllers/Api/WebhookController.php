<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $phone = $request->input('phone');
        $message = trim($request->input('message'));

        $structuredCommand = $this->getStructuredCommandFromGroq($message);

        if (isset($structuredCommand['error'])) {
            return response()->json(['reply' => $structuredCommand['error']]);
        }

        // Pastikan command ada sebelum masuk ke switch
        if (!isset($structuredCommand['command'])) {
            return response()->json(['reply' => "Maaf, terjadi sedikit kebingungan. Coba lagi ya."]);
        }

        switch ($structuredCommand['command']) {
            case 'catat_transaksi':
                return $this->handleTransactionFromAI($phone, $structuredCommand['data']);
            case 'catat_hutang_piutang':
                return $this->handleDebtFromAI($phone, $structuredCommand['data']);
            case 'tandai_lunas':
                return $this->handleMarkAsPaidFromAI($phone, $structuredCommand['data']);
            case 'laporan_keuangan':
                // Untuk laporan, kita butuh pesan originalnya untuk parsing periode
                return $this->handleReportFromAI($phone, $message);
            case 'bantuan':
                return $this->showHelp();
            default:
                return response()->json(['reply' => "Maaf, saya tidak mengerti maksud Anda. Coba katakan dengan cara lain atau ketik /bantuan."]);
        }
    }

    private function getStructuredCommandFromGroq($userMessage) {
        // ... (FUNGSI INI TETAP SAMA SEPERTI SEBELUMNYA, TIDAK PERLU DIUBAH) ...
        // Kode yang sudah Anda miliki di sini sudah benar.
        // Kita biarkan saja.
        $apiKey = env('GROQ_API_KEY');
        if (empty($apiKey)) {
            Log::error('GROQ_API_KEY tidak ditemukan di .env');
            return ['error' => 'API Key untuk AI belum diatur di server.'];
        }
        $systemPrompt = <<<PROMPT
        Anda adalah asisten keuangan pribadi yang sangat pintar. Tugas utama Anda adalah mengubah pesan bahasa alami dari pengguna menjadi format JSON yang terstruktur agar bisa diproses oleh sistem.
        Selalu balas HANYA dengan format JSON, tanpa penjelasan apa pun.

        Berikut adalah format JSON yang harus Anda gunakan:
        1. Untuk mencatat transaksi:
        {"command":"catat_transaksi", "data":{"type":"pengeluaran" atau "pemasukan", "amount":_jumlah_angka_, "description":"_deskripsi_singkat_", "category":"_opsional_kategori_"}}

        2. Untuk mencatat hutang/piutang:
        {"command":"catat_hutang_piutang", "data":{"type":"hutang" atau "piutang", "amount":_jumlah_angka_, "person_name":"_nama_orang_", "description":"_deskripsi_singkat_"}}

        3. Untuk menandai lunas:
        {"command":"tandai_lunas", "data":{"amount":_jumlah_angka_, "person_name":"_nama_orang_"}}

        4. Untuk meminta laporan:
        {"command":"laporan_keuangan"}

        5. Jika pengguna meminta bantuan:
        {"command":"bantuan"}

        6. Jika Anda sama sekali tidak mengerti atau informasinya tidak lengkap:
        {"error":"Maaf, saya kurang paham. Bisa tolong perjelas lagi perintahnya?"}

        Contoh:
        User: tadi abis 35rb buat makan siang di warteg
        Output: {"command":"catat_transaksi", "data":{"type":"pengeluaran", "amount":35000, "description":"makan siang di warteg", "category":"makanan"}}

        User: masuk gaji 5jt
        Output: {"command":"catat_transaksi", "data":{"type":"pemasukan", "amount":5000000, "description":"gaji", "category":null}}

        User: aku ngutang ke budi 50rb buat bensin
        Output: {"command":"catat_hutang_piutang", "data":{"type":"hutang", "amount":50000, "person_name":"Budi", "description":"bensin"}}

        User: budi bayar utangnya yg 50rb
        Output: {"command":"tandai_lunas", "data":{"amount":50000, "person_name":"Budi"}}

        User: laporan bulan ini dong
        Output: {"command":"laporan_keuangan"}
        PROMPT;

        try {
            Log::info('Mencoba menghubungi API Groq...');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama3-8b-8192', 'messages' => [['role' => 'system', 'content' => $systemPrompt],['role' => 'user', 'content' => $userMessage],],
                'temperature' => 0.1, 'response_format' => ['type' => 'json_object'],
            ]);
            if ($response->successful()) {
                Log::info('Sukses! Respons dari Groq diterima.');
                $content = $response->json()['choices'][0]['message']['content'];
                return json_decode($content, true);
            }
            if ($response->failed()) {
                Log::error('API Groq merespons dengan error.', ['status' => $response->status(), 'body' => $response->body(),]);
                return ['error' => 'AI merespons dengan error. Status: ' . $response->status()];
            }
        } catch (\Exception $e) {
            Log::error('Exception saat menghubungi Groq: ' . $e->getMessage());
            return ['error' => 'Terjadi kesalahan koneksi ke server AI.'];
        }
        return ['error' => 'Terjadi kesalahan yang tidak diketahui saat menghubungi AI.'];
    }

    // --- INI ADALAH BAGIAN YANG BARU DAN PENTING ---

    private function handleTransactionFromAI($phone, $data)
    {
        Transaction::create([
            'user_phone' => $phone,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
        ]);

        $formattedAmount = number_format($data['amount'], 0, ',', '.');
        $reply = "✅ Sip! Berhasil mencatat **{$data['type']}** sebesar *Rp {$formattedAmount}* untuk '{$data['description']}'.";

        return response()->json(['reply' => $reply]);
    }

    private function handleDebtFromAI($phone, $data)
    {
        Debt::create([
            'user_phone' => $phone,
            'type' => $data['type'],
            'person_name' => $data['person_name'],
            'amount' => $data['amount'],
            'description' => $data['description'],
        ]);

        $formattedAmount = number_format($data['amount'], 0, ',', '.');
        $action = $data['type'] === 'hutang' ? 'dari' : 'kepada';
        $reply = "✅ Oke, berhasil dicatat! **{$data['type']}** sebesar *Rp {$formattedAmount}* {$action} *{$data['person_name']}* untuk '{$data['description']}'.";

        return response()->json(['reply' => $reply]);
    }

    private function handleMarkAsPaidFromAI($phone, $data)
    {
        $debt = Debt::where('user_phone', $phone)
            ->where('person_name', 'like', '%' . $data['person_name'] . '%')
            ->where('amount', $data['amount'])
            ->where('status', 'belum lunas')
            ->first();

        if ($debt) {
            $debt->status = 'lunas';
            $debt->save();

            $formattedAmount = number_format($debt->amount, 0, ',', '.');
            return response()->json(['reply' => "✅ Mantap! Catatan {$debt->type} dari/kepada *{$debt->person_name}* sebesar *Rp {$formattedAmount}* telah ditandai **LUNAS**. 👍"]);
        } else {
            return response()->json(['reply' => "❌ Hmm, sepertinya tidak ada catatan hutang/piutang yang cocok untuk *{$data['person_name']}* sebesar *{$data['amount']}*."]);
        }
    }

    // Kita gunakan kembali fungsi laporan yang lama karena parsing periodenya lebih mudah dilakukan dengan string biasa
    private function handleReportFromAI($phone, $message)
    {
        $parts = explode(' ', $message);
        $period = 'hari'; // Default
        foreach($parts as $part) {
            if (in_array(strtolower($part), ['minggu', 'mingguan', 'week'])) {
                $period = 'minggu';
                break;
            }
            if (in_array(strtolower($part), ['bulan', 'bulanan', 'month'])) {
                $period = 'bulan';
                break;
            }
        }

        $category = null;
        if (str_contains($message, '#')) {
            $categoryParts = explode('#', $message, 2);
            $category = trim($categoryParts[1]);
        }

        $query = Transaction::where('user_phone', $phone);

        switch ($period) {
            case 'hari': $query->whereDate('created_at', today()); $periodName = 'Hari Ini'; break;
            case 'minggu': $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); $periodName = 'Minggu Ini'; break;
            case 'bulan': $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]); $periodName = 'Bulan Ini'; break;
        }

        if ($category) {
            $query->where('category', $category);
            $periodName .= " (Kategori: #{$category})";
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        if ($transactions->isEmpty()) {
            return response()->json(['reply' => "Tidak ada data transaksi untuk *{$periodName}*. 🤔"]);
        }

        $totalPemasukan = $transactions->where('type', 'pemasukan')->sum('amount');
        $totalPengeluaran = $transactions->where('type', 'pengeluaran')->sum('amount');
        $selisih = $totalPemasukan - $totalPengeluaran;

        $reply = "📊 *Laporan Keuangan {$periodName}*\n\n";
        $reply .= "💰 *Total Pemasukan:*\nRp " . number_format($totalPemasukan, 0, ',', '.') . "\n\n";
        $reply .= "💸 *Total Pengeluaran:*\nRp " . number_format($totalPengeluaran, 0, ',', '.') . "\n\n";
        $reply .= "⚖️ *Selisih (Cash Flow):*\nRp " . number_format($selisih, 0, ',', '.') . "\n";
        $reply .= "-----------------------------------\n";
        $reply .= "*5 Transaksi Terakhir:*\n";

        foreach ($transactions->take(5) as $trx) {
            $emoji = $trx->type == 'pemasukan' ? '🟢' : '🔴';
            $amountFormatted = number_format($trx->amount, 0, ',', '.');
            $reply .= "{$emoji} Rp {$amountFormatted} - {$trx->description}\n";
        }

        return response()->json(['reply' => $reply]);
    }

    private function showHelp()
    {
        $reply = "🤖 *Bantuan Bot Keuangan (Versi AI)* 🤖\n\n";
        $reply .= "Sekarang Anda bisa berbicara dengan bahasa biasa! Coba saja bilang apa yang Anda mau.\n\n";
        $reply .= "*Contoh Perintah:*\n";
        $reply .= "- `kemarin aku jajan 15rb buat seblak`\n";
        $reply .= "- `dapet transferan 200rb dari ibu`\n";
        $reply .= "- `aku minjemin andi 50rb buat ongkos`\n";
        $reply .= "- `andi udah balikin utangnya yg 50rb`\n";
        $reply .= "- `laporan keuangan bulan ini dong`\n";
        $reply .= "- `total pengeluaran buat #transportasi minggu ini`\n\n";
        $reply .= "Cukup katakan saja, saya akan coba mengerti! 😉";

        return response()->json(['reply' => $reply]);
    }
}
