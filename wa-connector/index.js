// index.js

const makeWASocket = require('@whiskeysockets/baileys').default;
const { useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const pino = require('pino');
const axios = require('axios');
const qrcode = require('qrcode-terminal');

// PASTIKAN URL INI BENAR SESUAI DENGAN ALAMAT SERVER LARAVEL ANDA
const LARAVEL_API_URL = 'http://127.0.0.1:8000/webhook';
// const BOT_SECRET_KEY = 'IniAdalahKunciRahasiaSayaYangSangatPanjang123!@#';
async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');

  const sock = makeWASocket({
    logger: pino({ level: 'silent' }),
    auth: state,
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', (update) => {
    const { connection, lastDisconnect, qr } = update;
    if(qr) {
        qrcode.generate(qr, {small: true});
    }
    if (connection === 'close') {
      const shouldReconnect = (lastDisconnect.error)?.output?.statusCode !== DisconnectReason.loggedOut;
      console.log('Koneksi terputus: ', lastDisconnect.error, ', mencoba menghubungkan kembali... ', shouldReconnect);
      if (shouldReconnect) {
        connectToWhatsApp();
      }
    } else if (connection === 'open') {
      console.log('Koneksi WhatsApp berhasil dibuka!');
    }
  });

  sock.ev.on('messages.upsert', async (m) => {
    const msgInfo = m.messages[0];
    if (msgInfo.key.fromMe || !msgInfo.message) {
      return;
    }

    const senderNumber = msgInfo.key.remoteJid;
    // Dapatkan teks pesan. Handle untuk pesan biasa dan extended.
    const messageText = msgInfo.message.conversation || msgInfo.message.extendedTextMessage?.text;

    if (!messageText) return;

    console.log(`Pesan dari [${senderNumber}]: "${messageText}"`);

    try {
      // Kirim data ke API Laravel menggunakan Axios
      const response = await axios.post(LARAVEL_API_URL, {
        phone: senderNumber,
        message: messageText,
        // secret: BOT_SECRET_KEY,
      });

      // Ambil balasan dari Laravel
      const replyText = response.data.reply;
      if (replyText) {
        await sock.sendMessage(senderNumber, { text: replyText });
        console.log(`Balasan terkirim ke [${senderNumber}]: "${replyText}"`);
      }
    } catch (error) {
      // Modifikasi di sini untuk menampilkan detail error dari server
      if (error.response) {
        console.error('Error saat menghubungi API Laravel:', JSON.stringify(error.response.data, null, 2));
      } else {
        console.error('Error saat menghubungi API Laravel:', error.message);
      }
    }
  });
}

// Jalankan botnya
connectToWhatsApp();
