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
use App\Exports\TransactionsExport;
use App\Exports\InvestmentsExport;
use App\Exports\DebtsExport;
use App\Exports\SavingsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
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
    // === Perintah Pencatatan Data ===
    case 'catat_transaksi':
        return $this->handleTransactionFromAI($phone, $structuredCommand['data']);
    case 'catat_hutang_piutang':
        return $this->handleDebtFromAI($phone, $structuredCommand['data']);
    case 'tandai_lunas':
        return $this->handleMarkAsPaidFromAI($phone, $structuredCommand['data']);
    case 'catat_investasi':
        return $this->handleInvestmentFromAI($phone, $structuredCommand['data']);
    case 'buat_tabungan':
        return $this->handleCreateSavingGoal($phone, $structuredCommand['data']);
    case 'tambah_tabungan':
        return $this->handleAddToSaving($phone, $structuredCommand['data']);
    case 'buat_dana_darurat':
        return $this->handleCreateEmergencyFund($phone, $structuredCommand['data']);
    case 'tambah_dana_darurat':
        return $this->handleAddToEmergencyFund($phone, $structuredCommand['data']);
    case 'pakai_dana_darurat':
        return $this->handleUseEmergencyFund($phone, $structuredCommand['data']);
    case 'set_budget':
        return $this->handleSetBudget($phone, $structuredCommand['data']);
    case 'buat_transaksi_berulang':
        return $this->handleCreateRecurring($phone, $structuredCommand['data']);
    case 'hapus_transaksi_berulang':
        return $this->handleDeleteRecurring($phone, $structuredCommand['data']);
     case 'export_data':
        return $this->handleExportData($phone, $structuredCommand['data']);

    // === Perintah untuk Melihat Data & Laporan ===
    case 'lihat_transaksi_berulang':
        return $this->handleShowRecurring($phone);

    case 'lihat_laporan':
        // SATU FUNGSI UTAMA UNTUK SEMUA JENIS LAPORAN
        return $this->handleShowReport($phone, $structuredCommand['data']);

    // === Perintah Bantuan & Interaksi Lainnya ===
    case 'bantuan':
        return $this->showHelp();
    case 'minta_info_tambahan':
        return response()->json(['reply' => $structuredCommand['data']['pertanyaan']]);

    default:
        return response()->json(['reply' => "Maaf, saya tidak mengerti maksud Anda. Coba katakan dengan cara lain atau ketik /bantuan."]);
}
    }

    // Tambahkan fungsi baru ini di dalam class WebhookController
// Tambahkan 4 fungsi baru ini di dalam class WebhookController
// Tambahkan 2 fungsi baru ini di dalam class WebhookController
// Tambahkan 3 fungsi baru ini di dalam class WebhookController

// Tambahkan 2 fungsi baru ini di dalam class WebhookController

// Ganti fungsi handleExportData yang lama dengan yang ini

// Ganti fungsi handleExportData yang lama dengan yang ini
private function handleExportData($phone, $data)
{
    $dataType = $data['jenis_data'] ?? 'transaksi';
    $period = $data['periode'] ?? 'bulan_ini';

    $fileName = "export_{$dataType}_{$phone}_" . time() . '.xlsx';
    $filePath = "exports/{$fileName}";

    // Nama file yang lebih ramah untuk pengguna
    $userFriendlyFileName = "Laporan " . ucfirst($dataType) . ".xlsx";

    switch ($dataType) {
        case 'transaksi':
            Excel::store(new TransactionsExport($phone, $period), $filePath, 'public');
            break;

        case 'investasi':
            Excel::store(new InvestmentsExport($phone, $period), $filePath, 'public');
            break;

        case 'hutang':
            // Ekspor hutang tidak memerlukan periode
            Excel::store(new DebtsExport($phone), $filePath, 'public');
            break;
        case 'tabungan':
            Excel::store(new SavingsExport($phone), $filePath, 'public');
            break;

        default:
            return response()->json(['reply' => "Maaf, ekspor untuk data '{$dataType}' belum didukung."]);
    }

    $fileUrl = Storage::disk('public')->url($filePath);

    return response()->json([
        'type' => 'file',
        'url' => url($fileUrl),
        'fileName' => $userFriendlyFileName,
        'caption' => "Ini dia laporan {$dataType} Anda."
    ]);
}
// /**
//  * Menghasilkan string CSV untuk data transaksi.
//  */
// private function generateTransactionsCSV($phone, $period)
// {
//     $query = Transaction::where('user_phone', $phone);

