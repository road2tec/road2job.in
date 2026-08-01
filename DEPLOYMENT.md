# Deploying Road2Job to Hostinger

Road2Job is a server-rendered Core PHP 8 MVC app with no build step and no Node.js dependency in production. Deployment is a file upload + one database import - under 5 minutes end to end.

One ready-to-upload ZIP is built for you (project root): **`road2job-hostinger-final.zip`**. Extract it directly inside Hostinger's existing `public_html` folder - nothing else to upload, no second ZIP, no files placed outside `public_html`, no migrations to import one by one. See "Directory layout" and the security note below before you upload.

**Earlier approaches tried and superseded, for context**: this project's deployment layout went through several iterations in the same session before landing here - a layout with private folders one level *above* `public_html` (broke on Hostinger's `domains/yourdomain.tld/` nesting), then two separate ZIPs (technically correct but more upload steps), then 22 separate migration files to import one at a time via phpMyAdmin (correct but slow and error-prone to click through). **Current approach**: everything lives inside `public_html` in one ZIP, and the entire database (schema + roles + all migrations + an admin account) is ONE SQL file to import once.

## Directory layout

`road2job-hostinger-final.zip`'s root **is** the contents - extracting it while standing inside Hostinger's existing `public_html` folder produces exactly this, directly inside `public_html`, no wrapper folder:

```
public_html/
├── index.php            <- front controller (BASE_PATH = this file's own directory)
├── .htaccess             <- routing + security headers + blocks the folders below
├── .env                  <- placeholders only as shipped; fill in real values (step 4)
├── .env.example           <- same template, kept as a reference copy
├── composer.json
├── assets/                <- CSS/JS/images, publicly served
├── uploads/               <- avatars, documents, gallerys, logos, resumes, videos (writable, see step 5; already blocks PHP execution)
├── app/                   <- controllers, models, views, services - blocked from direct web access (see below)
├── core/                  <- framework classes - blocked from direct web access
├── config/                <- blocked from direct web access
├── database/               <- ONE file: road2job_database.sql - blocked from direct web access
├── routes/                 <- blocked from direct web access
├── storage/logs/, storage/sessions/  <- writable, blocked from direct web access
└── vendor/                 <- hand-vendored PHPMailer - blocked from direct web access
```

### Security note: how the private folders are protected without being outside the web root

Putting `app/`, `core/`, `config/`, `database/`, `routes/`, `storage/`, `vendor/`, and `.env` *inside* `public_html` means they physically exist under the domain's web-servable directory - there's no folder boundary keeping them off-limits by default. This package protects them entirely through `.htaccess`, two layers deep:

1. `public_html/.htaccess` has a `RewriteRule` that returns `403 Forbidden` for any request path starting with `app/`, `core/`, `config/`, `database/`, `routes/`, `storage/`, or `vendor/`, plus a `<FilesMatch>` block denying `.env`/`.env.example`, `.git*`, and `composer.json`/`composer.lock` - all evaluated *before* the normal front-controller routing rule.
2. Each of those seven folders **also ships its own `.htaccess`** with an unconditional `Require all denied` - so even if the top-level rule were ever misconfigured or removed, direct requests into those folders are still refused independently.

This was verified against real Apache (not just PHP's built-in server, which ignores `.htaccess` entirely) - see Verification below. If you ever add a new top-level private folder to this project in the future, remember to add both a rewrite-block entry and its own deny-all `.htaccess`, the same way.

## Step 1 - Upload the ZIP

In hPanel > Files > File Manager, open the domain's **existing `public_html` folder** (for accounts managing multiple domains, this is usually under `domains/yourdomain.tld/public_html/` - make sure you're inside the right domain's folder, not a different one or the account's top-level root).

## Step 2 - Extract

Upload `road2job-hostinger-final.zip` and extract it **while standing inside `public_html`**. Everything lands directly inside it, no subfolder created, no second location to worry about. Delete the `.zip` file afterward (optional cleanup).

## Step 3 - Create the database

In hPanel > Databases > MySQL Databases, create a database and a user with full privileges on it. Note the generated database name/username (Hostinger prefixes both with your account ID, e.g. `u448940947_...`) and set a strong password.

