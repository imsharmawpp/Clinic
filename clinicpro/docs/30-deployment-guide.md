# 30 — Deployment Guide

## Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.1 | 8.3 |
| MySQL | 8.0 | 8.0+ |
| Web Server | Apache 2.4 | Apache 2.4 with mod_rewrite |
| RAM | 512MB | 1GB+ |
| Storage | 1GB | 5GB+ |
| SSL | Required | Let's Encrypt / Paid |

## PHP Extensions Required

- pdo_mysql
- json
- mbstring
- fileinfo
- openssl
- session
- gd

## Hostinger Deployment (Step-by-Step)

### 1. Upload Files

Option A: File Manager
```
1. Login to Hostinger hPanel
2. Go to File Manager
3. Navigate to public_html/
4. Upload clinicpro.zip
5. Extract the archive
6. Ensure public_html/ points to clinicpro/public/
```

Option B: FTP
```
1. Connect via FTP (FileZilla)
2. Upload entire clinicpro/ folder to public_html/
3. Set document root to clinicpro/public/
```

### 2. Create Database

```
1. Hostinger hPanel → Databases → MySQL Databases
2. Create database: clinicpro (or your preferred name)
3. Create user with full privileges
4. Note: host, database name, username, password
```

### 3. Configure Environment

Create/edit `.env` file in clinicpro root:
```env
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
APP_URL=https://yourdomain.com
APP_ENV=production
```

### 4. Set Document Root

**If clinicpro is in root:**
```
Document Root: public_html/clinicpro/public/
```

**If using subdomain:**
```
Subdomain document root → clinicpro/public/
```

### 5. Run Installer

```
1. Visit: https://yourdomain.com/install.php
2. Check system requirements
3. Enter database credentials
4. Set admin email/password
5. Click "Install ClinicPro"
6. DELETE install.php immediately after!
```

### 6. Set Permissions

```bash
chmod 755 clinicpro/
chmod 755 clinicpro/public/
chmod 755 clinicpro/public/uploads/
chmod 755 clinicpro/storage/
chmod 755 clinicpro/storage/logs/
chmod 644 clinicpro/.env
chmod 644 clinicpro/public/.htaccess
```

### 7. SSL Configuration

Hostinger provides free SSL via Let's Encrypt:
```
hPanel → Security → SSL → Install
```

### 8. Post-Installation

- [ ] Delete install.php
- [ ] Change default admin password
- [ ] Upload clinic logo
- [ ] Configure clinic details in Settings
- [ ] Set timezone
- [ ] Configure invoice prefix

## Apache VirtualHost (if needed)

```apache
<VirtualHost *:443>
    ServerName clinic.yourdomain.com
    DocumentRoot /home/user/public_html/clinicpro/public
    
    <Directory /home/user/public_html/clinicpro/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
</VirtualHost>
```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 500 Error | Check .htaccess, PHP version, error logs |
| Database connection failed | Verify .env credentials |
| Blank page | Enable APP_DEBUG temporarily |
| CSS not loading | Check APP_URL in .env |
| Upload fails | Check directory permissions |
| 403 Forbidden | Check .htaccess RewriteBase |
