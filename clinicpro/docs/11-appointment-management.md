# 11 — Appointment Management

## Features

### Appointment Booking
- Select patient (AJAX search)
- Select doctor
- Choose date and time slot
- Appointment type: OPD, Online, Walk-in, Follow-up
- Reason/notes
- Auto-generated appointment number (APT-XXXXXX)
- Token number assignment

### Doctor Schedule
- Day-wise schedule (Mon-Sat)
- Start/end time per day
- Slot duration (configurable, default 15 min)
- Max slots per day
- Leave management

### Status Workflow

```
Scheduled → Confirmed → Waiting → In Progress → Completed
                                               → Cancelled
                                               → No Show
```

### Calendar View
- Date-based filtering
- Doctor-based filtering
- Status-based filtering

### Appointment List
- DataTables with search
- Filter by date, doctor, status
- Quick status update (AJAX)
- Today's stats summary

## Data Model

```sql
appointments (
    id, clinic_id, appointment_no,
    patient_id, doctor_id,
    appointment_date, appointment_time, token_no,
    type (opd/online/walkin/followup),
    reason, status, cancelled_reason,
    consulted_at, booked_by, notes,
    created_at, updated_at
)

doctor_schedules (
    id, doctor_id, day_of_week,
    start_time, end_time, slot_mins, max_slots, is_active
)

doctor_leaves (
    id, doctor_id, leave_date, reason, created_at
)
```

## Available Slots API

```
GET appointments.php?action=slots&doctor_id=X&date=YYYY-MM-DD
→ Returns JSON array of available time slots
```

## Status Update API

```
POST appointments.php?action=updateStatus
Body: { id, status, reason }
→ Returns JSON { success: true, status: "completed" }
```
