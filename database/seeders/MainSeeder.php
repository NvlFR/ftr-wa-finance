<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Party;
use Illuminate\Support\Facades\Hash;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat satu user utama untuk testing
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Membuat beberapa pihak yang terhubung dengan user tersebut
        $party1 = $user->parties()->create(['name' => 'Budi', 'type' => 'Perorangan']);
        $party2 = $user->parties()->create(['name' => 'Toko Kelontong', 'type' => 'Toko']);

        // Membuat contoh data transaksi
        $user->transactions()->create([
            'type' => 'pemasukan', 'amount' => 5000000, 'description' => 'Gaji Awal', 'category' => 'gaji'
        ]);
        $user->transactions()->create([
            'type' => 'pengeluaran', 'amount' => 50000, 'description' => 'Beli Kopi', 'category' => 'jajan'
        ]);

        // Membuat contoh data hutang yang terhubung dengan pihak
        $user->debts()->create([
            'party_id' => $party1->id,
            'type' => 'piutang',
            'amount' => 100000,
            'description' => 'Pinjam untuk ongkos'
        ]);

        $this->command->info('Database seeding completed successfully!');
    }
}
