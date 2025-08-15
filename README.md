### **Dokumen Desain Perangkat Lunak (SDD): FTR-WA-FINANCE**

**Versi: 1.0**
**Tanggal: 15 Agustus 2025**

### 1. Pendahuluan

#### 1.1. Tujuan Dokumen
Dokumen ini bertujuan untuk memberikan deskripsi teknis yang komprehensif mengenai desain dan arsitektur sistem **FTR-WA-FINANCE**. Dokumen ini akan menjadi panduan bagi pengembang untuk memahami alur kerja, struktur data, komponen-komponen utama, dan bagaimana semuanya saling berinteraksi.

#### 1.2. Ruang Lingkup Proyek
**FTR-WA-FINANCE** adalah sebuah sistem manajemen keuangan pribadi yang berinteraksi dengan pengguna melalui antarmuka percakapan (conversational UI) via WhatsApp.

Fungsionalitas utama sistem ini meliputi:
*   **Pencatatan Transaksi**: Pengguna dapat mencatat pemasukan dan pengeluaran.
*   **Manajemen Utang**: Pengguna dapat mencatat dan mengelola utang-piutang.
*   **Pelaporan**: Sistem dapat mengirimkan laporan keuangan harian secara otomatis.
*   **Interaksi via WhatsApp**: Semua interaksi utama dilakukan melalui perintah teks di WhatsApp.

#### 1.3. Teknologi yang Digunakan
*   **Backend Framework**: Laravel (PHP)
*   **Database**: SQLite
*   **WhatsApp Connector**: Node.js (kemungkinan besar menggunakan library seperti `Baileys` atau `whatsapp-web.js` berdasarkan adanya direktori `wa-connector` dan `auth_info_baileys`).
*   **Frontend**: Laravel Blade (untuk tampilan web minimalis, jika ada).
*   **Web Server**: Lingkungan server PHP pada umumnya (misal: Nginx atau Apache).

---

### 2. Arsitektur Sistem

Sistem ini mengadopsi arsitektur berbasis layanan yang terpisah (decoupled) yang terdiri dari dua komponen utama:

1.  **Aplikasi Web (Backend Laravel)**: Bertindak sebagai otak dari sistem. Komponen ini menangani semua logika bisnis, validasi data, interaksi database, dan menyediakan Webhook API.
2.  **Konektor WhatsApp (Node.js)**: Bertindak sebagai jembatan antara pengguna di WhatsApp dan backend Laravel. Komponen ini bertanggung jawab untuk menerima pesan dari WhatsApp dan meneruskannya ke backend, serta mengirimkan balasan dari backend ke pengguna.

#### Alur Kerja Umum:
1.  Pengguna mengirimkan pesan (perintah) ke nomor WhatsApp bot.
2.  **Konektor WhatsApp (Node.js)** menerima pesan tersebut.
3.  Konektor membuat request HTTP POST ke endpoint `/api/webhook` di **Backend Laravel**, dengan menyertakan isi pesan, nomor pengirim, dan informasi relevan lainnya.
4.  **Backend Laravel** menerima request melalui `WebhookController`.
5.  Middleware `VerifyBotSecret` akan memverifikasi token rahasia untuk memastikan request datang dari Konektor WhatsApp yang sah.
6.  `WebhookController` memproses pesan, menjalankan logika bisnis (misalnya, membuat entri transaksi baru di database).
7.  `WebhookController` menghasilkan pesan balasan (misalnya, "Transaksi berhasil dicatat.").
8.  Backend mengembalikan balasan dalam format JSON ke Konektor WhatsApp.
9.  **Konektor WhatsApp** menerima balasan dan mengirimkannya sebagai pesan WhatsApp ke pengguna.

