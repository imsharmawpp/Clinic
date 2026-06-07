# ClinicPro SaaS — Clinic Management System

## System Requirements
- PHP 8.1+
- MySQL 8.0+
- Apache with mod_rewrite (Hostinger shared/business hosting works)
- Extensions: pdo_mysql, json, mbstring, fileinfo

---

## Deployment on Hostinger

### Option A — Web Installer (Recommended)
1. Upload the entire `clinicpro/` folder to your Hostinger `public_html/`
2. Create a MySQL database in Hostinger hPanel → Databases
3. Visit: `https://yourdomain.com/clinicpro/public/install.php`
4. Fill in DB credentials and admin account
5. Click Install
6. **Delete `public/install.php` after install**

### Option B — Manual Install
1. Upload files to `public_html/clinicpro/`
2. Create a MySQL DB in hPanel
3. Import `database/migrations/001_schema.sql` via phpMyAdmin
4. Create `.env` file in project root:
```
DB_HOST=localhost
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_db_password
APP_URL=https://yourdomain.com/clinicpro/public
APP_ENV=production
```
5. Run seeder: `php database/seeds/install.php` via SSH or update .env and visit install.php

---

## File Structure
```
clinicpro/
├── public/              ← Web root (point domain here)
│   ├── index → login.php
│   ├── dashboard.php
│   ├── patients.php
│   ├── appointments.php
│   ├── billing.php
│   ├── opd.php
│   ├── prescriptions.php
│   ├── lab.php
│   ├── pharmacy.php
│   ├── doctors.php
│   ├── reports.php
│   ├── settings.php
│   ├── install.php     ← DELETE AFTER INSTALL
│   ├── .htaccess
│   └── assets/
├── app/
│   ├── controllers/
│   ├── repositories/
│   ├── middleware/
│   └── helpers.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── bootstrap.php
├── views/
│   └── layouts/app.php (master layout)
├── database/
│   ├── migrations/001_schema.sql
│   └── seeds/install.php
└── storage/
    └── logs/
```

---

## Demo Login
- URL: `http://yourdomain.com/clinicpro/public/login.php`
- Email: `admin@democlinic.com`
- Password: `Admin@1234`

---

## Hostinger Configuration
In hPanel → File Manager → `.htaccess` (in public_html root if needed):
```apache
RewriteEngine On
RewriteRule ^$ clinicpro/public/ [L,R=301]
```

Or point domain directly to `public_html/clinicpro/public/`

---

## Modules
| Module | Features |
|--------|---------|
| Dashboard | Stats, charts, quick actions |
| Patients | Register, search, history, documents |
| Appointments | Scheduling, slot management, status |
| OPD | Vitals, diagnosis, clinical notes |
| Prescriptions | Digital Rx, print-ready |
| Laboratory | Orders, results, tracking |
| Pharmacy | Stock, expiry, low-stock alerts |
| Billing | GST invoices, payments, receipts |
| Doctors | Profiles, schedules |
| Reports | Revenue, appointments, patient analytics |
| Settings | Clinic profile, users, roles |

---

## Security Features
- bcrypt password hashing (cost 12)
- CSRF protection on all forms and AJAX
- Brute-force lockout (5 attempts, 15 min lock)
- Session regeneration on login
- SQL injection prevention (PDO prepared statements)
- XSS prevention (htmlspecialchars throughout)
- File upload type/size validation
- .htaccess blocks direct access to app/, views/, config/

---

## Support
ClinicPro v1.0 | Built with PHP 8.3 + MySQL 8 + Bootstrap 5
