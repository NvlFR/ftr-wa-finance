require('dotenv').config();

const makeWASocket = require('@whiskeysockets/baileys').default;
const { useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const pino = require('pino');
const axios = require('axios');
const express = require('express');
const qrcode = require('qrcode-terminal');

// --- KONFIGURASI (Sekarang dibaca dari file .env) ---
const LARAVEL_API_URL = process.env.LARAVEL_API_URL;
const BOT_SECRET_KEY = process.env.BOT_SECRET_KEY;
const EXPRESS_PORT = 3000;
async function startBot() {
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys');
    const sock = makeWASocket({
        logger: pino({ level: 'silent' }),
        printQRInTerminal: true,
        auth: state,
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;
        if (qr) {
            qrcode.generate(qr, { small: true });
        }
        if (connection === 'close') {
            const shouldReconnect = (lastDisconnect.error)?.output?.statusCode !== DisconnectReason.loggedOut;
            if (shouldReconnect) {
                startBot();
            }
        } else if (connection === 'open') {
            console.log('Koneksi WhatsApp berhasil dibuka!');
        }
    });

    sock.ev.on('messages.upsert', async (m) => {
        const msgInfo = m.messages[0];
        if (msgInfo.key.fromMe || !msgInfo.message) return;
        const senderNumber = msgInfo.key.remoteJid;
        const messageText = msgInfo.message.conversation || msgInfo.message.extendedTextMessage?.text;
        if (!messageText) return;

        console.log(`Pesan dari [${senderNumber}]: "${messageText}"`);

        try {
            const response = await axios.post(LARAVEL_API_URL, {
                phone: senderNumber,
                message: messageText,
                secret: BOT_SECRET_KEY, // <-- Mengirim kunci rahasia
            });

            const responseData = response.data;
            if (responseData.type === 'file') {
                console.log(`Menerima permintaan file dari Laravel: ${responseData.url}`);
                const fileResponse = await axios.get(responseData.url, { responseType: 'arraybuffer' });
                const fileBuffer = Buffer.from(fileResponse.data);

                await sock.sendMessage(senderNumber, {
                    document: fileBuffer,
                    mimetype: responseData.mimetype,
                    fileName: responseData.fileName,
                    caption: responseData.caption,
                });
                console.log(`File terkirim ke [${senderNumber}]: "${responseData.fileName}"`);
            } else {
                const replyText = responseData.reply;
                if (replyText) {
                    await sock.sendMessage(senderNumber, { text: replyText });
                    console.log(`Balasan terkirim ke [${senderNumber}]: "${replyText}"`);
                }
            }
        } catch (error) {
            console.error('Error saat menghubungi API Laravel:', error.response ? error.response.data : error.message);
            await sock.sendMessage(senderNumber, { text: 'Maaf, terjadi kesalahan di server bot. Coba lagi nanti.' });
        }
    });

    // --- Server Express untuk menerima perintah dari Laravel ---
    const app = express();
    app.use(express.json());

    // Middleware sederhana untuk cek secret key dari Laravel
    app.use((req, res, next) => {
        const secret = req.body.secret;
        if (secret !== BOT_SECRET_KEY) {
            return res.status(401).json({ error: 'Unauthorized' });
        }
        next();
    });

    // Endpoint untuk mengirim pesan proaktif
    app.post('/send-message', async (req, res) => {
        const { recipient, message } = req.body;
        if (!recipient || !message) {
            return res.status(400).json({ error: 'Recipient and message are required' });
        }
        try {
            await sock.sendMessage(recipient, { text: message });
            console.log(`Pesan proaktif terkirim ke ${recipient} via API`);
            res.status(200).json({ success: true, message: 'Message sent' });
        } catch (e) {
            console.error('Gagal mengirim pesan proaktif via API:', e);
            res.status(500).json({ success: false, error: 'Failed to send message' });
        }
    });

    app.listen(EXPRESS_PORT, () => {
        console.log(`Server Express berjalan di port ${EXPRESS_PORT}. Siap menerima perintah dari Laravel.`);
    });

    
}

// Jalankan bot
startBot();