![Diagram Arsitektur Sederhana](httpsg://i.imgur.com/9yZ9oXk.png)
*(Ini adalah representasi konseptual dari alur kerja)*

---

### 3. Desain Data

Data sistem disimpan dalam database SQLite. Struktur data utama didefinisikan oleh model dan file migrasi Laravel.

#### 3.1. Tabel-tabel Utama

*   **`users`** (dari `..._create_users_table.php`)
    *   `id`: Primary Key
    *   `name`: Nama pengguna.
    *   `whatsapp_number`: Nomor WhatsApp unik untuk identifikasi.
    *   `email`, `password`: Kolom standar Laravel, mungkin tidak digunakan secara aktif dalam interaksi bot.
    *   `timestamps`: `created_at`, `updated_at`.

*   **`transactions`** (dari `..._create_transactions_table.php`)
    *   `id`: Primary Key
    *   `user_id`: Foreign key ke tabel `users`.
    *   `type`: Enum/String ('income', 'expense').
    *   `amount`: Jumlah uang (Decimal/Integer).
    *   `category`: Kategori transaksi (misal: 'makanan', 'transportasi', 'gaji').
    *   `description`: Deskripsi singkat transaksi.
    *   `transaction_date`: Tanggal transaksi.
    *   `timestamps`: `created_at`, `updated_at`.

*   **`debts`** (dari `..._create_debts_table.php`)
    *   `id`: Primary Key
    *   `user_id`: Foreign key ke tabel `users` (pemilik catatan).
    *   `type`: Enum/String ('receivable' / utang orang lain, 'payable' / utang saya).
    *   `person_name`: Nama orang yang berhutang/memberi utang.
    *   `amount`: Jumlah utang.
    *   `due_date`: Tanggal jatuh tempo.
    *   `status`: Enum/String ('unpaid', 'paid').
    *   `timestamps`: `created_at`, `updated_at`.

---

### 4. Desain Komponen

#### 4.1. Backend (Aplikasi Laravel)

*   **Routes (`routes/web.php`)**:
    *   `POST /api/webhook`: Endpoint utama yang menerima data dari Konektor WhatsApp. Dilindungi oleh middleware `VerifyBotSecret`.

*   **Controllers (`app/Http/Controllers/Api/WebhookController.php`)**:
    *   Berisi logika utama untuk mem-parsing pesan masuk dari pengguna.
    *   Akan ada metode-metode internal untuk menangani berbagai perintah seperti `handleNewTransaction()`, `handleNewDebt()`, `handleCheckBalance()`, dll.
    *   Berinteraksi dengan Model untuk memanipulasi data.

*   **Middleware (`app/Http/Middleware/VerifyBotSecret.php`)**:
    *   Sebuah middleware krusial untuk keamanan.
    *   Memeriksa `Authorization` header atau parameter khusus dalam request untuk memastikan hanya Konektor WhatsApp yang dapat mengakses webhook. Kunci rahasia (secret) disimpan di file `.env`.

*   **Models (`app/Models/*.php`)**:
    *   `User.php`, `Transaction.php`, `Debt.php`: Merepresentasikan tabel database dan mendefinisikan relasi antar tabel (misal: User `hasMany` Transactions).

*   **Scheduled Commands (`app/Console/Commands/SendDailyReport.php`)**:
    *   Sebuah tugas terjadwal (cron job) yang dieksekusi setiap hari.
    *   Logikanya adalah mengambil ringkasan transaksi harian untuk setiap pengguna dan mengirimkannya melalui Konektor WhatsApp.

#### 4.2. Konektor WhatsApp (`wa-connector/`)

*   **`index.js`**:
    *   File utama dari layanan Node.js.
    *   Menginisialisasi client WhatsApp (misal: Baileys).
    *   Menangani proses autentikasi, menyimpan sesi di `auth_info_baileys/`.
    *   Mendengarkan event `messages.upsert` (atau yang setara) untuk setiap pesan baru yang masuk.
    *   Saat pesan diterima, ia akan mem-format data dan mengirimkannya ke API Laravel menggunakan `axios` atau `node-fetch`.
    *   Menunggu respons dari Laravel dan mengirimkan kembali hasilnya ke pengguna.

---

### 5. Desain Antarmuka Pengguna (UI/UX)

Antarmuka utama adalah **percakapan teks di WhatsApp**. Interaksi didasarkan pada format perintah yang spesifik.

**Contoh Alur Perintah:**

*   **Mencatat Pengeluaran:**
    *   User: `keluar 50000 beli kopi`
    *   Bot: `✅ Pengeluaran "beli kopi" sebesar Rp 50.000 berhasil dicatat.`

*   **Mencatat Pemasukan:**
    *   User: `masuk 2500000 gaji bulanan`
    *   Bot: `✅ Pemasukan "gaji bulanan" sebesar Rp 2.500.000 berhasil dicatat.`

*   **Mencatat Utang:**
    *   User: `utang budi 100000 pinjaman`
    *   Bot: `✅ Utang kepada Budi sebesar Rp 100.000 berhasil dicatat.`

*   **Melihat Laporan:**
    *   User: `laporan hari ini`
    *   Bot: (Mengirim ringkasan pemasukan dan pengeluaran hari ini).

---

### 6. Keamanan

*   **Proteksi Webhook**: `VerifyBotSecret.php` mencegah akses tidak sah ke API.
*   **Manajemen Kredensial**: Semua kunci API, secret token, dan konfigurasi database disimpan dalam file `.env` dan tidak boleh dimasukkan ke dalam sistem kontrol versi (Git).
*   **Identifikasi Pengguna**: Sistem mengidentifikasi pengguna berdasarkan nomor WhatsApp pengirim. Pengguna baru mungkin perlu mendaftar terlebih dahulu dengan perintah seperti `daftar <nama>`.
