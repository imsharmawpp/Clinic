# 08 — License Management System

## Objective

Prevent unauthorized installations and enforce plan limits.

## License Table Schema

```sql
CREATE TABLE license_keys (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    license_key   VARCHAR(64) NOT NULL UNIQUE,
    domain        VARCHAR(255),
    ip_address    VARCHAR(45),
    install_hash  VARCHAR(64),
    plan          ENUM('starter','professional','enterprise'),
    max_doctors   INT UNSIGNED DEFAULT 1,
    max_patients  INT UNSIGNED DEFAULT 500,
    status        ENUM('inactive','active','suspended','expired'),
    activated_at  DATETIME,
    expiry_date   DATE NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME ON UPDATE CURRENT_TIMESTAMP
);
```

## Activation Flow

```
1. Client installs ClinicPro on their server
        ↓
2. Web installer prompts for License Key
        ↓
3. System sends verification request to License Server
   POST https://license.clinicpro.in/api/verify
   {
       "license_key": "XXXX-XXXX-XXXX-XXXX",
       "domain": "clinic.example.com",
       "ip": "1.2.3.4",
       "hash": sha256(domain + server_info)
   }
        ↓
4. License Server validates and responds
   { "valid": true, "plan": "professional", "expiry": "2025-12-31" }
        ↓
5. Local system stores activation token
        ↓
6. Software unlocked for use
```

## Plan Enforcement

| Check | Starter | Professional | Enterprise |
|-------|---------|--------------|------------|
| Max Doctors | 1 | 5 | Unlimited |
| Max Patients | 500 | Unlimited | Unlimited |
| Lab Module | ✗ | ✓ | ✓ |
| Pharmacy | ✗ | ✓ | ✓ |
| Multi-user | ✗ | ✓ | ✓ |
| Reports | Basic | Advanced | Full |
| White-label | ✗ | ✗ | ✓ |
| API Access | ✗ | ✗ | ✓ |

## Anti-Piracy Measures

1. **Domain Lock** — License tied to specific domain
2. **IP Lock** — Optional IP restriction
3. **Installation Hash** — Unique hash per install
4. **Encrypted License** — Token stored encrypted locally
5. **Periodic Validation** — Checks license server periodically
6. **Expiry Enforcement** — Blocks access after expiry date

## Demo License

For development/testing:
```
Key: DEMO-CLINIC-PRO-2024-XXXX
Plan: Enterprise
Expiry: 2099-12-31
```

## License Server (Separate System)

The license server is a separate application that:
- Generates license keys
- Validates activations
- Tracks installations
- Manages renewals
- Provides admin dashboard for key management