## Step 4 - Import `road2job_database.sql`

One file, one import. In phpMyAdmin (linked from the database row in hPanel) or via `mysql` over SSH: Import tab → Choose File → `database/road2job_database.sql` → Go. This single file contains the full schema (60+ tables), all foreign keys and indexes, the 8 required roles, default settings, and one ready-to-use admin account - nothing else to import afterward.

**Default admin login created by this file**: `admin@road2job.in` / a freshly-generated password shown in the SQL file's own header comment (open `database/road2job_database.sql` in a text editor and read the first few lines if you need to look it up again). **Change this password immediately after your first login** (Settings → your profile, or update the row directly in phpMyAdmin with a new `password_hash`). This is a fresh, randomly-generated password unique to this build - not a publicly-documented default - but it's still known to anyone who can read this file, so treat it as temporary.

The file is idempotent (safe to re-run if something goes wrong partway through - `CREATE TABLE IF NOT EXISTS`, `ON DUPLICATE KEY UPDATE`, and `information_schema`-guarded `ALTER TABLE`s throughout) - if an import is interrupted, just run it again from the top rather than trying to figure out where it stopped.

## Step 5 - Edit `.env`

A `.env` file is already at the root of `public_html` (shipped with placeholders, no real secrets baked into the ZIP). Open it in hPanel's File Manager and fill in:

- `APP_KEY` - set to any random 32+ character string.
- `APP_URL` - your real domain (`https://road2job.in`), with no path suffix.
- `DB_HOST`/`DB_PORT`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` - from step 3 (Hostinger's MySQL is almost always `DB_HOST=localhost`, `DB_PORT=3306`).
- `MAIL_*` - your SMTP credentials (this project's real mailbox is `admin@road2job.in`, matching the production domain).
- `FAST2SMS_*` - your Fast2SMS API key and sender ID once your DLT/WhatsApp template approvals are in place.
- `APP_DEBUG=false` - must stay false in production (stack traces should never reach real visitors).

This edit is unavoidable regardless of packaging approach - nobody but you has the real database password, SMTP password, or API keys, so `.env` can never ship pre-filled with them. Everything else about this deployment requires zero manual file moves, no separate migration imports, and no path edits.

Also set writable permissions while you're in File Manager (Permissions dialog, or `chmod` over SSH - start with `755`, use `775` only if uploads still fail):
- `public_html/storage/logs/`
- `public_html/storage/sessions/`
- `public_html/uploads/avatars/`, `documents/`, `gallerys/`, `logos/`, `resumes/`, `videos/`

## Step 6 - Open `https://road2job.in`

That's it - homepage should load immediately.

## Verify the deploy

- Load the homepage - confirm no PHP errors, styling loads correctly (confirms `.htaccess` rewrite + `mod_expires` are active).
- Load `/robots.txt` and `/sitemap.xml` - confirm both render (confirms routing works end-to-end, not just static pages).
- Log in with the admin credentials from step 4, change the password immediately, then open `/admin/system` and `/admin/health` - confirms DB connectivity, and shows at a glance whether SMS/SMTP env vars were picked up correctly.
- Submit a real registration end-to-end (OTP + email verification) to confirm Fast2SMS/SMTP are both actually working, not just configured.
- **Confirm the private folders are actually blocked** - visit `https://yourdomain/.env`, `https://yourdomain/app/controllers/AuthController.php`, `https://yourdomain/core/Autoloader.php`, `https://yourdomain/config/database.php`, `https://yourdomain/database/road2job_database.sql`, `https://yourdomain/vendor/autoload.php` - every one of these must return **403 Forbidden**, never 200 and never a directory listing. If any of them return 200, stop and check that folder's own `.htaccess` is actually present (it should have been extracted along with everything else) before doing anything else on the live site.

## What's already built and verified in this app (not part of this deployment task, noted so nothing seems missing)

