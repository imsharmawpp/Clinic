# 03 — System Architecture

## High-Level Architecture

```
Browser (Client)
     ↓ HTTPS
┌─────────────────────────┐
│    Apache / Nginx        │
│    (public/ directory)   │
└────────────┬────────────┘
             ↓
┌─────────────────────────┐
│   Front Controller      │
│   (*.php entry points)  │
└────────────┬────────────┘
             ↓
┌─────────────────────────┐
│   Controllers           │
│   (Business Logic)      │
└────────────┬────────────┘
             ↓
┌─────────────────────────┐
│   Repositories          │
│   (Data Access Layer)   │
└────────────┬────────────┘
             ↓
┌─────────────────────────┐
│   MySQL 8 (InnoDB)      │
│   (Data Storage)        │
└─────────────────────────┘
```

## Application Layers

### 1. Presentation Layer
- Views (PHP templates with Bootstrap 5)
- Layout system (views/layouts/app.php)
- Flash messages, pagination

### 2. Application Layer
- Controllers handle request/response
- Input validation and sanitization
- CSRF protection
- Permission checks

### 3. Business Layer
- Repositories encapsulate queries
- Helpers provide utility functions
- Audit logging on all mutations

### 4. Data Layer
- PDO with prepared statements
- Singleton database connection
- Transactions for multi-table operations

### 5. Security Layer
- AuthMiddleware (session-based)
- Role-based access control (RBAC)
- CSRF tokens
- XSS protection (output escaping)
- SQL injection prevention (PDO)
- Brute-force protection (account locking)

### 6. License Layer
- Domain-locked license keys
- Plan-based feature/limit enforcement
- Installation hash verification

### 7. Audit Layer
- All create/update/delete operations logged
- IP address and user agent captured
- JSON diff of old/new data

## Multi-Tenancy Model

- Single database, shared tables
- Every table has `clinic_id` column
- All queries filter by `clinic_id`
- Middleware enforces tenant isolation

## File Structure

```
clinicpro/
├── app/
│   ├── controllers/
│   ├── middleware/
│   ├── repositories/
│   └── helpers.php
├── config/
│   ├── app.php
│   ├── bootstrap.php
│   └── database.php
├── database/
│   ├── migrations/
│   └── seeds/
├── public/          ← Document root
│   ├── assets/
│   ├── uploads/
│   └── *.php (entry points)
├── storage/
│   └── logs/
├── views/
│   ├── layouts/
│   └── [module]/
├── docs/
└── .env
```
