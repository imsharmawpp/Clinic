# 07 — Authentication & Security

## Authentication Methods

### Email + Password Login
- Primary authentication method
- Email must be unique across system
- Password hashed with `password_hash()` (bcrypt, cost=12)
- Verified with `password_verify()`

### Session Management
- PHP native sessions
- Session name: `clinicpro_session`
- Lifetime: 7200 seconds (2 hours)
- `session_regenerate_id(true)` on login
- HttpOnly cookies
- SameSite: Strict

## Security Measures

### Password Security
```php
password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

### CSRF Protection
- Token generated per session: `bin2hex(random_bytes(32))`
- Verified on every POST request
- Available via `csrf_field()` helper in forms
- AJAX sends via `X-CSRF-TOKEN` header

### Brute Force Protection
- Failed attempts tracked per user
- After 5 failed attempts: account locked for 15 minutes
- Lock timestamp stored in `locked_until` column
- Reset on successful login

### Input Validation
- `sanitize()` — strips tags, trims
- `sanitize_int()` — forces integer
- `sanitize_float()` — forces float
- `e()` — HTML entity encoding for output

### SQL Injection Prevention
- All queries use PDO prepared statements
- No raw SQL concatenation anywhere
- Parameter binding with types

### XSS Prevention
- All output escaped with `e()` helper
- `htmlspecialchars(ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`

### Security Headers (.htaccess)
```apache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

### File Upload Security
- MIME type validation via `finfo`
- File size limit: 10MB
- Allowed types whitelist
- Random filenames generated
- No PHP execution in uploads directory
- `.htaccess` blocks script execution in uploads/

### Directory Protection
- `.htaccess` blocks access to: `.env`, `.git`, `config/`, `app/`, `views/`, `database/`, `storage/`
- `Options -Indexes` prevents directory listing

## Session Data Structure

```php
$_SESSION['user'] = [
    'id'          => int,
    'name'        => string,
    'email'       => string,
    'role_id'     => int,
    'role'        => string (slug),
    'clinic_id'   => int,
    'clinic_name' => string,
    'avatar'      => string|null,
    'is_super'    => bool,
];

$_SESSION['permissions'] = ['module.action', ...];
$_SESSION['csrf_token']  = string;
```
