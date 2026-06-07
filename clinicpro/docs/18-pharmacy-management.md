# 18 — Pharmacy Management

## Features

### Medicine Database
- Name, Generic Name
- Category, Type (tablet, capsule, syrup, etc.)
- Unit (mg, ml, etc.)
- Manufacturer
- Barcode, HSN Code
- GST percentage
- Purchase price, Selling price, MRP

### Stock Management
- Batch-wise tracking
- Batch number
- Expiry date per batch
- Quantity per batch
- Purchase and selling price per batch

### Expiry Alerts
- Dashboard alert for medicines expiring within 30 days
- Low stock alerts (quantity < 10)

### Dispensing
- Link to prescription
- Reduce stock on dispense
- Track dispensed quantities

### Inventory Reports
- Stock valuation
- Expiry report
- Low stock report
- Purchase history
- Sales history

## Data Model

```sql
medicines (
    id, clinic_id, name, generic_name,
    category, type, unit, manufacturer,
    barcode, hsn_code, gst_percent,
    purchase_price, selling_price, mrp,
    status, created_at, updated_at
)

medicine_stock (
    id, clinic_id, medicine_id,
    batch_no, expiry_date, quantity,
    purchase_price, selling_price,
    created_at
)
```

## Medicine Types (ENUM)
- tablet
- capsule
- syrup
- injection
- cream
- drops
- inhaler
- other
