require('dotenv').config();
const express = require('express');
const nodemailer = require('nodemailer');
const path = require('path');

const app = express();
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

const transporter = nodemailer.createTransport({
  host: process.env.SMTP_HOST || 'smtp.gmail.com',
  port: parseInt(process.env.SMTP_PORT || '587'),
  secure: false,
  auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS,
  },
});

app.post('/api/inquiry', async (req, res) => {
  const { name, company, email, phone, country, product, quantity, message } = req.body;

  if (!name || !email || !product) {
    return res.status(400).json({ error: 'Missing required fields.' });
  }

  const rows = [
    ['Full Name', name],
    ['Company', company || '—'],
    ['Email', email],
    ['Phone', phone || '—'],
    ['Country', country || '—'],
    ['Product of Interest', product],
    ['Quantity (MT)', quantity || '—'],
    ['Additional Message', message || '—'],
  ];

  const tableRows = rows
    .map(
      ([k, v]) =>
        `<tr>
          <td style="padding:10px 16px;font-weight:600;color:#122844;background:#f3eee3;white-space:nowrap;border-bottom:1px solid #e4ddcd;">${k}</td>
          <td style="padding:10px 16px;color:#16202c;border-bottom:1px solid #e4ddcd;">${v}</td>
        </tr>`
    )
    .join('');

  const html = `
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f5f5f5;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f5;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
        <tr>
          <td style="background:#0c1a2b;padding:28px 36px;">
            <p style="margin:0;font-size:22px;font-weight:700;color:#d8b86a;letter-spacing:-0.01em;">SPEEDLINE</p>
            <p style="margin:4px 0 0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:rgba(255,255,255,0.6);">General Trading LLC</p>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 36px 8px;">
            <p style="margin:0;font-size:18px;font-weight:700;color:#0c1a2b;">New Inquiry Received</p>
            <p style="margin:8px 0 0;font-size:14px;color:#5d6675;">Submitted via the Speedline General Trading website</p>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 36px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e4ddcd;border-radius:8px;overflow:hidden;">
              ${tableRows}
            </table>
          </td>
        </tr>
        <tr>
          <td style="background:#f3eee3;padding:18px 36px;font-size:13px;color:#5d6675;">
            Reply directly to this email to respond to ${name}.
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>`;

  const mailOptions = {
    from: `"Speedline Website" <${process.env.SMTP_USER}>`,
    to: 'sales@speedlifetrades.com',
    replyTo: email,
    subject: `New Inquiry: ${product} — ${name}${company ? ` (${company})` : ''}`,
    html,
  };

  try {
    await transporter.sendMail(mailOptions);
    res.json({ success: true });
  } catch (err) {
    console.error('Email send error:', err.message);
    res.status(500).json({ error: 'Failed to send email. Please try again or contact us directly.' });
  }
});

// Serve index.html for root
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Speedline website running at http://localhost:${PORT}`);
});
