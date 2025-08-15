<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\WebhookController;

class SendDailyReport extends Command
{
    protected $signature = 'app:send-daily-report';
    protected $description = 'Generate and send daily financial reports to all active users.';

    public function handle()
    {
        $this->info('Starting to send daily reports...');

        // Ambil semua nomor unik yang bertransaksi hari ini
        $users = Transaction::whereDate('created_at', today())
            ->distinct()
            ->pluck('user_phone');

        if ($users->isEmpty()) {
            $this->info('No active users today. No reports sent.');
            return;
        }

        $webhookController = new WebhookController();

        foreach ($users as $userPhone) {
            // Gunakan kembali logika laporan dari WebhookController
            // Kita "memalsukan" request untuk mendapatkan balasan JSON
            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->merge(['phone' => $userPhone]);
            $reportResponse = $webhookController->handleReport($userPhone, '/laporan hari');
            $reportContent = json_decode($reportResponse->getContent(), true);
            $message = $reportContent['reply'];

            // Kirim pesan ke Node.js untuk diteruskan ke WhatsApp
            // Perhatikan port 3000, ini untuk server di Node.js nanti
            Http::post('http://localhost:3000/send-message', [
                'recipient' => $userPhone,
                'message' => "🔔 *Laporan Otomatis Harian Anda*\n\n" . $message,
                // 'secret' => env('BOT_API_SECRET'), // Kirim secret juga
            ]);

            $this->info("Report sent to {$userPhone}");
        }
        $this->info('All reports sent successfully.');
    }
}
