// backend/server.js
require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const { SESClient, SendRawEmailCommand } = require("@aws-sdk/client-ses");

const app = express();

// Updated CORS configuration to handle multiple origins
const frontendUrl = process.env.FRONTEND_URL || '';
const baseDomain = frontendUrl.replace(/^https?:\/\//, '').replace(/^www\./, '');

const allowedOrigins = [
  'http://127.0.0.1:5500/Public/',
  'http://localhost:3000', // For local development
  'http://localhost:3001', // Node server
  'http://localhost', // XAMPP default
  'https://registrations.saviskar.co.in', // Your old domain
  'https://13.255.106.225' // Your server's public IP
];

const corsOptions = {
  origin: function (origin, callback) {
    if (!origin) return callback(null, true);
    if (allowedOrigins.indexOf(origin) !== -1) {
      return callback(null, true);
    }
    // Dynamically allow any http/https or www variant of the FRONTEND_URL
    if (baseDomain && origin.includes(baseDomain)) {
      return callback(null, true);
    }
    callback(new Error('Not allowed by CORS'));
  },
  credentials: true,
  methods: 'GET,HEAD,PUT,PATCH,POST,DELETE',
  optionsSuccessStatus: 204
};

app.use(cors(corsOptions));
app.use(express.json());

// Serve static files from the frontend Public directory
app.use(express.static(path.join(__dirname, '../Public')));

// Configure the AWS SES V3 client
const sesClient = new SESClient({
  region: process.env.AWS_REGION,
  // This credentials block is required for Lightsail environment variables
  credentials: {
    accessKeyId: process.env.AWS_ACCESS_KEY_ID,
    secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY,
  },
});

// In-memory store for OTPs. For production, use a database like Redis.
const otpStore = {};

app.post('/api/send-otp', async (req, res) => {
  const { email } = req.body;
  if (!email) {
    return res.status(400).json({ success: false, error: 'Email is required.' });
  }

  const otp = Math.floor(100000 + Math.random() * 900000).toString();
  otpStore[email] = { otp, expires: Date.now() + 10 * 60 * 1000 };

  const emailHtmlBody = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
      <title>Your Saviskar Festival Verification Code</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: 'Poppins', Arial, sans-serif; background-color: #f8f9fa;">
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 40px auto;">
        <tr>
          <td>
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);">
              <tr>
                <td align="center" style="padding: 40px 0 30px 0;">
                  <img src="https://i.ibb.co/FkG6hLVV/logo.png" alt="Saviskar 2025 Logo" width="180" style="display: block;">
                </td>
              </tr>
              
              <tr>
                <td style="padding: 0 40px 40px 40px;">
                  <h1 style="font-size: 24px; color: #2c3e50; font-weight: 600; text-align: center; margin: 0 0 20px 0;">
                    Your Verification Code
                  </h1>
                  <p style="font-size: 16px; color: #2c3e50; line-height: 1.6; text-align: center; margin: 0 0 30px 0;">
                    Please use the following code to complete your registration.
                  </p>
                  
                  <div style="border: 1px solid #e0e0e0; border-radius: 12px; text-align: center; padding: 25px; margin-bottom: 30px;">
                    <p style="font-size: 48px; font-weight: 700; color: #2ecc71; letter-spacing: 8px; margin: 0;">
                      ${otp}
                    </p>
                  </div>
                  
                  <p style="font-size: 15px; color: #7f8c8d; text-align: center; line-height: 1.5; margin: 0 0 10px 0;">
                    This code is valid for <strong>10 minutes</strong>.
                  </p>
                  
                  <p style="font-size: 14px; color: #7f8c8d; text-align: center; line-height: 1.5; margin: 0;">
                    If you did not request this, please disregard this email.
                  </p>
                </td>
              </tr>
              
              <tr>
                <td align="center" style="padding: 30px 40px; background-color: #f8f9fa; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                  <p style="font-size: 12px; color: #7f8c8d; margin: 0;">
                    © 2025 Saviskar Festival. All Rights Reserved.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>
  `;

  const rawMessage = [
    `From: ${process.env.SES_SOURCE_EMAIL}`,
    `To: ${email}`,
    `Subject: Your Saviskar Festival Verification Code`,
    `MIME-Version: 1.0`,
    `Content-Type: text/html; charset=UTF-8`,
    ``,
    emailHtmlBody,
  ].join('\n');

  const command = new SendRawEmailCommand({
    RawMessage: { Data: Buffer.from(rawMessage) },
  });

  try {
    if (!process.env.AWS_REGION || !process.env.SES_SOURCE_EMAIL || !process.env.AWS_ACCESS_KEY_ID || !process.env.AWS_SECRET_ACCESS_KEY) {
      console.log(`\n========================================`);
      console.log(`[LOCAL DEV MOCK] OTP for ${email}: ${otp}`);
      console.log(`========================================\n`);
      return res.json({ success: true, message: 'OTP sent to console (mocked)', mockOtp: otp });
    }

    await sesClient.send(command);
    res.json({ success: true });
  } catch (error) {
    console.error('Failed to send email:', error);
    res.status(500).json({ success: false, error: 'Failed to send OTP.' });
  }
});

app.post('/api/verify-otp', (req, res) => {
  const { email, otp } = req.body;
  const stored = otpStore[email];

  if (stored && stored.otp === otp && Date.now() < stored.expires) {
    delete otpStore[email];
    res.json({ success: true, message: 'OTP verified.' });
  } else {
    res.status(400).json({ success: false, error: 'Invalid or expired OTP.' });
  }
});

const PORT = process.env.PORT || 3001;

// Handle any other routes by serving the frontend index.html
app.use((req, res) => {
  res.sendFile(path.join(__dirname, '../Public/index.html'));
});

app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
