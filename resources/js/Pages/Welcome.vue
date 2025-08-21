<template>
  <div class="welcome-container">
    <!-- Header Section -->
    <header class="header">
      <div class="container">
        <div class="logo">
          <h1>FTR-WA-FINANCE</h1>
        </div>
        <nav class="navigation">
          <a href="#features">Fitur</a>
          <a href="#how-it-works">Cara Kerja</a>
          <a href="#contact">Kontak</a>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <h2>Kelola Keuangan Pribadi dengan Mudah melalui WhatsApp</h2>
          <p>FTR-WA-FINANCE membantu Anda mencatat transaksi, mengelola utang, dan mendapatkan laporan keuangan langsung melalui WhatsApp</p>
          <div class="cta-buttons">
            <button class="btn-primary" @click="startChat">Mulai Sekarang</button>
            <button class="btn-secondary" @click="scrollToFeatures">Pelajari Lebih Lanjut</button>
          </div>
        </div>
        <div class="hero-image">
          <img src="" alt="WhatsApp Finance Chat Illustration">
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
      <div class="container">
        <h2>Fitur Unggulan</h2>
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-exchange-alt"></i>
            </div>
            <h3>Pencatatan Transaksi</h3>
            <p>Catat pemasukan dan pengeluaran dengan mudah melalui WhatsApp</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-hand-holding-usd"></i>
            </div>
            <h3>Manajemen Utang</h3>
            <p>Kelola utang piutang dengan orang lain secara terorganisir</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3>Laporan Keuangan</h3>
            <p>Dapatkan laporan harian otomatis langsung di WhatsApp Anda</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
      <div class="container">
        <h2>Bagaimana Cara Kerjanya?</h2>
        <div class="steps">
          <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
              <h3>Kirim Pesan ke WhatsApp Bot</h3>
              <p>Kirim perintah seperti "keluar 50000 beli kopi" ke nomor bot kami</p>
            </div>
          </div>
          <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
              <h3>Sistem Memproses Permintaan</h3>
              <p>Backend kami akan memproses dan menyimpan data transaksi Anda</p>
            </div>
          </div>
          <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
              <h3>Dapatkan Konfirmasi</h3>
              <p>Terima notifikasi konfirmasi bahwa transaksi telah berhasil dicatat</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Example Commands Section -->
    <section class="commands">
      <div class="container">
        <h2>Contoh Perintah yang Dapat Digunakan</h2>
        <div class="command-examples">
          <div class="command-card">
            <div class="command-text">masuk 2500000 gaji bulanan</div>
            <div class="command-desc">Mencatat pemasukan sebesar Rp 2.500.000 dengan deskripsi "gaji bulanan"</div>
          </div>
          <div class="command-card">
            <div class="command-text">keluar 50000 beli kopi</div>
            <div class="command-desc">Mencatat pengeluaran sebesar Rp 50.000 dengan deskripsi "beli kopi"</div>
          </div>
          <div class="command-card">
            <div class="command-text">utang budi 100000 pinjaman</div>
            <div class="command-desc">Mencatat utang kepada Budi sebesar Rp 100.000 dengan deskripsi "pinjaman"</div>
          </div>
          <div class="command-card">
            <div class="command-text">laporan hari ini</div>
            <div class="command-desc">Melihat ringkasan pemasukan dan pengeluaran hari ini</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
      <div class="container">
        <h2>Siap Mengelola Keuangan dengan Lebih Baik?</h2>
        <p>Mulai gunakan FTR-WA-FINANCE sekarang dan rasakan kemudahannya</p>
        <button class="btn-primary" @click="startChat">Mulai Chat via WhatsApp</button>
      </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>FTR-WA-FINANCE</h3>
            <p>Solusi manajemen keuangan pribadi melalui WhatsApp</p>
          </div>
          <div class="footer-section">
            <h3>Kontak</h3>
            <p>Email: support@ftr-wa-finance.com</p>
            <p>WhatsApp: +62 812-3456-7890</p>
          </div>
          <div class="footer-section">
            <h3>Teknologi</h3>
            <p>Dibangun dengan Laravel, Node.js, dan Vue.js</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 FTR-WA-FINANCE. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'WelcomePage',
  methods: {
    startChat() {
      // Redirect ke WhatsApp dengan nomor bot
      window.open('https://wa.me/6281234567890?text=Halo,%20saya%20ingin%20menggunakan%20FTR-WA-FINANCE', '_blank');
    },
    scrollToFeatures() {
      const featuresSection = document.getElementById('features');
      if (featuresSection) {
        featuresSection.scrollIntoView({ behavior: 'smooth' });
      }
    }
  }
}
</script>

