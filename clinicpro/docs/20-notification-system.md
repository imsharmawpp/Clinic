# 20 — Notification System

## Channels

| Channel | Status | Implementation |
|---------|--------|---------------|
| In-App | Active | Database notifications table |
| Email | Ready | PHP mail() or SMTP |
| SMS | Planned | API integration |
| WhatsApp | Planned | WhatsApp Cloud API |
| Push | Planned | Web Push API |

## Notification Triggers

| Event | Channels | Recipient |
|-------|----------|-----------|
| Appointment Booked | In-App, SMS | Patient, Doctor |
| Appointment Reminder | SMS, WhatsApp | Patient |
| Appointment Cancelled | In-App | Doctor, Receptionist |
| Payment Received | In-App | Accountant |
| Lab Results Ready | In-App, SMS | Patient, Doctor |
| Low Stock Alert | In-App | Pharmacist, Admin |
| Follow-up Due | SMS | Patient |
| Birthday Wish | SMS, WhatsApp | Patient |
| License Expiring | In-App, Email | Admin |

## Data Model

```sql
notifications (
    id, clinic_id, user_id,
    type, title, message, data (JSON),
    is_read, read_at, created_at
)
```

## In-App Notification UI

- Bell icon in topbar with unread count (red dot)
- Dropdown panel showing recent notifications
- Mark as read on click
- Link to related resource
- Auto-dismiss after viewing

## Future Integration Points

### SMS Gateway (India)
- MSG91
- Textlocal
- Twilio

### WhatsApp Business API
- WhatsApp Cloud API (Meta)
- Template messages for reminders

### Email
- SMTP configuration in settings
- Email templates (customizable)