//     switch ($period) {
//         case 'bulan_ini':
//             $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
//             break;
//         case 'tahun_ini':
//             $query->whereYear('created_at', now()->year);
//             break;
//         case 'semua':
//             // Tidak perlu filter tambahan
//             break;
//         default:
//             // Default ke bulan ini jika periode tidak dikenal
//             $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
//             break;
//     }

//     $transactions = $query->orderBy('created_at', 'asc')->get();

//     if ($transactions->isEmpty()) {
//         return "";
//     }

//     // Header CSV
//     $csvHeader = ['Tanggal', 'Tipe', 'Jumlah', 'Deskripsi', 'Kategori'];
//     $csvRows = [implode(',', $csvHeader)];

//     // Baris data
//     foreach ($transactions as $trx) {
//         $row = [
//             $trx->created_at->format('Y-m-d'),
//             $trx->type,
//             $trx->amount,
//             // Mengganti koma di deskripsi agar tidak merusak format CSV
//             str_replace(',', ';', $trx->description),
//             $trx->category,
//         ];
//         $csvRows[] = implode(',', $row);
//     }

//     return implode("\n", $csvRows);
// }



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

// private function handleShowBudget($phone)
// {
//     $currentMonth = now()->month;
//     $currentYear = now()->year;

//     $budgets = Budget::where('user_phone', $phone)
//         ->where('month', $currentMonth)
//         ->where('year', $currentYear)
//         ->get();

//     if ($budgets->isEmpty()) {
//         return response()->json(['reply' => "Anda belum mengatur budget untuk bulan ini. Coba atur dengan perintah 'set budget ...'"]);
//     }

//     // Ambil total pengeluaran per kategori untuk bulan ini
//     $spendings = Transaction::where('user_phone', $phone)
//         ->where('type', 'pengeluaran')
//         ->whereMonth('created_at', $currentMonth)
//         ->whereYear('created_at', $currentYear)
//         ->select('category', DB::raw('SUM(amount) as total_spent'))
//         ->groupBy('category')
//         ->pluck('total_spent', 'category');

//     $reply = "📊 *Ringkasan Budget Bulan " . now()->translatedFormat('F Y') . "*\n\n";

//     foreach ($budgets as $budget) {
//         $spent = $spendings[strtolower($budget->category)] ?? 0;
//         $remaining = $budget->amount - $spent;
//         $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
//         $statusEmoji = $percentage >= 100 ? '🔥' : ($percentage >= 80 ? '⚠️' : '✅');

//         $reply .= "{$statusEmoji} *Kategori: {$budget->category}*\n";
//         $reply .= "   - *Terpakai:* Rp " . number_format($spent, 0, ',', '.') . "\n";
//         $reply .= "   - *Sisa:* Rp " . number_format($remaining, 0, ',', '.') . "\n";
//         $reply .= "   - *Budget:* Rp " . number_format($budget->amount, 0, ',', '.') . " (" . number_format($percentage, 1) . "%)\n\n";
//     }

//     return response()->json(['reply' => $reply]);
// }

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

// private function handleShowEmergencyFund($phone)
// {
//     $emergencyFund = Saving::where('user_phone', $phone)->where('is_emergency_fund', true)->first();

//     if (!$emergencyFund) {
//         return response()->json(['reply' => "Anda belum mengatur Dana Darurat. Yuk, atur targetnya dengan perintah 'set dana darurat ...'"]);
//     }

