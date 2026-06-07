# 17 — Lab Management

## Features

### Test Master
- Test name and short name
- Category (Haematology, Biochemistry, Radiology, etc.)
- Price
- Normal range reference
- Unit of measurement
- Turnaround time

### Lab Orders
- Auto-generated order number (LAB-XXXXXX)
- Link to patient, doctor, visit
- Multiple tests per order
- Total amount calculation
- Order date

### Status Workflow

```
Ordered → Sample Collected → Processing → Completed
                                        → Cancelled
```

### Result Entry
- Per-test result entry
- Reference range display
- Unit display
- Result date
- Individual test status tracking

### Reports
- PDF lab report generation
- Patient-friendly format
- Doctor review

## Data Model

```sql
lab_tests_master (
    id, clinic_id, name, short_name,
    category, price, turnaround,
    normal_range, unit, status
)

lab_orders (
    id, clinic_id, order_no,
    patient_id, doctor_id, visit_id,
    order_date, status, notes, total_amount,
    created_at
)

lab_order_items (
    id, order_id, test_id,
    result, reference, unit,
    status (pending/completed),
    result_date, price
)
```
