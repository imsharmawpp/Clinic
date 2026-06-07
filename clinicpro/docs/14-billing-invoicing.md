# 14 — Billing & Invoicing

## Features

### Invoice Generation
- Auto-generated invoice number (INV-XXXXXX)
- Link to patient, doctor, visit
- Invoice date and due date
- Multiple line items

### Line Items
- Item types: Consultation, Procedure, Medicine, Lab, Other
- Description, quantity, unit price
- Per-item discount
- GST percentage and amount
- Line total calculation

### Billing Calculations
- Subtotal (sum of line items)
- Discount (fixed or percentage)
- Tax/GST calculation
- Total amount
- Paid amount tracking
- Balance amount

### Payment Management
- Multiple payment methods: Cash, Card, UPI, Net Banking, Cheque, Insurance
- Partial payments supported
- Payment reference number
- Payment date tracking
- Auto-generated payment number (PAY-XXXXXX)

### Invoice Status Workflow

```
Draft → Pending → Partial → Paid
                          → Cancelled
                          → Refunded
```

### Printing & Export
- Print-ready invoice layout
- PDF export
- GST-compliant format

## Data Model

```sql
invoices (
    id, clinic_id, invoice_no,
    patient_id, doctor_id, visit_id,
    invoice_date, due_date,
    subtotal, discount_type, discount_value, discount_amount,
    tax_amount, total_amount, paid_amount, balance_amount,
    status, notes, created_by,
    created_at, updated_at
)

invoice_items (
    id, invoice_id, item_type,
    description, quantity, unit_price,
    discount, gst_percent, gst_amount, total,
    sort_order
)

payments (
    id, clinic_id, invoice_id, payment_no,
    amount, method, reference_no,
    payment_date, notes, created_by,
    created_at
)
```

## GST Compliance

- GSTIN stored at clinic level
- Per-item GST percentage
- GST amount calculated per item
- HSN codes for medicines
- GST-compliant invoice format
