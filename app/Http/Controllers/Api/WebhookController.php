<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecurringTransaction;
use App\Models\Debt;
use App\Models\Transaction;
use App\Models\Saving;
use App\Models\Budget;
use App\Models\InvestmentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
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
                return $this->handleReportFromAI($phone, $message);
            case 'catat_investasi':
                return $this->handleInvestmentFromAI($phone, $structuredCommand['data']);
            case 'lihat_portfolio':
                return $this->handlePortfolio($phone);
            case 'buat_tabungan':
                return $this->handleCreateSavingGoal($phone, $structuredCommand['data']);
            case 'tambah_tabungan':
                return $this->handleAddToSaving($phone, $structuredCommand['data']);
            case 'lihat_tabungan':
                return $this->handleShowSavings($phone);
            case 'buat_dana_darurat':
                return $this->handleCreateEmergencyFund($phone, $structuredCommand['data']);
            case 'tambah_dana_darurat':
                return $this->handleAddToEmergencyFund($phone, $structuredCommand['data']);
            case 'pakai_dana_darurat':
                return $this->handleUseEmergencyFund($phone, $structuredCommand['data']);
            case 'lihat_dana_darurat':
                return $this->handleShowEmergencyFund($phone);
            case 'bantuan':
                return $this->showHelp();
            case 'set_budget':
                return $this->handleSetBudget($phone, $structuredCommand['data']);
            case 'lihat_budget':
                return $this->handleShowBudget($phone);
            case 'buat_transaksi_berulang':
                return $this->handleCreateRecurring($phone, $structuredCommand['data']);
            case 'lihat_transaksi_berulang':
                return $this->handleShowRecurring($phone);
            case 'hapus_transaksi_berulang':
                return $this->handleDeleteRecurring($phone, $structuredCommand['data']);

            // --- INI BAGIAN BARU YANG PENTING ---
            case 'minta_info_tambahan':
                // Jika AI butuh info lebih, kita kirim pertanyaannya ke pengguna
                return response()->json(['reply' => $structuredCommand['data']['pertanyaan']]);

            default:
                return response()->json(['reply' => "Maaf, saya tidak mengerti maksud Anda. Coba katakan dengan cara lain atau ketik /bantuan."]);
        }
    }

    // Tambahkan fungsi baru ini di dalam class WebhookController
// Tambahkan 4 fungsi baru ini di dalam class WebhookController
// Tambahkan 2 fungsi baru ini di dalam class WebhookController
// Tambahkan 3 fungsi baru ini di dalam class WebhookController

