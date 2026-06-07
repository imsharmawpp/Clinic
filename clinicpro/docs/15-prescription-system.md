# 15 — Prescription System

## Features

### Prescription Builder
- Auto-generated prescription number (RX-XXXXXX)
- Link to OPD visit (optional)
- Patient and Doctor selection
- Prescription date
- Diagnosis notes
- Follow-up date

### Medicine Items
- Search from medicine database
- Custom medicine name entry
- Dosage (e.g., 500mg)
- Frequency (e.g., 1-0-1, BD, TDS)
- Duration (e.g., 5 days, 1 week)
- Route (oral, topical, IV, etc.)
- Special instructions
- Quantity
- Sort order (drag to reorder)

### Templates
- Save common prescriptions as templates
- Quick-load templates for common conditions

### Print & PDF
- Print-ready prescription layout
- Doctor signature
- Clinic branding (white-label)
- PDF generation

### Status
- Active: Awaiting dispensing
- Dispensed: Medicines given to patient
- Cancelled: Voided

## Data Model

```sql
prescriptions (
    id, clinic_id, prescription_no,
    visit_id, patient_id, doctor_id,
    prescription_date, diagnosis, notes,
    follow_up_date, status,
    created_at, updated_at
)

prescription_items (
    id, prescription_id, medicine_id,
    medicine_name, dosage, frequency,
    duration, route, instructions,
    quantity, sort_order
)
```

## Prescription Format

```
┌──────────────────────────────────────┐
│  [Clinic Logo]   CLINIC NAME         │
│                  Address, Phone       │
├──────────────────────────────────────┤
│  Patient: [Name]    Age: [XX] Sex: [M/F]
│  Date: [DD/MM/YYYY]  Rx No: RX-000001
├──────────────────────────────────────┤
│  Rx                                  │
│  1. Tab. Paracetamol 500mg           │
│     1-0-1 x 5 days (After food)     │
│  2. Cap. Omeprazole 20mg            │
│     1-0-0 x 7 days (Before food)    │
├──────────────────────────────────────┤
│  Diagnosis: [...]                    │
│  Follow-up: [date]                   │
├──────────────────────────────────────┤
│  [Doctor Signature]                  │
│  Dr. [Name], [Qualification]        │
│  Reg No: [XXXXX]                    │
└──────────────────────────────────────┘
```
