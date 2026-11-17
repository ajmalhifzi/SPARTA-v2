# SPARTA

**Sistem Pengurusan Projek Tahun Akhir** is a local Laravel preview for KVSP1 students and supervisors. It uses Blade, Tailwind CSS, Alpine.js, and a session-backed demonstration repository.

The preview includes a student profile, a 13-section project proposal, separate PTA 1 and PTA 2 meeting logs, supervisor-only PTA 1 evaluation, and a Slaid page for PPTX, PDF, image pages, or Canva links. Laporan is a placeholder. Uploaded files are kept on the local private disk and served through assigned-project checks.

## Run locally

Requires PHP 8.2+, Composer, and Node.js.

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
npm run build
composer run serve:sparta
```

Open <http://127.0.0.1:8000>. The `serve:sparta` command sets the PHP upload limits needed for slide files up to 25 MB each. The login page supplies demonstration accounts.

```powershell
php artisan test
```

This is a **local preview**, not a production deployment. The current repository uses synthetic records stored in the session; it does not connect its forms to the already completed PostgreSQL schema. Demo authentication, signatures, and assessment rubric details require verified production integration. Keep `.env`, uploaded files, and session data private; they are excluded by `.gitignore`.

Project notes are in [PRODUCT.md](PRODUCT.md) and [docs](docs).
