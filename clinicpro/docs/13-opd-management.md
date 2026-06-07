# 13 — OPD Management

## Features

### OPD Visit Recording
- Link to appointment (optional)
- Patient and Doctor selection
- Visit date

### Clinical Data
- Chief Complaint
- Symptoms
- Physical Examination findings
- Diagnosis (with ICD code support)
- Treatment Plan
- Clinical Notes

### Vitals Recording
- Blood Pressure (systolic/diastolic)
- Pulse Rate (bpm)
- Temperature (°F/°C)
- Weight (kg)
- Height (cm)
- SpO2 (%)
- Respiratory Rate

### Follow-up
- Next follow-up date
- Follow-up notes/instructions

### History View
- Complete visit history per patient
- Doctor name and specialization
- Date-wise chronological listing

## Data Model

```sql
opd_visits (
    id, clinic_id, appointment_id,
    patient_id, doctor_id, visit_date,
    chief_complaint, symptoms, examination,
    diagnosis, icd_code, treatment_plan,
    notes, follow_up_date, follow_up_notes,
    vitals_bp, vitals_pulse, vitals_temp,
    vitals_weight, vitals_height, vitals_spo2, vitals_rr,
    created_by, created_at, updated_at
)
```

## Workflow

```
1. Patient arrives for appointment
        ↓
2. Receptionist marks "Waiting"
        ↓
3. Nurse/staff records vitals
        ↓
4. Doctor starts consultation
        ↓
5. Doctor records: symptoms, diagnosis, treatment
        ↓
6. Doctor writes prescription (linked to visit)
        ↓
7. Doctor orders lab tests (if needed)
        ↓
8. Visit marked complete
        ↓
9. Patient proceeds to billing/pharmacy
```