//     $progress = $emergencyFund->target_amount > 0 ? ($emergencyFund->current_amount / $emergencyFund->target_amount) * 100 : 100;

//     $reply = "🚨 *Status Dana Darurat Anda*\n\n";
//     $reply .= "   - *Terkumpul:* Rp " . number_format($emergencyFund->current_amount, 0, ',', '.') . "\n";
//     $reply .= "   - *Target:* Rp " . number_format($emergencyFund->target_amount, 0, ',', '.') . "\n";
//     $reply .= "   - *Progres:* " . number_format($progress, 1) . "% dari target\n\n";
//     $reply .= "_Ingat, dana ini untuk keadaan mendesak. Selalu prioritaskan untuk mengisinya kembali jika terpakai._";

//     return response()->json(['reply' => $reply]);
// }

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

// private function handleShowSavings($phone)
// {
//     $savings = Saving::where('user_phone', $phone)->get();

//     if ($savings->isEmpty()) {
//         return response()->json(['reply' => "Anda belum punya tujuan tabungan. Yuk, buat satu dengan perintah 'buat tabungan...'!"]);
//     }

//     $reply = "🎯 *Progres Tabungan Anda*\n\n";

//     foreach ($savings as $saving) {
//         $progress = ($saving->current_amount / $saving->target_amount) * 100;
//         $statusEmoji = $saving->status === 'completed' ? '✅' : '⏳';

//         $reply .= "{$statusEmoji} *{$saving->goal_name}*\n";
//         $reply .= "   - *Terkumpul:* Rp " . number_format($saving->current_amount, 0, ',', '.') . "\n";
//         $reply .= "   - *Target:* Rp " . number_format($saving->target_amount, 0, ',', '.') . "\n";
//         $reply .= "   - *Progres:* " . number_format($progress, 1) . "%\n\n";
//     }

//     return response()->json(['reply' => $reply]);
// }
// private function handlePortfolio($phone)
// {
//     // Query ini akan mengelompokkan aset dan menghitung totalnya
//     $assets = InvestmentTransaction::where('user_phone', $phone)
//         ->select(
//             'asset_name',
//             'asset_type',
//             // Menjumlahkan quantity 'beli' dan mengurangi quantity 'jual'
//             DB::raw("SUM(CASE WHEN type = 'beli' THEN quantity ELSE -quantity END) as total_quantity"),
//             // Menjumlahkan total_amount 'beli' dan mengurangi total_amount 'jual'
//             DB::raw("SUM(CASE WHEN type = 'beli' THEN total_amount ELSE -total_amount END) as total_capital")
//         )
//         ->groupBy('asset_name', 'asset_type')
//         ->get();

//     // Filter aset yang quantity-nya sudah 0 atau kurang (sudah dijual semua)
//     $portfolio = $assets->filter(function ($asset) {
//         return $asset->total_quantity > 0.00000001; // Toleransi kecil untuk angka desimal
//     });

//     if ($portfolio->isEmpty()) {
//         return response()->json(['reply' => "Anda belum memiliki aset investasi apapun. Coba catat transaksi pertama Anda!"]);
//     }

//     $reply = "💼 *Portofolio Investasi Anda*\n\n";

//     foreach ($portfolio as $asset) {
//         // Hitung rata-rata harga beli
//         $averagePrice = $asset->total_capital / $asset->total_quantity;

//         // Pilih emoji berdasarkan tipe aset
//         $emoji = match(strtolower($asset->asset_type)) {
//             'crypto' => '💎',
//             'saham' => '📈',
//             'emas' => '🪙',
//             'reksadana' => '📄',
//             default => '💰'
//         };

//         $reply .= "{$emoji} *{$asset->asset_name}* ({$asset->asset_type})\n";
//         $reply .= "   - *Jumlah:* " . rtrim(rtrim(number_format($asset->total_quantity, 8), '0'), '.') . " unit\n";
//         $reply .= "   - *Avg. Beli:* Rp " . number_format($averagePrice, 2, ',', '.') . " /unit\n";
//         $reply .= "   - *Modal:* Rp " . number_format($asset->total_capital, 2, ',', '.') . "\n\n";
//     }

