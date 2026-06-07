# 22 — Admin Panel

## Super Admin Features

### Dashboard
- Total clinics/installations
- Total revenue
- License statistics
- Active users

### Manage Clients (Clinics)
- List all clinics
- View clinic details
- Activate/suspend clinics
- View clinic admin contact

### Manage Licenses
- Generate new license keys
- View all licenses
- Activate/deactivate licenses
- Track domain and IP
- Set plan and limits
- Extend expiry

### Manage Plans
- Define plan features
- Set doctor/patient limits
- Module access per plan

### Manage Updates
- Push update notifications
- Version tracking

### View Logs
- Audit trail across all clinics
- Login activity
- Error logs
- Security events

## Access Control

- Only users with `is_super_admin = 1` can access
- Separate section in sidebar
- Additional permission checks in controllers

## Super Admin Menu

```
Super Admin Panel
├── Dashboard (all-clinic stats)
├── Clinics (CRUD)
├── Licenses (generate, manage)
├── Plans (configure)
├── Users (all system users)
├── Audit Logs (global)
└── System Settings
```
