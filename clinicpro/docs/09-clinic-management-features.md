# 09 — Clinic Management Features

## Dashboard Overview

The dashboard provides at-a-glance clinic operations:

### Statistics Cards
- Today's Appointments (total, completed, waiting)
- Total Active Patients
- Revenue Today / This Month
- Pending Collections

### Quick Actions
- Book New Appointment
- Register Patient
- Create Invoice
- Start OPD Visit

### Today's Appointments Table
- Token number, patient, doctor, time, status
- Direct link to appointment details

### Revenue Chart
- Last 7 days bar chart (Chart.js)
- Daily revenue visualization

### Recent Patients
- Last 5 registered patients with quick links

### Low Stock Alerts
- Medicines with quantity < 10

### Today's Summary
- Appointment status breakdown (visual progress bars)
- Active doctors count

## Core Modules

| Module | Entry Point | Controller |
|--------|-------------|------------|
| Dashboard | dashboard.php | DashboardController |
| Patients | patients.php | PatientController |
| Appointments | appointments.php | AppointmentController |
| OPD | opd.php | OpdController |
| Doctors | doctors.php | DoctorController |
| Billing | billing.php | BillingController |
| Prescriptions | prescriptions.php | PrescriptionController |
| Laboratory | lab.php | LabController |
| Pharmacy | pharmacy.php | PharmacyController |
| Reports | reports.php | ReportsController |
| Settings | settings.php | SettingsController |

## Navigation Structure

### Main
- Dashboard
- Appointments

### Clinical
- Patients
- OPD
- Prescriptions
- Laboratory

### Operations
- Billing
- Pharmacy
- Doctors

### Analytics
- Reports

### System
- Settings
- Super Admin (visible to super admins only)
