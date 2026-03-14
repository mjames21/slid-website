# Deploy to cPanel / shared hosting (Apache)

This package is structured for the common cPanel layout:

- `public_html/`  -> web root (what the domain serves)
- `app/`, `routes/`, `resources/` -> kept **outside** web root for security

## Option A (recommended): With .htaccess rewrites (pretty URLs)
1. Upload and extract this zip into your cPanel **home directory** (same level as `public_html`).
2. Copy/merge contents of `cpanel-ready/public_html/` into your real `public_html/` folder.
3. Copy `app/`, `routes/`, `resources/` into your home directory (same level as `public_html`).
   You should end up with:
   ```
   /home/<user>/public_html/index.php
   /home/<user>/public_html/bootstrap.php
   /home/<user>/app/Support/...
   /home/<user>/routes/web.php
   /home/<user>/resources/views/...
   ```
4. Ensure `public_html/.htaccess` exists (included in this package).
5. Visit your domain.

## Option B (no .htaccess available): Use /index.php/... URLs
If your host does not allow `.htaccess` (rare on cPanel Apache), use the provided file:
- `public_html/index.nohtaccess.php`

Steps:
1. Rename `public_html/index.php` -> `public_html/index.htaccess.php`
2. Rename `public_html/index.nohtaccess.php` -> `public_html/index.php`
3. Your routes must be accessed like:
   - `/index.php/`
   - `/index.php/articles`
   - `/index.php/events`

## Notes
- Do NOT place `app/` inside `public_html` unless you fully restrict access.
- Assets should be referenced as `/assets/...` (these live in `public_html/assets/`).


##Localhost deployment (Developement)
php -S localhost:8083 -t public_html public_html/router.php