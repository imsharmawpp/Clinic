# 12 — Doctor Management

## Features

### Doctor Profiles
- Name, Specialization, Qualification
- Registration Number (Medical Council)
- Email, Phone
- Experience (years)
- Consultation Fee
- Bio/Description
- Avatar photo
- Digital Signature upload

### Commission System
- Type: Fixed or Percentage
- Value: Amount or percentage per consultation
- Calculated in billing

### Schedule Management
- Day-wise availability (0=Sun to 6=Sat)
- Start time, End time
- Slot duration (minutes)
- Max slots per day
- Active/inactive toggle per day

### Leave Management
- Mark leaves by date
- Reason tracking
- Blocks appointment booking on leave dates

### Status Management
- Active: Available for appointments
- Inactive: Not shown in booking
- On Leave: Temporarily unavailable

## Data Model

```sql
doctors (
    id, clinic_id, user_id,
    name, specialization, qualification, registration_no,
    email, phone, avatar,
    experience_years, consultation_fee,
    commission_type, commission_value,
    bio, signature, status,
    created_at, updated_at
)
```

## Doctor-User Link

- Doctors can optionally be linked to a user account (user_id)
- This allows doctors to log in and access the system
- Doctor role grants: view patients, write prescriptions, manage OPD