//     return response()->json(['reply' => $reply]);
// }

    private function getStructuredCommandFromGroq($userMessage)
    {
        $apiKey = env('GROQ_API_KEY');
        if (empty($apiKey)) {
            Log::error('GROQ_API_KEY tidak ditemukan di .env');
            return ['error' => 'API Key untuk AI belum diatur di server.'];
        }
        // Ganti HANYA bagian systemPrompt di dalam fungsi getStructuredCommandFromGroq Anda

// Ganti SELURUH isi variabel $systemPrompt Anda dengan kode di bawah ini

$systemPrompt = <<<PROMPT
Anda adalah asisten keuangan pribadi yang sangat pintar. Tugas utama Anda adalah mengubah pesan bahasa alami dari pengguna menjadi format JSON yang terstruktur. Selalu balas HANYA dengan format JSON.

Berikut adalah format JSON yang harus Anda gunakan:
1.  **Transaksi:** {"command":"catat_transaksi", "data":{"type":"pengeluaran" atau "pemasukan", "amount":_angka_, "description":"_deskripsi_"}}
2.  **Hutang/Piutang:** {"command":"catat_hutang_piutang", "data":{"type":"hutang" atau "piutang", "amount":_angka_, "person_name":"_nama_", "description":"_deskripsi_"}}
3.  **Lunas:** {"command":"tandai_lunas", "data":{"amount":_angka_, "person_name":"_nama_"}}
4.  **Investasi:** {"command":"catat_investasi", "data":{"type":"beli" atau "jual", "asset_name":"_aset_", "asset_type":"crypto" atau "saham" dll, "quantity":_unit_, "price_per_unit":_harga_}}
5.  **Buat Tabungan:** {"command":"buat_tabungan", "data":{"goal_name":"_tujuan_", "target_amount":_target_}}
6.  **Tambah Tabungan:** {"command":"tambah_tabungan", "data":{"goal_name":"_tujuan_", "amount":_jumlah_}}
7.  **Buat Dana Darurat:** {"command":"buat_dana_darurat", "data":{"target_amount":_target_}}
8.  **Tambah Dana Darurat:** {"command":"tambah_dana_darurat", "data":{"amount":_jumlah_}}
9.  **Pakai Dana Darurat:** {"command":"pakai_dana_darurat", "data":{"amount":_jumlah_, "description":"_alasan_"}}
10. **Set Budget:** {"command":"set_budget", "data":{"category":"_kategori_", "amount":_jumlah_}}
11. **Transaksi Berulang (Buat):** {"command":"buat_transaksi_berulang", "data":{"type":"pemasukan" atau "pengeluaran", "amount":_jumlah_, "description":"_deskripsi_", "day_of_month":_tanggal_}}
12. **Transaksi Berulang (Lihat):** {"command":"lihat_transaksi_berulang"}
13. **Transaksi Berulang (Hapus):** {"command":"hapus_transaksi_berulang", "data":{"description":"_deskripsi_"}}
14. **Laporan (Fleksibel):** {"command":"lihat_laporan", "data":{"jenis_laporan":"utama" atau "transaksi" atau "hutang" atau "investasi" atau "tabungan" atau "budget", "periode":"hari" atau "minggu" atau "bulan"}}
15. **Bantuan:** {"command":"bantuan"}
16. **Info Kurang:** {"command":"minta_info_tambahan", "data":{"pertanyaan":"_pertanyaan_untuk_pengguna_"}}
17. **Error:** {"error":"Maaf, saya kurang paham."}
18. Untuk mengekspor data ke format teks CSV:
{"command":"export_data", "data":{"jenis_data":"transaksi" atau "investasi" atau "hutang", "periode":"bulan_ini" atau "tahun_ini" atau "semua"}}


Contoh-contoh Penting:
User: laporan bulan ini
Output: {"command":"lihat_laporan", "data":{"jenis_laporan":"utama", "periode":"bulan"}}

User: gimana status utang piutangku?
Output: {"command":"lihat_laporan", "data":{"jenis_laporan":"hutang", "periode":null}}

User: cek budget
Output: {"command":"lihat_laporan", "data":{"jenis_laporan":"budget", "periode":null}}

User: /portfolio
Output: {"command":"lihat_laporan", "data":{"jenis_laporan":"investasi", "periode":null}}

User: /tabungan
Output: {"command":"lihat_laporan", "data":{"jenis_laporan":"tabungan", "periode":null}}

User: laporan transaksi minggu kemarin
Output: {"command":"lihat_laporan", "data":{"jenis_laporan":"transaksi", "periode":"minggu"}}

User: aku punya utang
Output: {"command":"minta_info_tambahan", "data":{"pertanyaan":"Tentu, hutang kepada siapa dan berapa jumlahnya?"}}

User: beli saham bbca 1 lot harga 9500
Output: {"command":"catat_investasi", "data":{"type":"beli", "asset_name":"Saham BBCA", "asset_type":"saham", "quantity":100, "price_per_unit":9500}}

User: atur pengeluaran spotify 55rb tiap tgl 28
Output: {"command":"buat_transaksi_berulang", "data":{"type":"pengeluaran", "amount":55000, "description":"Spotify", "day_of_month":28}}

User: export transaksi bulan ini
Output: {"command":"export_data", "data":{"jenis_data":"transaksi", "periode":"bulan_ini"}}

User: kirim semua data investasiku
Output: {"command":"export_data", "data":{"jenis_data":"investasi", "periode":"semua"}}
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

    // private function handleReportFromAI($phone, $message)
    // {
    //     $parts = explode(' ', $message);
    //     $period = 'hari';
    //     foreach($parts as $part) {
    //         if (in_array(strtolower($part), ['minggu', 'mingguan', 'week'])) { $period = 'minggu'; break; }
    //         if (in_array(strtolower($part), ['bulan', 'bulanan', 'month'])) { $period = 'bulan'; break; }
    //     }
    //     $category = null;
    //     if (str_contains($message, '#')) {
    //         $categoryParts = explode('#', $message, 2); $category = trim($categoryParts[1]);
    //     }
    //     $query = Transaction::where('user_phone', $phone);
    //     switch ($period) {
    //         case 'hari': $query->whereDate('created_at', today()); $periodName = 'Hari Ini'; break;
    //         case 'minggu': $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); $periodName = 'Minggu Ini'; break;
    //         case 'bulan': $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]); $periodName = 'Bulan Ini'; break;
    //     }
    //     if ($category) { $query->where('category', $category); $periodName .= " (Kategori: #{$category})"; }
    //     $transactions = $query->orderBy('created_at', 'desc')->get();
    //     if ($transactions->isEmpty()) { return response()->json(['reply' => "Tidak ada data transaksi untuk *{$periodName}*. 🤔"]); }
    //     $totalPemasukan = $transactions->where('type', 'pemasukan')->sum('amount');
    //     $totalPengeluaran = $transactions->where('type', 'pengeluaran')->sum('amount');
    //     $selisih = $totalPemasukan - $totalPengeluaran;
    //     $reply = "📊 *Laporan Keuangan {$periodName}*\n\n";
    //     $reply .= "💰 *Total Pemasukan:*\nRp " . number_format($totalPemasukan, 0, ',', '.') . "\n\n";
    //     $reply .= "💸 *Total Pengeluaran:*\nRp " . number_format($totalPengeluaran, 0, ',', '.') . "\n\n";
    //     $reply .= "⚖️ *Selisih (Cash Flow):*\nRp " . number_format($selisih, 0, ',', '.') . "\n";
    //     $reply .= "-----------------------------------\n*5 Transaksi Terakhir:*\n";
    //     foreach ($transactions->take(5) as $trx) {
    //         $emoji = $trx->type == 'pemasukan' ? '🟢' : '🔴';
    //         $amountFormatted = number_format($trx->amount, 0, ',', '.');
    //         $reply .= "{$emoji} Rp {$amountFormatted} - {$trx->description}\n";
    //     }
    //     return response()->json(['reply' => $reply]);
    // }

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


    // --- FUNGSI-FUNGSI BARU UNTUK LAPORAN KOMPREHENSIF ---

/**
 * Fungsi utama yang menjadi "otak" untuk semua jenis laporan.
 */
private function handleShowReport($phone, $data)
{
    $reportType = $data['jenis_laporan'] ?? 'utama';
    $period = $data['periode'] ?? 'bulan'; // Default periode ke bulan

    switch ($reportType) {
        case 'transaksi':
            return response()->json(['reply' => $this->generateCashFlowReport($phone, $period)]);
        case 'hutang':
            return response()->json(['reply' => $this->generateDebtReport($phone)]);
        case 'investasi':
            return response()->json(['reply' => $this->generatePortfolioReport($phone)]);
        case 'tabungan':
            // Menggabungkan tabungan dan dana darurat
            $savingsReport = $this->generateSavingsReport($phone);
            $emergencyFundReport = $this->generateEmergencyFundReport($phone, false); // false = jangan tampilkan header
            return response()->json(['reply' => $savingsReport . "\n\n" . $emergencyFundReport]);
        case 'budget':
            return response()->json(['reply' => $this->generateBudgetReport($phone)]);
        case 'utama':
        default:
            // Laporan utama akan menampilkan semua ringkasan
            $netWorth = $this->generateNetWorthSummary($phone);
            $cashFlow = $this->generateCashFlowReport($phone, $period);
            $debt = $this->generateDebtReport($phone);
            $portfolio = $this->generatePortfolioReport($phone);

            $fullReport = "📊 *Laporan Keuangan Komprehensif*\n";
            $fullReport .= "-----------------------------------\n";
            $fullReport .= $netWorth . "\n";
            $fullReport .= "-----------------------------------\n";
            $fullReport .= $cashFlow . "\n";
            $fullReport .= "-----------------------------------\n";
            $fullReport .= $debt . "\n";
            $fullReport .= "-----------------------------------\n";
            $fullReport .= $portfolio;

            return response()->json(['reply' => $fullReport]);
    }
}

/**
 * Menghasilkan ringkasan kekayaan bersih (Net Worth).
 */
private function generateNetWorthSummary($phone)
{
    // 1. Aset Lancar (Tabungan + Dana Darurat)
    $liquidAssets = Saving::where('user_phone', $phone)->sum('current_amount');

    // 2. Aset Investasi (Modal)
    $investmentCapital = InvestmentTransaction::where('user_phone', $phone)
        ->select(DB::raw("SUM(CASE WHEN type = 'beli' THEN total_amount ELSE -total_amount END) as total_capital"))
        ->value('total_capital') ?? 0;

    // 3. Piutang (Uang yang dipinjam orang)
    $receivables = Debt::where('user_phone', $phone)->where('type', 'piutang')->where('status', 'belum lunas')->sum('amount');

    // 4. Hutang (Uang yang kita pinjam)
    $debts = Debt::where('user_phone', $phone)->where('type', 'hutang')->where('status', 'belum lunas')->sum('amount');

    $totalAssets = $liquidAssets + $investmentCapital + $receivables;
    $netWorth = $totalAssets - $debts;

    $reply = "👑 *Ringkasan Kekayaan Bersih*\n\n";
    $reply .= "🟢 *Total Aset:* Rp " . number_format($totalAssets, 0, ',', '.') . "\n";
    $reply .= "🔴 *Total Hutang:* Rp " . number_format($debts, 0, ',', '.') . "\n";
    $reply .= "⚖️ *Estimasi Kekayaan Bersih:*\n*Rp " . number_format($netWorth, 0, ',', '.') . "*";

    return $reply;
}

/**
 * Menghasilkan laporan Hutang & Piutang yang masih aktif.
 */
private function generateDebtReport($phone)
{
    $debts = Debt::where('user_phone', $phone)->where('status', 'belum lunas')->orderBy('type')->get();
    if ($debts->isEmpty()) {
        return "⛓️ *Laporan Hutang & Piutang*\n\n✅ Anda tidak memiliki hutang/piutang aktif.";
    }

    $reply = "⛓️ *Laporan Hutang & Piutang Aktif*\n\n";
    $piutangList = "";
    $hutangList = "";

    foreach ($debts as $debt) {
        $amount = number_format($debt->amount, 0, ',', '.');
        if ($debt->type == 'piutang') {
            $piutangList .= "   - *{$debt->person_name}:* Rp {$amount}\n";
        } else {
            $hutangList .= "   - *Kepada {$debt->person_name}:* Rp {$amount}\n";
        }
    }

    if (!empty($piutangList)) $reply .= "✅ *Uang Anda di Luar (Piutang):*\n" . $piutangList;
    if (!empty($hutangList)) $reply .= "\n🔴 *Kewajiban Anda (Hutang):*\n" . $hutangList;

    return $reply;
}

/**
 * Versi baru untuk laporan transaksi (Arus Kas).
 */
private function generateCashFlowReport($phone, $period) {
    // Logika ini sama dengan handleReportFromAI yang lama, kita pindahkan ke sini
    $query = Transaction::where('user_phone', $phone);
    $periodName = 'Bulan Ini'; // Default
    switch ($period) {
        case 'hari': $query->whereDate('created_at', today()); $periodName = 'Hari Ini'; break;
        case 'minggu': $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]); $periodName = 'Minggu Ini'; break;
        case 'bulan': $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]); $periodName = 'Bulan Ini'; break;
    }
    $transactions = $query->get();
    if ($transactions->isEmpty()) { return "💸 *Laporan Arus Kas {$periodName}*\n\nTidak ada transaksi."; }

    $totalPemasukan = $transactions->where('type', 'pemasukan')->sum('amount');
    $totalPengeluaran = $transactions->where('type', 'pengeluaran')->sum('amount');

    $reply = "💸 *Laporan Arus Kas {$periodName}*\n\n";
    $reply .= "   - *Pemasukan:* Rp " . number_format($totalPemasukan, 0, ',', '.') . "\n";
    $reply .= "   - *Pengeluaran:* Rp " . number_format($totalPengeluaran, 0, ',', '.');
    return $reply;
}

