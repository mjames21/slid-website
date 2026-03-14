# Memeh Web Starter (shared-hosting friendly)

This is a small, Laravel-inspired PHP web starter for consuming a Headless CMS (Memeh CMS APIs).
It’s designed to run on shared hosting (no Composer required) and uses:

- **Controllers** (app/Controllers) to fetch data from the Headless CMS
- **ViewModel** arrays (aka the “VM”) to pass data to views
- **Mustache templates** (resources/views) to render HTML
- **TailwindCSS CDN** for styling

## Quick start (local)

1) Edit `app/config/config.php` and set:
- `api_base` (e.g. `http://127.0.0.1:8000/api`)
- `tenant_id`
- `site_title`
- `home` image paths (hero + DG)

2) Run with the built-in PHP server **using the router**:

```bash
php -S localhost:8083 -t public public/router.php
```

Then open:
- http://localhost:8083/home
- http://localhost:8083/articles
- http://localhost:8083/events
- http://localhost:8083/jobs
- http://localhost:8083/departments
- http://localhost:8083/notices
- http://localhost:8083/publications

## Deploy (shared hosting)

Upload everything (keep folder structure). Ensure your host points the domain document-root to `public/`.

## Where to customize

- **Layout:** `resources/views/layout.html`
- **Pages:** `resources/views/*.html`
- **Page data mapping (VM):** each Controller in `app/Controllers`
- **Navigation from CMS menus:** `app/Support/NavBuilder.php`
