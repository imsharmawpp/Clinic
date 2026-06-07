# 29 — Security Checklist

## Critical Security Measures

- [x] Prepared Statements (PDO) — all queries
- [x] Input Validation — sanitize() on all inputs
- [x] Output Escaping — e() helper on all output
- [x] CSRF Protection — token per session, verified on POST
- [x] Security Headers — via .htaccess
- [x] Rate Limiting — brute-force protection (5 attempts)
- [x] File Upload Validation — MIME type, size, extension
- [x] Audit Logs — all CRUD operations logged
- [x] Password Hashing — bcrypt with cost=12
- [x] Session Security — HttpOnly, SameSite, regenerate on login
- [x] Directory Protection — .htaccess blocks sensitive dirs
- [x] No Directory Listing — Options -Indexes
- [x] Upload Execution Block — PHP execution disabled in uploads/

## OWASP Top 10 Compliance

### A01:2021 — Broken Access Control
- Role-based access control (RBAC)
- Permission checks on every action
- clinic_id isolation in every query
- Middleware blocks unauthorized access

### A02:2021 — Cryptographic Failures
- Passwords hashed with bcrypt (cost=12)
- CSRF tokens use `random_bytes(32)`
- Session IDs regenerated on login
- No sensitive data in URL parameters

### A03:2021 — Injection
- PDO prepared statements everywhere
- Input sanitization on all user input
- No shell execution
- No eval/dynamic includes from user input

### A04:2021 — Insecure Design
- Repository pattern isolates data access
- Middleware architecture for auth/permissions
- Principle of least privilege in roles

### A05:2021 — Security Misconfiguration
- Error display disabled in production
- Security headers set
- Default credentials documented (change on install)
- Sensitive files blocked by .htaccess

### A06:2021 — Vulnerable & Outdated Components
- Minimal external dependencies
- CDN libraries from trusted sources
- PHP 8.3 (latest stable)
- MySQL 8 (latest stable)

### A07:2021 — Identification & Authentication Failures
- Account lockout mechanism
- Password strength (min 6 chars)
- Session timeout (2 hours)
- Failed login tracking

### A08:2021 — Software & Data Integrity Failures
- License verification system
- CSRF tokens prevent request forgery
- Audit logs track all changes

### A09:2021 — Security Logging & Monitoring Failures
- Audit logs for all CRUD operations
- IP address logged per action
- User agent captured
- JSON diff of changes stored
- Failed login attempts tracked

### A10:2021 — Server-Side Request Forgery (SSRF)
- No user-controlled URLs in server requests
- License verification uses hardcoded server URL
- File uploads validated locally (no remote fetch)

## Production Hardening

- [ ] Delete install.php after installation
- [ ] Change default admin password
- [ ] Set APP_ENV=production
- [ ] Disable display_errors
- [ ] Enable SSL/HTTPS
- [ ] Set proper file permissions (755 dirs, 644 files)
- [ ] Configure firewall
- [ ] Setup log rotation
- [ ] Regular backup schedule
