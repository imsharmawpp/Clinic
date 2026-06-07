# 26 — Folder Structure

```
clinicpro/
│
├── app/
│   ├── controllers/
│   │   ├── AppointmentController.php
│   │   ├── AuthController.php
│   │   ├── BillingController.php
│   │   ├── DashboardController.php
│   │   ├── DoctorController.php
│   │   ├── LabController.php
│   │   ├── OpdController.php
│   │   ├── PatientController.php
│   │   ├── PharmacyController.php
│   │   ├── PrescriptionController.php
│   │   ├── ReportsController.php
│   │   └── SettingsController.php
│   │
│   ├── middleware/
│   │   └── AuthMiddleware.php
│   │
│   ├── repositories/
│   │   ├── AppointmentRepository.php
│   │   ├── BillingRepository.php
│   │   └── PatientRepository.php
│   │
│   └── helpers.php
│
├── config/
│   ├── app.php          (Application constants)
│   ├── bootstrap.php    (Autoloader, session, includes)
│   └── database.php     (PDO singleton connection)
│
├── database/
│   ├── migrations/
│   │   └── 001_schema.sql   (Full database schema)
│   └── seeds/
│       └── install.php       (CLI installer with demo data)
│
├── docs/                     (Documentation — 34 files)
│   └── *.md
│
├── public/                   ← DOCUMENT ROOT
│   ├── .htaccess            (Apache config, security headers)
│   ├── assets/
│   │   └── css/
│   │       └── clinicpro.css
│   ├── uploads/
│   │   ├── .gitkeep
│   │   └── .htaccess        (Blocks PHP execution)
│   ├── appointments.php
│   ├── billing.php
│   ├── dashboard.php
│   ├── doctors.php
│   ├── install.php           (Web installer — DELETE after use)
│   ├── lab.php
│   ├── login.php
│   ├── logout.php
│   ├── opd.php
│   ├── patients.php
│   ├── pharmacy.php
│   ├── prescriptions.php
│   ├── reports.php
│   └── settings.php
│
├── storage/
│   └── logs/
│       └── .gitkeep
│
├── views/
│   ├── layouts/
│   │   └── app.php          (Master layout)
│   ├── appointments/
│   │   └── index.php
│   ├── auth/
│   │   └── login.php
│   ├── billing/
│   │   └── index.php
│   ├── dashboard/
│   │   └── index.php
│   ├── doctors/
│   │   └── index.php
│   ├── lab/
│   │   ├── create.php
│   │   ├── index.php
│   │   └── show.php
│   ├── opd/
│   │   ├── create.php
│   │   ├── index.php
│   │   └── show.php
│   ├── patients/
│   │   ├── create.php
│   │   ├── edit.php
│   │   ├── index.php
│   │   └── show.php
│   ├── pharmacy/
│   │   └── index.php
│   ├── prescriptions/
│   │   ├── create.php
│   │   ├── index.php
│   │   └── show.php
│   ├── reports/
│   │   └── index.php
│   └── settings/
│       └── index.php
│
├── .env                      (Environment configuration)
├── .env.example              (Template for deployment)
└── .gitignore
```

## Key Principles

1. **public/** is the only web-accessible directory
2. **app/** contains all PHP logic (never directly accessible)
3. **views/** contains templates (never directly accessible)
4. **config/** loads environment and bootstraps application
5. **storage/** for logs and temp files (writable)
6. **uploads/** for user files (writable, no PHP execution)
