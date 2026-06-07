# 24 — Multi-Tenant SaaS Architecture

## Tenant Isolation Model

**Single Database, Shared Tables** with `clinic_id` column.

Every data table includes a `clinic_id` foreign key that references `clinics.id`.

## Security Guarantees

1. **Clinic A cannot see Clinic B data** — enforced at query level
2. **Every query includes `clinic_id` filter** — via `clinic_id()` helper
3. **Middleware validates session** — user's clinic_id set at login
4. **No cross-tenant data leakage** — repository pattern enforces filtering

## Implementation

### Session
```php
$_SESSION['user']['clinic_id'] = 1; // Set at login
```

### Helper
```php
function clinic_id(): int {
    return (int)($_SESSION['user']['clinic_id'] ?? 0);
}
```

### Repository Pattern
```php
// Every query uses clinic_id
$stmt = $db->prepare("SELECT * FROM patients WHERE clinic_id = ? AND id = ?");
$stmt->execute([clinic_id(), $id]);
```

## Tenant Provisioning Flow

```
1. Super Admin creates license key
        ↓
2. Client installs on their server
        ↓
3. Web installer creates clinic record
        ↓
4. Clinic admin account created
        ↓
5. clinic_id assigned to all subsequent data
```

## Scalability Considerations

### Current (Single Server)
- One MySQL database, all clinics in same tables
- Suitable for 50-100 clinics

### Future (Scale-out)
- Database-per-tenant option
- Read replicas
- Connection pooling
- Horizontal sharding by clinic_id

## Data Isolation Checklist

- [ ] Every INSERT includes clinic_id
- [ ] Every SELECT filters by clinic_id
- [ ] Every UPDATE filters by clinic_id
- [ ] Every DELETE filters by clinic_id
- [ ] API responses never leak other tenant data
- [ ] File uploads organized by clinic_id subfolder
- [ ] Audit logs tagged with clinic_id
