# 05 — Database Design

## Database: `clinicpro`
- Engine: InnoDB
- Charset: utf8mb4
- Collation: utf8mb4_unicode_ci

## Entity Relationship

```
license_keys
    └── clinics (1:N)
            ├── roles (1:N)
            │     └── role_permissions (N:M with permissions)
            ├── users (1:N)
            ├── doctors (1:N)
            │     ├── doctor_schedules (1:N)
            │     └── doctor_leaves (1:N)
            ├── patients (1:N)
            ├── appointments (1:N)
            ├── opd_visits (1:N)
            ├── medicines (1:N)
            │     └── medicine_stock (1:N)
            ├── prescriptions (1:N)
            │     └── prescription_items (1:N)
            ├── lab_tests_master (1:N)
            ├── lab_orders (1:N)
            │     └── lab_order_items (1:N)
            ├── invoices (1:N)
            │     ├── invoice_items (1:N)
            │     └── payments (1:N)
            ├── medical_documents (1:N)
            ├── settings (1:N)
            ├── notifications (1:N)
            └── audit_logs (1:N)
```

## Core Tables

### license_keys
Controls software activation and plan limits.

| Column | Type | Purpose |
|--------|------|---------|
| license_key | VARCHAR(64) | Unique activation key |
| domain | VARCHAR(255) | Locked domain |
| plan | ENUM | starter/professional/enterprise |
| max_doctors | INT | Plan limit |
| max_patients | INT | Plan limit |
| status | ENUM | inactive/active/suspended/expired |
| expiry_date | DATE | License expiration |

### clinics (Tenants)
Each clinic is an isolated tenant.

| Column | Type | Purpose |
|--------|------|---------|
| license_id | FK | Links to license_keys |
| name, slug | VARCHAR | Identification |
| logo, favicon | VARCHAR | White-label branding |
| theme_color | VARCHAR | UI customization |
| gstin | VARCHAR | Tax registration |
| currency_symbol | VARCHAR | Display currency |

### users
All system users (admin, doctors, staff).

| Column | Type | Purpose |
|--------|------|---------|
| clinic_id | FK | Tenant isolation |
| role_id | FK | Permission role |
| email | VARCHAR | Login credential |
| password | VARCHAR | bcrypt hash |
| is_super_admin | TINYINT | Super admin flag |
| failed_attempts | TINYINT | Brute-force counter |
| locked_until | DATETIME | Account lock |
| two_factor_enabled | TINYINT | 2FA flag |

### patients
Patient registry with demographics and emergency info.

### appointments
Scheduling with token system, status workflow.

### opd_visits
Clinical visits with vitals, diagnosis, treatment plan.

### invoices + invoice_items + payments
Full billing with GST, discounts, partial payments.

### prescriptions + prescription_items
Medicine prescriptions with dosage, frequency, duration.

### lab_orders + lab_order_items
Lab test ordering and result entry.

### medicines + medicine_stock
Pharmacy inventory with batch/expiry tracking.

### audit_logs
Complete audit trail with JSON diff.

## Indexes

- All foreign keys are indexed
- Composite unique keys for tenant isolation (e.g., `uq_clinic_patient`)
- Search indexes on `name`, `phone`, `status`, `date` columns

## Migration File

Located at: `database/migrations/001_schema.sql`

Run via web installer or CLI: `php database/seeds/install.php`