<style scoped>
/* Global Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #333;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Header Styles */
.header {
  background-color: #fff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: fixed;
  width: 100%;
  top: 0;
  z-index: 1000;
}

.header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
}

.logo h1 {
  color: #25D366; /* WhatsApp green */
  font-size: 1.5rem;
}

.navigation a {
  margin-left: 20px;
  text-decoration: none;
  color: #333;
  font-weight: 500;
}

.navigation a:hover {
  color: #25D366;
}

/* Hero Section */
.hero {
  padding: 120px 0 80px;
  background: linear-gradient(135deg, #f5f9ff 0%, #e6f2ff 100%);
}

.hero .container {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.hero-content {
  flex: 1;
  max-width: 500px;
}

.hero-content h2 {
  font-size: 2.5rem;
  margin-bottom: 20px;
  color: #1a1a1a;
}

.hero-content p {
  font-size: 1.1rem;
  margin-bottom: 30px;
  color: #4d4d4d;
}

.hero-image {
  flex: 1;
  text-align: center;
}

.hero-image img {
  max-width: 100%;
  height: auto;
}

.cta-buttons {
  display: flex;
  gap: 15px;
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background-color: #25D366;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #128C7E;
}

.btn-secondary {
  background-color: transparent;
  color: #25D366;
  border: 2px solid #25D366;
}

.btn-secondary:hover {
  background-color: #25D366;
  color: white;
}

/* Features Section */
.features {
  padding: 80px 0;
  background-color: #fff;
}

.features h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 50px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
}

.feature-card {
  text-align: center;
  padding: 30px 20px;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
}

.feature-icon {
  width: 70px;
  height: 70px;
  background-color: #e6f7ff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  color: #25D366;
  font-size: 1.8rem;
}

.feature-card h3 {
  margin-bottom: 15px;
  font-size: 1.3rem;
}

/* How It Works Section */
.how-it-works {
  padding: 80px 0;
  background-color: #f9f9f9;
}

.how-it-works h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 50px;
}

.steps {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.step {
  display: flex;
  align-items: center;
  gap: 20px;
}

.step-number {
  width: 50px;
  height: 50px;
  background-color: #25D366;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  flex-shrink: 0;
}

.step-content h3 {
  margin-bottom: 10px;
  font-size: 1.3rem;
}

/* Commands Section */
.commands {
  padding: 80px 0;
  background-color: #fff;
}

.commands h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 50px;
}

.command-examples {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.command-card {
  background-color: #f5f9ff;
  padding: 20px;
  border-radius: 10px;
  border-left: 4px solid #25D366;
}

.command-text {
  font-family: monospace;
  font-size: 1.1rem;
  background-color: #e6f2ff;
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 10px;
}

.command-desc {
  color: #666;
}

/* CTA Section */
.cta {
  padding: 80px 0;
  background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
  color: white;
  text-align: center;
}

.cta h2 {
  font-size: 2rem;
  margin-bottom: 20px;
}

.cta p {
  margin-bottom: 30px;
  font-size: 1.1rem;
}

/* Footer */
.footer {
  padding: 50px 0 20px;
  background-color: #1a1a1a;
  color: #fff;
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 30px;
  margin-bottom: 30px;
}

.footer-section h3 {
  margin-bottom: 15px;
  color: #25D366;
}

.footer-bottom {
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #333;
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero .container {
    flex-direction: column;
    text-align: center;
  }

  .hero-content {
    margin-bottom: 40px;
  }

  .cta-buttons {
    justify-content: center;
  }

  .step {
    flex-direction: column;
    text-align: center;
  }

  .navigation {
    display: none;
  }
}
</style>
