# 06 — User Roles & Permissions

## Role Hierarchy

| Role | Scope | Description |
|------|-------|-------------|
| Super Admin | System-wide | Full access, manage all clinics |
| Clinic Admin | Clinic-level | Manage own clinic fully |
| Doctor | Clinical | View patients, write prescriptions, OPD |
| Receptionist | Front desk | Patients, appointments, basic billing |
| Lab Technician | Laboratory | Lab orders, results entry |
| Pharmacist | Pharmacy | Dispense medicines, manage stock |
| Accountant | Finance | Billing, payments, reports |

## Permission Structure

Permissions are stored as `module.action` pairs.

### Modules & Actions

| Module | Actions |
|--------|---------|
| patients | create, read, update, delete, export |
| doctors | create, read, update, delete |
| appointments | create, read, update, cancel |
| billing | create, read, update, print, refund |
| prescriptions | create, read, print |
| opd | create, read, update |
| lab | create, read, results |
| pharmacy | dispense, stock |
| reports | view, export |
| settings | manage |
| users | manage |

## Permission Check Flow

```php
// In controller
require_permission('patients', 'create');

// Helper function
function has_permission(string $module, string $action): bool {
    $perms = $_SESSION['permissions'] ?? [];
    return in_array($module . '.' . $action, $perms, true);
}
```

## Role Assignment

- Roles are clinic-scoped (each clinic has its own role set)
- System roles are created during installation (is_system = 1)
- Custom roles can be created by Clinic Admin
- Permissions are assigned via `role_permissions` junction table

## Default Role Permissions

### Super Admin / Clinic Admin
All permissions granted.

### Doctor
- patients.read, patients.update
- appointments.read
- opd.create, opd.read, opd.update
- prescriptions.create, prescriptions.read, prescriptions.print
- lab.create, lab.read
- reports.view

### Receptionist
- patients.create, patients.read, patients.update
- appointments.create, appointments.read, appointments.update, appointments.cancel
- billing.create, billing.read, billing.print

### Lab Technician
- patients.read
- lab.read, lab.results

### Pharmacist
- patients.read
- prescriptions.read
- pharmacy.dispense, pharmacy.stock

### Accountant
- billing.read, billing.create, billing.print, billing.refund
- reports.view, reports.export
