# 21 — API Documentation

## Base URL

```
https://your-domain.com/clinicpro/public/
```

## Authentication

All API requests require an active session (cookie-based) or can be extended to support token-based auth.

## CSRF Protection

All POST/PUT/DELETE requests require:
- Header: `X-CSRF-TOKEN: {token}`
- Or form field: `_csrf={token}`

## Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login.php` | Login (email, password) |
| GET | `/logout.php` | Logout |

### Patients

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/patients.php` | List patients (paginated) |
| GET | `/patients.php?action=show&id=X` | Get patient details |
| POST | `/patients.php?action=store` | Create patient |
| POST | `/patients.php?action=update&id=X` | Update patient |
| GET | `/patients.php?action=search&q=X` | Search (JSON) |

### Appointments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/appointments.php` | List appointments |
| POST | `/appointments.php?action=store` | Book appointment |
| POST | `/appointments.php?action=updateStatus` | Update status (AJAX) |
| GET | `/appointments.php?action=slots&doctor_id=X&date=Y` | Get slots (JSON) |

### Billing

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/billing.php` | List invoices |
| POST | `/billing.php?action=store` | Create invoice |
| POST | `/billing.php?action=payment` | Record payment |

### Doctors

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/doctors.php` | List doctors |
| POST | `/doctors.php?action=store` | Add doctor |

### Lab

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/lab.php` | List lab orders |
| POST | `/lab.php?action=store` | Create order |
| POST | `/lab.php?action=result` | Enter results |

### Prescriptions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/prescriptions.php` | List prescriptions |
| POST | `/prescriptions.php?action=store` | Create prescription |

### Reports

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reports.php?type=revenue` | Revenue report |
| GET | `/reports.php?type=appointments` | Appointment report |
| GET | `/reports.php?type=patients` | Patient report |

## Response Format

### Success
```json
{
    "success": true,
    "data": { ... }
}
```

### Error
```json
{
    "error": "Error message",
    "code": 400
}
```

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthenticated |
| 403 | Forbidden (CSRF / Permission) |
| 404 | Not Found |
| 500 | Server Error |
