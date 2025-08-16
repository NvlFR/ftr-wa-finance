<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTransaction; // <-- TAMBAHKAN INI
use App\Models\Transaction;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-recurring-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
         $today = now()->day;
    $this->info("Mengecek transaksi berulang untuk tanggal: {$today}...");

    $tasks = RecurringTransaction::where('day_of_month', $today)
        ->where('is_active', true)
        ->get();

    if ($tasks->isEmpty()) {
        $this->info('Tidak ada transaksi berulang untuk hari ini.');
        return;
    }

    foreach ($tasks as $task) {
        // Buat transaksi baru di tabel transactions
        Transaction::create([
            'user_phone' => $task->user_phone,
            'type' => $task->type,
            'amount' => $task->amount,
            'description' => $task->description . ' (Otomatis)',
            'category' => $task->category,
        ]);
        $this->info("Transaksi '{$task->description}' untuk {$task->user_phone} berhasil dicatat.");
    }

    $this->info('Semua transaksi berulang berhasil diproses.');
    }
}
