<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\InvestmentTransaction;
use App\Models\Party;
use App\Models\RecurringTransaction;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $phone = $request->input('phone');
        $message = trim($request->input('message'));

        // Cek apakah ini pesan untuk menautkan akun
        if (str_starts_with(strtoupper($message), 'FINBOT-')) {
            return $this->linkAccount($phone, $message);
        }

        // Cari user berdasarkan nomor WA yang sudah tertaut
        $user = User::where('whatsapp_phone', $phone)->first();

        // Jika user tidak ditemukan, kirim pesan untuk mendaftar
        if (!$user) {
            $webUrl = config('app.url');
            return response()->json(['reply' => "Nomor Anda belum tertaut ke akun manapun. Silakan daftar di {$webUrl}, lalu tautkan nomor WhatsApp Anda dari halaman profil."]);
        }

        $structuredCommand = $this->getStructuredCommandFromGroq($message);

        if (isset($structuredCommand['error'])) {
            return response()->json(['reply' => $structuredCommand['error']]);
        }
        if (!isset($structuredCommand['command'])) {
            return response()->json(['reply' => 'Maaf, terjadi sedikit kebingungan. Coba lagi ya.']);
        }

        switch ($structuredCommand['command']) {
            case 'catat_transaksi':
                return $this->handleTransactionFromAI($user, $structuredCommand['data']);
            case 'catat_hutang_piutang':
                return $this->handleDebtFromAI($user, $structuredCommand['data']);
            case 'tandai_lunas':
                return $this->handleMarkAsPaidFromAI($user, $structuredCommand['data']);
            case 'catat_investasi':
                return $this->handleInvestmentFromAI($user, $structuredCommand['data']);
            case 'buat_tabungan':
                return $this->handleCreateSavingGoal($user, $structuredCommand['data']);
            case 'tambah_tabungan':
                return $this->handleAddToSaving($user, $structuredCommand['data']);
            case 'buat_dana_darurat':
                return $this->handleCreateEmergencyFund($user, $structuredCommand['data']);
            case 'tambah_dana_darurat':
                return $this->handleAddToEmergencyFund($user, $structuredCommand['data']);
            case 'pakai_dana_darurat':
                return $this->handleUseEmergencyFund($user, $structuredCommand['data']);
            case 'set_budget':
                return $this->handleSetBudget($user, $structuredCommand['data']);
            case 'buat_transaksi_berulang':
                return $this->handleCreateRecurring($user, $structuredCommand['data']);
            case 'hapus_transaksi_berulang':
                return $this->handleDeleteRecurring($user, $structuredCommand['data']);
            case 'export_data':
                return response()->json(['reply' => 'Maaf, fitur ekspor data hanya tersedia melalui aplikasi web.']);
            case 'lihat_laporan':
                return $this->handleShowReport($user, $structuredCommand['data']);
            case 'lihat_transaksi_berulang':
                 return $this->handleShowRecurring($user);
            case 'bantuan':
                return $this->showHelp();
            case 'minta_info_tambahan':
                return response()->json(['reply' => $structuredCommand['data']['pertanyaan']]);
            default:
                return response()->json(['reply' => 'Maaf, saya tidak mengerti maksud Anda.']);
        }
    }

    private function linkAccount($phone, $code)
    {
        $userToLink = User::where('whatsapp_link_code', strtoupper($code))->first();
        if ($userToLink) {
            $userToLink->whatsapp_phone = $phone;
            $userToLink->whatsapp_link_code = null;
            $userToLink->save();
            return response()->json(['reply' => "✅ Berhasil! Akun Anda ('{$userToLink->name}') telah tertaut. Sekarang Anda bisa mulai menggunakan bot."]);
        }
        return response()->json(['reply' => '❌ Kode penautan tidak valid.']);
    }

    private function handleTransactionFromAI(User $user, $data)
    {
        $user->transactions()->create([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'user_phone' => $user->whatsapp_phone,
        ]);
        $actionDescription = "Berhasil mencatat {$data['type']} Rp " . number_format($data['amount']) . " untuk '{$data['description']}'.";
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleDebtFromAI(User $user, $data)
    {
        $party = $user->parties()->firstOrCreate(
            ['name' => $data['person_name']],
            ['type' => 'Perorangan']
        );
        $user->debts()->create([
            'party_id' => $party->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'user_phone' => $user->whatsapp_phone,
        ]);
        $action = $data['type'] === 'hutang' ? 'berhutang' : 'memberi piutang';
        $actionDescription = "Berhasil mencatat {$action} Rp " . number_format($data['amount']) . " kepada {$data['person_name']} untuk '{$data['description']}'.";
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleMarkAsPaidFromAI(User $user, $data)
    {
        $party = $user->parties()->where('name', 'like', '%' . $data['person_name'] . '%')->first();
        if (!$party) {
            return response()->json(['reply' => "Pihak bernama '{$data['person_name']}' tidak ditemukan."]);
        }
        $debt = $user->debts()->where('party_id', $party->id)
            ->where('amount', $data['amount'])
            ->where('status', 'belum lunas')
            ->first();

        if ($debt) {
            $debt->status = 'lunas';
            $debt->save();
            $actionDescription = "Berhasil menandai lunas untuk {$debt->type} kepada {$party->name} sebesar Rp " . number_format($debt->amount);
            return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
        }
        return response()->json(['reply' => "❌ Tidak ditemukan catatan hutang/piutang yang cocok untuk *{$data['person_name']}* sebesar *{$data['amount']}*."]);
    }
    
    private function handleInvestmentFromAI(User $user, $data)
    {
        $user->investmentTransactions()->create([
            'type' => $data['type'],
            'asset_name' => $data['asset_name'],
            'asset_type' => $data['asset_type'],
            'quantity' => $data['quantity'],
            'price_per_unit' => $data['price_per_unit'],
            'total_amount' => $data['quantity'] * $data['price_per_unit'],
            'user_phone' => $user->whatsapp_phone,
        ]);
        $actionDescription = "Berhasil mencatat {$data['type']} aset {$data['asset_type']} '{$data['asset_name']}'.";
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleCreateSavingGoal(User $user, $data)
    {
        if ($user->savings()->where('goal_name', 'like', $data['goal_name'])->exists()) {
            return response()->json(['reply' => "⚠️ Anda sudah punya tujuan tabungan '{$data['goal_name']}'. Gunakan nama lain ya."]);
        }
        $user->savings()->create([
            'goal_name' => $data['goal_name'],
            'target_amount' => $data['target_amount'],
            'user_phone' => $user->whatsapp_phone,
        ]);
        $actionDescription = "Berhasil membuat tujuan tabungan '{$data['goal_name']}' dengan target Rp " . number_format($data['target_amount']);
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleAddToSaving(User $user, $data)
    {
        $savingGoal = $user->savings()->where('goal_name', 'like', '%' . $data['goal_name'] . '%')->first();
        if (!$savingGoal) {
            return response()->json(['reply' => "❌ Tidak ditemukan tujuan tabungan '{$data['goal_name']}'."]);
        }
        if ($savingGoal->status === 'completed') {
            return response()->json(['reply' => "🎉 Selamat! Tabungan '{$savingGoal->goal_name}' Anda sudah tercapai."]);
        }
        $user->transactions()->create([
            'type' => 'pengeluaran', 'amount' => $data['amount'],
            'description' => "Menabung untuk {$savingGoal->goal_name}", 'category' => 'tabungan',
            'user_phone' => $user->whatsapp_phone,
        ]);
        $savingGoal->current_amount += $data['amount'];
        if ($savingGoal->current_amount >= $savingGoal->target_amount && !$savingGoal->is_emergency_fund) {
            $savingGoal->status = 'completed';
        }
        $savingGoal->save();
        $actionDescription = "Berhasil menambah Rp " . number_format($data['amount']) . " ke tabungan '{$savingGoal->goal_name}'.";
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleCreateEmergencyFund(User $user, $data)
    {
        $user->savings()->updateOrCreate(
            ['is_emergency_fund' => true],
            [
                'goal_name' => 'Dana Darurat',
                'target_amount' => $data['target_amount'],
                'user_phone' => $user->whatsapp_phone,
            ]
        );
        $actionDescription = 'Berhasil mengatur target Dana Darurat Anda menjadi Rp ' . number_format($data['target_amount']);
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleAddToEmergencyFund(User $user, $data)
    {
        $emergencyFund = $user->savings()->where('is_emergency_fund', true)->first();
        if (!$emergencyFund) {
            return response()->json(['reply' => "Anda belum mengatur target Dana Darurat."]);
        }
        $user->transactions()->create([
            'type' => 'pengeluaran', 'amount' => $data['amount'],
            'description' => 'Menabung untuk Dana Darurat', 'category' => 'dana darurat',
            'user_phone' => $user->whatsapp_phone,
        ]);
        $emergencyFund->current_amount += $data['amount'];
        $emergencyFund->save();
        $actionDescription = 'Berhasil menambah Rp ' . number_format($data['amount']) . ' ke Dana Darurat Anda.';
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleUseEmergencyFund(User $user, $data)
    {
        $emergencyFund = $user->savings()->where('is_emergency_fund', true)->first();
        if (!$emergencyFund) {
            return response()->json(['reply' => 'Anda belum memiliki Dana Darurat untuk dipakai.']);
        }
        if ($emergencyFund->current_amount < $data['amount']) {
            return response()->json(['reply' => 'Dana Darurat Anda tidak mencukupi. Saldo saat ini: Rp ' . number_format($emergencyFund->current_amount)]);
        }
        $emergencyFund->current_amount -= $data['amount'];
        $emergencyFund->save();
        $user->transactions()->create([
            'type' => 'pemasukan', 'amount' => $data['amount'],
            'description' => "Pakai Dana Darurat: {$data['description']}", 'category' => 'dana darurat',
            'user_phone' => $user->whatsapp_phone,
        ]);
        $actionDescription = "Berhasil memakai Dana Darurat Rp " . number_format($data['amount']) . " untuk '{$data['description']}'.";
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleSetBudget(User $user, $data)
    {
        $user->budgets()->updateOrCreate(
            ['category' => strtolower($data['category']), 'month' => now()->month, 'year' => now()->year],
            ['amount' => $data['amount'], 'user_phone' => $user->whatsapp_phone]
        );
        $actionDescription = "Berhasil mengatur budget '{$data['category']}' bulan ini sebesar Rp " . number_format($data['amount']);
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }
    
    private function handleCreateRecurring(User $user, $data)
    {
        $user->recurringTransactions()->create([
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'day_of_month' => $data['day_of_month'],
            'user_phone' => $user->whatsapp_phone,
        ]);
        $actionDescription = "Berhasil membuat aturan transaksi berulang '{$data['description']}' setiap tanggal {$data['day_of_month']}.";
        return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
    }

    private function handleDeleteRecurring(User $user, $data)
    {
        $deleted = $user->recurringTransactions()
            ->where('description', 'like', '%' . $data['description'] . '%')
            ->delete();
        if ($deleted) {
            $actionDescription = "Berhasil menghapus aturan transaksi berulang '{$data['description']}'.";
            return response()->json(['reply' => $this->getNaturalResponseFromGroq($actionDescription)]);
        }
        return response()->json(['reply' => "Tidak ditemukan aturan berulang '{$data['description']}'."]);
    }
    
    // --- FUNGSI LAPORAN, TIDAK PERLU BERUBAH BANYAK ---
    private function handleShowReport(User $user, $data) { /* ... pindahkan logika generateReport ke sini ... */ }
    private function handleShowRecurring(User $user) { /* ... pindahkan logika showRecurring ke sini ... */ }
    
    // --- FUNGSI HELPER, TIDAK PERLU DIUBAH SAMA SEKALI ---
    private function getStructuredCommandFromGroq($userMessage) { /* ... kode Anda sudah benar ... */ }
    private function getNaturalResponseFromGroq($actionDescription) { /* ... kode Anda sudah benar ... */ }
    private function showHelp() { /* ... kode Anda sudah benar ... */ }
}