# 10 — Patient Management

## Features

### Patient Registration
- Auto-generated Patient ID (PT-000001)
- Name, DOB/Age, Gender, Blood Group
- Phone (required), Email
- Address (full: address, city, state, pincode)
- Emergency Contact (name, phone, relation)
- Insurance Details (provider, number, expiry)
- Notes field
- Avatar/photo upload

### Patient Search
- Search by name, phone, patient ID
- Filter by gender, status
- AJAX-powered search for appointment booking
- Pagination (25 per page)

### Patient Profile View
- Demographics card
- Visit history (OPD visits with doctor, date, diagnosis)
- Appointment history
- Billing history
- Prescriptions
- Lab reports
- Documents

### Patient Edit
- Update all demographics
- Status toggle (active/inactive)
- Audit logged

### Patient Export
- Permission-based (patients.export)

## Data Model

```sql
patients (
    id, clinic_id, patient_id,
    name, dob, age, gender, blood_group,
    phone, alternate_phone, email,
    address, city, state, pincode,
    emergency_name, emergency_phone, emergency_relation,
    insurance_provider, insurance_number, insurance_expiry,
    avatar, notes, status,
    registered_by, created_at, updated_at
)
```

## API Endpoints

| Method | URL | Action |
|--------|-----|--------|
| GET | patients.php | List all patients |
| GET | patients.php?action=create | Show registration form |
| POST | patients.php?action=store | Save new patient |
| GET | patients.php?action=show&id=X | View patient profile |
| GET | patients.php?action=edit&id=X | Show edit form |
| POST | patients.php?action=update&id=X | Update patient |
| GET | patients.php?action=search&q=X | AJAX search (JSON) |

## Validation Rules

- Name: required, non-empty
- Phone: required, non-empty
- Gender: must be male/female/other
- Blood Group: validated enum
- All other fields: optional, sanitized
