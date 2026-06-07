# ClinicPro — Hostinger Deployment Guide

## Quick Start (5 Minutes)

### Step 1: Upload Files

1. Login to **Hostinger hPanel**
2. Go to **File Manager** → `public_html/`
3. Upload the `clinicpro/` folder (or ZIP and extract)
4. Your structure should be: `public_html/clinicpro/`

### Step 2: Create MySQL Database

1. In hPanel → **Databases** → **MySQL Databases**
2. Create a new database (e.g., `u123456789_clinicpro`)
3. Create a user with a strong password
4. Grant ALL privileges
5. Note down: **database name**, **username**, **password**

### Step 3: Configure Environment

1. In File Manager, navigate to `public_html/clinicpro/`
2. Rename `.env.example` to `.env`
3. Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u123456789_clinicpro
DB_USER=u123456789_admin
DB_PASS=YourStrongPassword123!
APP_URL=https://yourdomain.com/clinicpro/public
APP_ENV=production
APP_KEY=GENERATE_A_RANDOM_STRING_HERE
APP_TIMEZONE=Asia/Kolkata
```

### Step 4: Run Installer

Visit: `https://yourdomain.com/clinicpro/public/install.php`

1. Check requirements (all should be green ✓)
2. Enter your database credentials
3. Set your admin email and password
4. Click **Install ClinicPro**
5. **DELETE `install.php` immediately after!**

### Step 5: Login

Visit: `https://yourdomain.com/clinicpro/public/login.php`

---

## Alternative: Domain Root Setup

If you want ClinicPro accessible at `https://yourdomain.com/` directly:

### Option A: Subdomain

1. Create subdomain (e.g., `clinic.yourdomain.com`)
2. Set document root to: `public_html/clinicpro/public/`
3. Set `APP_URL=https://clinic.yourdomain.com` in `.env`

### Option B: Primary Domain

1. Move contents of `clinicpro/public/` to `public_html/`
2. Keep `clinicpro/` folder (with app/, config/, views/, etc.) one level up OR in `public_html/`
3. Update paths in all `require_once __DIR__ . '/../config/bootstrap.php'` if moved
4. Set `APP_URL=https://yourdomain.com` in `.env`

**Recommended: Use Option A (subdomain) for cleanest setup.**

---

## Directory Permissions

```
clinicpro/                → 755
clinicpro/public/         → 755
clinicpro/public/uploads/ → 755
clinicpro/storage/        → 755
clinicpro/storage/logs/   → 755
clinicpro/.env            → 644
```

## SSL Certificate

1. hPanel → **Security** → **SSL** → **Install**
2. Enable "Force HTTPS" in hPanel
3. Uncomment the HTTPS redirect in `public/.htaccess` (lines with `RewriteCond %{HTTPS}`)

## PHP Version

1. hPanel → **Advanced** → **PHP Configuration**
2. Select **PHP 8.1** or **PHP 8.2** or **PHP 8.3**
3. Enable extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`, `json`

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Blank white page | Set `APP_ENV=development` in `.env` temporarily to see errors |
| 500 Internal Server Error | Check `.htaccess` compatibility, check PHP error log |
| Database connection failed | Verify `.env` credentials match hPanel database settings |
| CSS/JS not loading | Check `APP_URL` in `.env` matches your actual URL |
| Upload fails | Check `public/uploads/` permissions (755) |
| Session issues | Check PHP version, clear browser cookies |

## Post-Installation Checklist

- [ ] Delete `install.php`
- [ ] Change default admin password
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Enable SSL and force HTTPS
- [ ] Upload clinic logo in Settings
- [ ] Configure invoice prefix/terms
- [ ] Add doctors and staff users
- [ ] Set up daily backups in hPanel

## Default Login

```
Email: (what you set during installation)
Password: (what you set during installation)
```

## Support

For issues, create a GitHub issue at: https://github.com/imsharmawpp/Clinic/issues