/**
 * Versi baru untuk laporan investasi (Portofolio).
 */
private function generatePortfolioReport($phone) {
    // Logika ini sama dengan handlePortfolio yang lama
    $assets = InvestmentTransaction::where('user_phone', $phone)
        ->select('asset_name', 'asset_type', DB::raw("SUM(CASE WHEN type = 'beli' THEN quantity ELSE -quantity END) as total_quantity"), DB::raw("SUM(CASE WHEN type = 'beli' THEN total_amount ELSE -total_amount END) as total_capital"))
        ->groupBy('asset_name', 'asset_type')->get();
    $portfolio = $assets->filter(function ($asset) { return $asset->total_quantity > 0.00000001; });
    if ($portfolio->isEmpty()) { return "📈 *Portofolio Investasi*\n\nAnda belum memiliki aset investasi."; }

    $reply = "📈 *Portofolio Investasi*\n";
    foreach ($portfolio as $asset) {
        $averagePrice = $asset->total_capital / $asset->total_quantity;
        $reply .= "\n   - *{$asset->asset_name} ({$asset->asset_type})*\n";
        $reply .= "     *Jumlah:* " . rtrim(rtrim(number_format($asset->total_quantity, 8), '0'), '.') . " unit\n";
        $reply .= "     *Modal:* Rp " . number_format($asset->total_capital, 0, ',', '.');
    }
    return $reply;
}

// Anda juga bisa membuat fungsi generateSavingsReport, generateEmergencyFundReport, dan generateBudgetReport
// dengan memindahkan logika dari fungsi handle... yang lama ke sini agar lebih rapi.

}