- **Interview module**: one continuous video recording per session (not one clip per question), questions read aloud via the browser's native `SpeechSynthesis` API (no external voice service/API key required).
- **Institute panel**: no course-selling anywhere in the app - institutes maintain a premium public portfolio (placements, achievements, campus activities, company visits, gallery, notices, recruiters), all editable from their dashboard.
- **Institute ranking**: computed automatically from placement uploads, posting activity, profile completeness, and recent engagement, weighted toward genuine sustained activity over spam bursts - students see top/trending institutes first, driven by this live score, never hardcoded.
- **Student portfolio**: animated public profile at `/u/{username}` with skills, certificates, projects, experience, resume builder, and linked GitHub/LinkedIn/coding-profile URLs, all editable from the dashboard.

These were built and tested in earlier phases of this project (see this project's own change history) - this deployment task packages the app as it already stands, it does not rebuild or re-verify feature behavior beyond the deployment path itself (paths, autoloading, routing, `.htaccess`, and the database import all re-verified fresh for this exact package, end to end - see Verify above).

## Backups

No in-app "download a database dump" feature exists, deliberately - a full DB dump (including password hashes) becoming downloadable through the admin panel on demand has no established retention or access-control strategy, and the risk of a compromised admin session exfiltrating the whole database in one click wasn't worth the convenience. Instead:

- Use Hostinger hPanel's built-in scheduled backup feature (Files > Backups) for both the database and file storage - this is external to the app entirely, which is the right place for backup infrastructure to live.
- If you need an ad hoc manual backup, use phpMyAdmin's Export tab or `mysqldump` over SSH, and store the resulting file somewhere access-controlled (not inside `public_html/`).

## Monitoring

Real uptime monitoring has to watch the site from outside its own server, so it's not something this app builds for itself. `/admin/health` (super admin only) gives a real, self-contained snapshot when you're already logged in - DB connectivity, recent error count, disk space, PHP version - but for actual outage alerting, point a free external service (e.g. UptimeRobot, Pingdom's free tier) at the homepage.

## Post-Deploy Checklist

- [ ] Changed the admin password from the one in `road2job_database.sql`'s header comment to something private only you know.
- [ ] Confirm `APP_DEBUG=false` in the live `.env` (stack traces should never be shown to real visitors).
- [ ] Confirm all seven private folders (`app`, `core`, `config`, `database`, `routes`, `storage`, `vendor`) and `.env`/`.env.example` return 403 when requested directly (see Verify above) - this is the single most important check given this deployment's layout.
- [ ] Confirm GD is loaded (`/admin/health` won't show this directly, but a test avatar upload will silently skip compression if it's missing - check the uploaded file size to confirm).
- [ ] Confirm `mod_expires` is active (`curl -I` a `.css` asset and look for `Cache-Control`).
- [ ] Confirm PHP OPcache is enabled (`opcache.enable=1`) - most shared-hosting PHP configs, Hostinger included, ship this on by default, but the app can't guarantee or control it. If your host exposes a PHP configuration panel, check there; otherwise a simple before/after timing comparison on a PHP-heavy page is a reasonable proxy.
- [ ] Set up a scheduled backup in hPanel (see Backups above).
- [ ] Set up an external uptime monitor pointed at the homepage (see Monitoring above).
- [ ] Submit `https://yourdomain/sitemap.xml` to Google Search Console.
- [ ] Toggle maintenance mode on/off once from `/admin/system` as a smoke test - it should be OFF when you're done.

## Prerequisites

- A Hostinger hosting plan with PHP 8.0+ (this project targets PHP >=8.0 exactly - confirmed no PHP 8.1+-only syntax anywhere in the codebase, so PHP 8.0 works, but selecting 8.1 or 8.2 in hPanel's PHP version selector is recommended for longer support) and a MySQL database (hPanel > Databases > MySQL Databases).
- GD and `mod_expires` are standard on Hostinger's shared-hosting PHP/Apache stack, but confirm both are active after deploy (see Post-Deploy Checklist) - this app relies on GD for image compression on avatar/logo/gallery uploads and `mod_expires` for static-asset cache headers.
- An SMTP account (Hostinger provides `smtp.hostinger.com`, or use any other provider) and a Fast2SMS account for OTP delivery (see the app's own `/admin/system` status page after deploy to confirm both are picked up correctly).
- Composer is **not required**. `vendor/` ships pre-populated (hand-vendored PHPMailer, no `composer.lock` in this project) - `composer install` is optional, only useful if you later add a real Composer dependency.