private function handleCreateRecurring($phone, $data)
{
    RecurringTransaction::create([
        'user_phone' => $phone,
        'type' => $data['type'],
        'amount' => $data['amount'],
        'description' => $data['description'],
        'category' => $data['category'] ?? null,
        'day_of_month' => $data['day_of_month'],
    ]);

    $actionDescription = "Berhasil membuat aturan transaksi berulang '{$data['description']}' yang akan berjalan setiap tanggal {$data['day_of_month']}.";
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleShowRecurring($phone)
{
    $recurrings = RecurringTransaction::where('user_phone', $phone)->where('is_active', true)->get();

    if ($recurrings->isEmpty()) {
        return response()->json(['reply' => "Anda belum punya aturan transaksi berulang yang aktif."]);
    }

    $reply = "🔁 *Daftar Transaksi Berulang Anda*\n\n";
    foreach ($recurrings as $item) {
        $emoji = $item->type == 'pemasukan' ? '🟢' : '🔴';
        $amount = number_format($item->amount, 0, ',', '.');
        $reply .= "{$emoji} *{$item->description}*\n";
        $reply .= "   - Jumlah: Rp {$amount}\n";
        $reply .= "   - Kategori: {$item->category}\n";
        $reply .= "   - Dicatat setiap tgl: *{$item->day_of_month}*\n\n";
    }

    return response()->json(['reply' => $reply]);
}

private function handleDeleteRecurring($phone, $data)
{
    $deleted = RecurringTransaction::where('user_phone', $phone)
        ->where('description', 'like', '%' . $data['description'] . '%')
        ->delete();

    if ($deleted) {
        $actionDescription = "Berhasil menghapus aturan transaksi berulang untuk '{$data['description']}'.";
        $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);
        return response()->json(['reply' => $naturalReply]);
    }

    return response()->json(['reply' => "Tidak ditemukan aturan transaksi berulang dengan deskripsi '{$data['description']}'."]);
}
private function handleSetBudget($phone, $data)
{
    $currentMonth = now()->month;
    $currentYear = now()->year;

    // Gunakan updateOrCreate untuk membuat budget baru atau update yang sudah ada
    Budget::updateOrCreate(
        [
            'user_phone' => $phone,
            'category' => strtolower($data['category']),
            'month' => $currentMonth,
            'year' => $currentYear,
        ],
        ['amount' => $data['amount']]
    );

    $actionDescription = "Berhasil mengatur budget untuk kategori '{$data['category']}' bulan ini sebesar Rp " . number_format($data['amount'], 0, ',', '.');
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleShowBudget($phone)
{
    $currentMonth = now()->month;
    $currentYear = now()->year;

    $budgets = Budget::where('user_phone', $phone)
        ->where('month', $currentMonth)
        ->where('year', $currentYear)
        ->get();

    if ($budgets->isEmpty()) {
        return response()->json(['reply' => "Anda belum mengatur budget untuk bulan ini. Coba atur dengan perintah 'set budget ...'"]);
    }

    // Ambil total pengeluaran per kategori untuk bulan ini
    $spendings = Transaction::where('user_phone', $phone)
        ->where('type', 'pengeluaran')
        ->whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->select('category', DB::raw('SUM(amount) as total_spent'))
        ->groupBy('category')
        ->pluck('total_spent', 'category');

    $reply = "📊 *Ringkasan Budget Bulan " . now()->translatedFormat('F Y') . "*\n\n";

    foreach ($budgets as $budget) {
        $spent = $spendings[strtolower($budget->category)] ?? 0;
        $remaining = $budget->amount - $spent;
        $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
        $statusEmoji = $percentage >= 100 ? '🔥' : ($percentage >= 80 ? '⚠️' : '✅');

        $reply .= "{$statusEmoji} *Kategori: {$budget->category}*\n";
        $reply .= "   - *Terpakai:* Rp " . number_format($spent, 0, ',', '.') . "\n";
        $reply .= "   - *Sisa:* Rp " . number_format($remaining, 0, ',', '.') . "\n";
        $reply .= "   - *Budget:* Rp " . number_format($budget->amount, 0, ',', '.') . " (" . number_format($percentage, 1) . "%)\n\n";
    }

    return response()->json(['reply' => $reply]);
}

private function handleCreateEmergencyFund($phone, $data)
{
    // Pengguna hanya boleh punya 1 dana darurat. Kita gunakan updateOrCreate.
    $savingGoal = Saving::updateOrCreate(
        ['user_phone' => $phone, 'is_emergency_fund' => true],
        [
            'goal_name' => 'Dana Darurat',
            'target_amount' => $data['target_amount'],
            'status' => 'ongoing',
        ]
    );

    $actionDescription = "Berhasil mengatur target Dana Darurat Anda menjadi Rp " . number_format($data['target_amount'], 0, ',', '.');
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleAddToEmergencyFund($phone, $data)
{
    $emergencyFund = Saving::where('user_phone', $phone)->where('is_emergency_fund', true)->first();

    if (!$emergencyFund) {
        return response()->json(['reply' => "Anda belum mengatur target Dana Darurat. Set target dulu dengan perintah 'set dana darurat ...'"]);
    }

    // 1. Catat sebagai 'pengeluaran' dari dompet utama
    Transaction::create([
        'user_phone' => $phone, 'type' => 'pengeluaran', 'amount' => $data['amount'],
        'description' => 'Menabung untuk Dana Darurat', 'category' => 'dana darurat',
    ]);

    // 2. Tambahkan ke saldo dana darurat
    $emergencyFund->current_amount += $data['amount'];
    $emergencyFund->save();

    $actionDescription = "Berhasil menambah Rp " . number_format($data['amount'], 0, ',', '.') . " ke Dana Darurat Anda.";
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleUseEmergencyFund($phone, $data)
{
    $emergencyFund = Saving::where('user_phone', $phone)->where('is_emergency_fund', true)->first();

    if (!$emergencyFund) {
        return response()->json(['reply' => "Anda belum memiliki Dana Darurat untuk dipakai."]);
    }
    if ($emergencyFund->current_amount < $data['amount']) {
        return response()->json(['reply' => "Dana Darurat Anda tidak mencukupi. Saldo saat ini: Rp " . number_format($emergencyFund->current_amount, 0, ',', '.')]);
    }

    // 1. Kurangi saldo dana darurat
    $emergencyFund->current_amount -= $data['amount'];
    $emergencyFund->save();

    // 2. Catat sebagai 'pemasukan' ke dompet utama
    Transaction::create([
        'user_phone' => $phone, 'type' => 'pemasukan', 'amount' => $data['amount'],
        'description' => "Pakai Dana Darurat: {$data['description']}", 'category' => 'dana darurat',
    ]);

    $actionDescription = "Berhasil memakai Dana Darurat sebesar Rp " . number_format($data['amount'], 0, ',', '.') . " untuk '{$data['description']}'. Jangan lupa diisi lagi ya!";
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleShowEmergencyFund($phone)
{
    $emergencyFund = Saving::where('user_phone', $phone)->where('is_emergency_fund', true)->first();

    if (!$emergencyFund) {
        return response()->json(['reply' => "Anda belum mengatur Dana Darurat. Yuk, atur targetnya dengan perintah 'set dana darurat ...'"]);
    }

    $progress = $emergencyFund->target_amount > 0 ? ($emergencyFund->current_amount / $emergencyFund->target_amount) * 100 : 100;

    $reply = "🚨 *Status Dana Darurat Anda*\n\n";
    $reply .= "   - *Terkumpul:* Rp " . number_format($emergencyFund->current_amount, 0, ',', '.') . "\n";
    $reply .= "   - *Target:* Rp " . number_format($emergencyFund->target_amount, 0, ',', '.') . "\n";
    $reply .= "   - *Progres:* " . number_format($progress, 1) . "% dari target\n\n";
    $reply .= "_Ingat, dana ini untuk keadaan mendesak. Selalu prioritaskan untuk mengisinya kembali jika terpakai._";

    return response()->json(['reply' => $reply]);
}

    // Tambahkan 3 fungsi baru ini di dalam class WebhookController

private function handleCreateSavingGoal($phone, $data)
{
    $existingGoal = Saving::where('user_phone', $phone)
        ->where('goal_name', 'like', $data['goal_name'])
        ->first();

    if ($existingGoal) {
        return response()->json(['reply' => "⚠️ Anda sudah punya tujuan tabungan '{$data['goal_name']}'. Gunakan nama lain ya."]);
    }

    Saving::create([
        'user_phone' => $phone,
        'goal_name' => $data['goal_name'],
        'target_amount' => $data['target_amount'],
    ]);

    $actionDescription = "Berhasil membuat tujuan tabungan baru '{$data['goal_name']}' dengan target Rp " . number_format($data['target_amount'], 0, ',', '.');
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleAddToSaving($phone, $data)
{
    $savingGoal = Saving::where('user_phone', $phone)
        ->where('goal_name', 'like', '%' . $data['goal_name'] . '%')
        ->first();

    if (!$savingGoal) {
        return response()->json(['reply' => "❌ Tidak ditemukan tujuan tabungan '{$data['goal_name']}'. Buat dulu ya dengan perintah 'buat tabungan...'."]);
    }

    if ($savingGoal->status === 'completed') {
        return response()->json(['reply' => "🎉 Selamat! Tabungan '{$savingGoal->goal_name}' Anda sudah tercapai. Anda tidak bisa menambah lagi."]);
    }

    // 1. Catat sebagai 'pengeluaran' dari dompet utama
    Transaction::create([
        'user_phone' => $phone,
        'type' => 'pengeluaran',
        'amount' => $data['amount'],
        'description' => "Menabung untuk {$savingGoal->goal_name}",
        'category' => 'tabungan',
    ]);

    // 2. Tambahkan ke saldo tabungan
    $savingGoal->current_amount += $data['amount'];

    // 3. Cek apakah target sudah tercapai
    if ($savingGoal->current_amount >= $savingGoal->target_amount) {
        $savingGoal->status = 'completed';
    }

    $savingGoal->save();

    $actionDescription = "Berhasil menambah Rp " . number_format($data['amount'], 0, ',', '.') . " ke tabungan '{$savingGoal->goal_name}'.";
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

private function handleShowSavings($phone)
{
    $savings = Saving::where('user_phone', $phone)->get();

    if ($savings->isEmpty()) {
        return response()->json(['reply' => "Anda belum punya tujuan tabungan. Yuk, buat satu dengan perintah 'buat tabungan...'!"]);
    }

    $reply = "🎯 *Progres Tabungan Anda*\n\n";

    foreach ($savings as $saving) {
        $progress = ($saving->current_amount / $saving->target_amount) * 100;
        $statusEmoji = $saving->status === 'completed' ? '✅' : '⏳';

        $reply .= "{$statusEmoji} *{$saving->goal_name}*\n";
        $reply .= "   - *Terkumpul:* Rp " . number_format($saving->current_amount, 0, ',', '.') . "\n";
        $reply .= "   - *Target:* Rp " . number_format($saving->target_amount, 0, ',', '.') . "\n";
        $reply .= "   - *Progres:* " . number_format($progress, 1) . "%\n\n";
    }

    return response()->json(['reply' => $reply]);
}
private function handlePortfolio($phone)
{
    // Query ini akan mengelompokkan aset dan menghitung totalnya
    $assets = InvestmentTransaction::where('user_phone', $phone)
        ->select(
            'asset_name',
            'asset_type',
            // Menjumlahkan quantity 'beli' dan mengurangi quantity 'jual'
            DB::raw("SUM(CASE WHEN type = 'beli' THEN quantity ELSE -quantity END) as total_quantity"),
            // Menjumlahkan total_amount 'beli' dan mengurangi total_amount 'jual'
            DB::raw("SUM(CASE WHEN type = 'beli' THEN total_amount ELSE -total_amount END) as total_capital")
        )
        ->groupBy('asset_name', 'asset_type')
        ->get();

    // Filter aset yang quantity-nya sudah 0 atau kurang (sudah dijual semua)
    $portfolio = $assets->filter(function ($asset) {
        return $asset->total_quantity > 0.00000001; // Toleransi kecil untuk angka desimal
    });

    if ($portfolio->isEmpty()) {
        return response()->json(['reply' => "Anda belum memiliki aset investasi apapun. Coba catat transaksi pertama Anda!"]);
    }

    $reply = "💼 *Portofolio Investasi Anda*\n\n";

    foreach ($portfolio as $asset) {
        // Hitung rata-rata harga beli
        $averagePrice = $asset->total_capital / $asset->total_quantity;

        // Pilih emoji berdasarkan tipe aset
        $emoji = match(strtolower($asset->asset_type)) {
            'crypto' => '💎',
            'saham' => '📈',
            'emas' => '🪙',
            'reksadana' => '📄',
            default => '💰'
        };

        $reply .= "{$emoji} *{$asset->asset_name}* ({$asset->asset_type})\n";
        $reply .= "   - *Jumlah:* " . rtrim(rtrim(number_format($asset->total_quantity, 8), '0'), '.') . " unit\n";
        $reply .= "   - *Avg. Beli:* Rp " . number_format($averagePrice, 2, ',', '.') . " /unit\n";
        $reply .= "   - *Modal:* Rp " . number_format($asset->total_capital, 2, ',', '.') . "\n\n";
    }

    return response()->json(['reply' => $reply]);
}

    private function getStructuredCommandFromGroq($userMessage)
    {
        $apiKey = env('GROQ_API_KEY');
        if (empty($apiKey)) {
            Log::error('GROQ_API_KEY tidak ditemukan di .env');
            return ['error' => 'API Key untuk AI belum diatur di server.'];
        }
        // Ganti HANYA bagian systemPrompt di dalam fungsi getStructuredCommandFromGroq Anda

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

4. Untuk mencatat investasi:
{"command":"catat_investasi", "data":{"type":"beli" atau "jual", "asset_name":"_nama_aset_", "asset_type":"crypto" atau "saham" atau "emas" atau lainnya, "quantity":_jumlah_unit_, "price_per_unit":_harga_per_unit_}}

5. Untuk meminta laporan:
{"command":"laporan_keuangan"}

6. Jika pengguna meminta bantuan:
{"command":"bantuan"}

7. Jika informasi untuk sebuah perintah TIDAK LENGKAP (misal: "catat pengeluaran" tanpa jumlah, atau "catat hutang" tanpa nama/jumlah), minta informasi tambahan:
{"command":"minta_info_tambahan", "data":{"pertanyaan":"_pertanyaan_untuk_minta_info_tambahan_"}}

8. Jika Anda sama sekali tidak mengerti atau perintahnya di luar konteks keuangan:
{"error":"Maaf, saya kurang paham. Bisa tolong perjelas lagi perintahnya?"}

9. Untuk melihat ringkasan portofolio investasi:
{"command":"lihat_portfolio"}

10. Untuk membuat tujuan tabungan baru:
{"command":"buat_tabungan", "data":{"goal_name":"_nama_tujuan_", "target_amount":_jumlah_target_}}

11. Untuk menambah uang ke tabungan yang sudah ada:
{"command":"tambah_tabungan", "data":{"goal_name":"_nama_tujuan_", "amount":_jumlah_uang_}}

12. Untuk melihat progres semua tabungan:
{"command":"lihat_tabungan"}

13. Untuk membuat/set target Dana Darurat:
{"command":"buat_dana_darurat", "data":{"target_amount":_jumlah_target_}}

14. Untuk menambah uang ke Dana Darurat:
{"command":"tambah_dana_darurat", "data":{"amount":_jumlah_uang_}}

15. Untuk memakai/menarik uang dari Dana Darurat:
{"command":"pakai_dana_darurat", "data":{"amount":_jumlah_uang_, "description":"_alasan_pemakaian_"}}

16. Untuk melihat status Dana Darurat:
{"command":"lihat_dana_darurat"}

17. Untuk mengatur budget bulanan untuk sebuah kategori:
{"command":"set_budget", "data":{"category":"_nama_kategori_", "amount":_jumlah_uang_}}

18. Untuk melihat ringkasan budget bulan ini:
{"command":"lihat_budget"}

19. Untuk membuat transaksi berulang:
{"command":"buat_transaksi_berulang", "data":{"type":"pemasukan" atau "pengeluaran", "amount":_jumlah_, "description":"_deskripsi_", "category":"_opsional_kategori_", "day_of_month":_tanggal_}}

20. Untuk melihat daftar transaksi berulang:
{"command":"lihat_transaksi_berulang"}

21. Untuk menghapus transaksi berulang:
{"command":"hapus_transaksi_berulang", "data":{"description":"_deskripsi_unik_"}}

Contoh:
User: tadi abis 35rb buat makan siang
Output: {"command":"catat_transaksi", "data":{"type":"pengeluaran", "amount":35000, "description":"makan siang", "category":"makanan"}}

User: aku punya utang
Output: {"command":"minta_info_tambahan", "data":{"pertanyaan":"Tentu, hutang kepada siapa dan berapa jumlahnya?"}}

User: pengeluaran
Output: {"command":"minta_info_tambahan", "data":{"pertanyaan":"Oke, mau catat pengeluaran apa dan berapa jumlahnya?"}}

User: beli saham bbca 1 lot harga 9500
Output: {"command":"catat_investasi", "data":{"type":"beli", "asset_name":"Saham BBCA", "asset_type":"saham", "quantity":100, "price_per_unit":9500}}

User: jual crypto bitcoin 0.01 btc
Output: {"command":"minta_info_tambahan", "data":{"pertanyaan":"Tentu, dijual di harga berapa per BTC nya?"}}

User: /portfolio
Output: {"command":"lihat_portfolio"}

User: liat portofolio investasi saya
Output: {"command":"lihat_portfolio"}

User: asetku ada apa aja?
Output: {"command":"lihat_portfolio"}

User: buat tabungan laptop baru target 15jt
Output: {"command":"buat_tabungan", "data":{"goal_name":"Laptop Baru", "target_amount":15000000}}

User: nabung 500rb buat laptop baru
Output: {"command":"tambah_tabungan", "data":{"goal_name":"Laptop Baru", "amount":500000}}

User: /tabungan
Output: {"command":"lihat_tabungan"}

User: set dana darurat 10jt
Output: {"command":"buat_dana_darurat", "data":{"target_amount":10000000}}

User: tambah dana darurat 250rb
Output: {"command":"tambah_dana_darurat", "data":{"amount":250000}}

User: pake dana darurat 1jt buat servis motor
Output: {"command":"pakai_dana_darurat", "data":{"amount":1000000, "description":"servis motor"}}

User: /danadarurat
Output: {"command":"lihat_dana_darurat"}

User: set budget makanan bulan ini 1.5jt
Output: {"command":"set_budget", "data":{"category":"makanan", "amount":1500000}}

User: /budget
Output: {"command":"lihat_budget"}

User: atur pengeluaran bulanan netflix 186rb setiap tanggal 10
Output: {"command":"buat_transaksi_berulang", "data":{"type":"pengeluaran", "amount":186000, "description":"Netflix", "category":"hiburan", "day_of_month":10}}

User: /rutin
Output: {"command":"lihat_transaksi_berulang"}

User: hapus pengeluaran rutin netflix
Output: {"command":"hapus_transaksi_berulang", "data":{"description":"Netflix"}}
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama3-8b-8192', 'messages' => [['role' => 'system', 'content' => $systemPrompt],['role' => 'user', 'content' => $userMessage],],
                'temperature' => 0.1, 'response_format' => ['type' => 'json_object'],
            ]);
            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                return json_decode($content, true);
            }
            if ($response->failed()) {
                Log::error('API Groq (pemahaman) merespons dengan error.', ['status' => $response->status(), 'body' => $response->body(),]);
                return ['error' => 'AI merespons dengan error. Status: ' . $response->status()];
            }
        } catch (\Exception $e) {
            Log::error('Exception saat menghubungi Groq (pemahaman): ' . $e->getMessage());
            return ['error' => 'Terjadi kesalahan koneksi ke server AI.'];
        }
        return ['error' => 'Terjadi kesalahan yang tidak diketahui saat menghubungi AI.'];
    }


    // Tambahkan fungsi baru ini di dalam class WebhookController

private function handleInvestmentFromAI($phone, $data)
{
    // Pastikan data esensial ada
    if (!isset($data['type'], $data['asset_name'], $data['asset_type'], $data['quantity'], $data['price_per_unit'])) {
        return response()->json(['reply' => 'Maaf, informasi investasinya kurang lengkap untuk dicatat.']);
    }

    $quantity = $data['quantity'];
    $pricePerUnit = $data['price_per_unit'];
    $totalAmount = $quantity * $pricePerUnit;

    InvestmentTransaction::create([
        'user_phone' => $phone,
        'type' => $data['type'],
        'asset_name' => $data['asset_name'],
        'asset_type' => $data['asset_type'],
        'quantity' => $quantity,
        'price_per_unit' => $pricePerUnit,
        'total_amount' => $totalAmount,
    ]);

    $formattedTotal = number_format($totalAmount, 0, ',', '.');
    $actionDescription = "Berhasil mencatat transaksi {$data['type']} aset {$data['asset_type']} '{$data['asset_name']}' senilai Rp {$formattedTotal}.";
    $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

    return response()->json(['reply' => $naturalReply]);
}

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
        $actionDescription = "Berhasil mencatat {$data['type']} sebesar Rp {$formattedAmount} untuk '{$data['description']}'.";
        $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

        return response()->json(['reply' => $naturalReply]);
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
        $action = $data['type'] === 'hutang' ? 'berhutang' : 'memberi piutang';
        $actionDescription = "Berhasil mencatat bahwa pengguna {$action} sebesar Rp {$formattedAmount} kepada {$data['person_name']} untuk '{$data['description']}'.";
        $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

        return response()->json(['reply' => $naturalReply]);
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
            $actionDescription = "Berhasil menandai lunas untuk {$debt->type} dari/kepada {$debt->person_name} sebesar Rp {$formattedAmount}.";
            $naturalReply = $this->getNaturalResponseFromGroq($actionDescription);

            return response()->json(['reply' => $naturalReply]);
        } else {
            return response()->json(['reply' => "❌ Hmm, sepertinya tidak ada catatan hutang/piutang yang cocok untuk *{$data['person_name']}* sebesar *{$data['amount']}*."]);
        }
    }

    private function handleReportFromAI($phone, $message)
    {
        $parts = explode(' ', $message);
        $period = 'hari';
        foreach($parts as $part) {
            if (in_array(strtolower($part), ['minggu', 'mingguan', 'week'])) { $period = 'minggu'; break; }
            if (in_array(strtolower($part), ['bulan', 'bulanan', 'month'])) { $period = 'bulan'; break; }
        }
        $category = null;
        if (str_contains($message, '#')) {
            $categoryParts = explode('#', $message, 2); $category = trim($categoryParts[1]);
        }
        $query = Transaction::where('user_phone', $phone);
        switch ($period) {
            case 'hari': $query->whereDate('created_at', today()); $periodName = 'Hari Ini'; break;
            case 'minggu': $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); $periodName = 'Minggu Ini'; break;
            case 'bulan': $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]); $periodName = 'Bulan Ini'; break;
        }
        if ($category) { $query->where('category', $category); $periodName .= " (Kategori: #{$category})"; }
        $transactions = $query->orderBy('created_at', 'desc')->get();
        if ($transactions->isEmpty()) { return response()->json(['reply' => "Tidak ada data transaksi untuk *{$periodName}*. 🤔"]); }
        $totalPemasukan = $transactions->where('type', 'pemasukan')->sum('amount');
        $totalPengeluaran = $transactions->where('type', 'pengeluaran')->sum('amount');
        $selisih = $totalPemasukan - $totalPengeluaran;
        $reply = "📊 *Laporan Keuangan {$periodName}*\n\n";
        $reply .= "💰 *Total Pemasukan:*\nRp " . number_format($totalPemasukan, 0, ',', '.') . "\n\n";
        $reply .= "💸 *Total Pengeluaran:*\nRp " . number_format($totalPengeluaran, 0, ',', '.') . "\n\n";
        $reply .= "⚖️ *Selisih (Cash Flow):*\nRp " . number_format($selisih, 0, ',', '.') . "\n";
        $reply .= "-----------------------------------\n*5 Transaksi Terakhir:*\n";
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

    private function getNaturalResponseFromGroq($actionDescription)
    {
        $apiKey = env('GROQ_API_KEY');
        $systemPrompt = "Anda adalah asisten AI yang ramah dan ceria. Tugas Anda adalah membuat kalimat balasan singkat untuk pengguna berdasarkan aksi yang baru saja berhasil dilakukan oleh sistem. Buatlah kalimat yang bervariasi setiap saat. WAJIB selalu gunakan Bahasa Indonesia yang santai dan natural. Jangan pernah menggunakan Bahasa Inggris.";

        try {
            Log::info('Mencoba menghubungi API Groq untuk respons natural...');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama3-8b-8192',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Tolong buatkan kalimat balasan untuk aksi ini: " . $actionDescription],
                ],
                'temperature' => 0.8,
            ]);
            if ($response->successful()) {
                Log::info('Sukses! Respons natural dari Groq diterima.');
                return $response->json()['choices'][0]['message']['content'];
            }
            return "Aksi berhasil dilakukan!";
        } catch (\Exception $e) {
            Log::error('Gagal membuat respons natural: ' . $e->getMessage());
            return "Aksi berhasil dilakukan!";
        }
    }

}
