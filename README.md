# Speedlife General Trading — Company Website

Marketing site for **Speedlife General Trading LLC**, a Dubai-based commodity trading company (sugar, rice, spices). Built as freelance client work and deployed on Hostinger shared hosting.

**Live preview:** https://speedline-commodity-site.vercel.app

## Highlights

- **Single-page, dependency-free front end.** Hand-written HTML, CSS and vanilla JS with no framework or build step, so it loads fast on shared hosting.
- **Sections:** hero, about, product catalog, trade process, quality assurance, global reach and contact.
- **Working inquiry form.** The browser submits via `fetch` to a PHP endpoint (`contact.php`) that validates input and sends a formatted HTML email through authenticated SMTP using [PHPMailer](https://github.com/PHPMailer/PHPMailer).
- **Secrets kept out of source control.** SMTP credentials load from a server-only `smtp-config.php` that is gitignored.

## Tech

`HTML5` · `CSS3` · `JavaScript` · `PHP` · `PHPMailer` · `Hostinger`

## Project structure

```
index.html                 # The full site: markup, styles, scripts
contact.php                # POST endpoint: validate → send email via SMTP
smtp-config.example.php    # Template for server-side SMTP credentials
phpmailer/                 # Vendored PHPMailer library
images/                    # Optimized photography assets
```

## Deploying

1. Upload the repo contents to the hosting `public_html` directory.
2. Copy `smtp-config.example.php` to `smtp-config.php` on the server and fill in the mailbox password.
3. Done. No build step.
