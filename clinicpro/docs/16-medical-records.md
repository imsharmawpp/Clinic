# 16 — Medical Records

## Features

### Document Types
- X-Ray images
- Lab Reports (uploaded PDFs/images)
- Prescriptions (linked)
- Insurance documents
- Other documents

### Upload Management
- Title/description
- File path storage
- File size tracking
- MIME type validation
- Linked to patient and visit

### Security
- Files stored outside web root (in uploads/ with execution blocked)
- MIME type validation (finfo)
- File size limit: 10MB
- Allowed types: PDF, JPEG, PNG
- Random filename generation

### Patient Document History
- Chronological listing
- Filter by document type
- Preview/download

## Data Model

```sql
medical_documents (
    id, clinic_id, patient_id, visit_id,
    type (xray/lab_report/prescription/insurance/other),
    title, file_path, file_size, mime_type,
    notes, uploaded_by, created_at
)
```

## Upload Flow

```
1. Select document type
2. Choose file
3. Add title and notes
4. Upload (AJAX with progress)
5. Validate MIME type and size
6. Generate random filename
7. Store in uploads/[subdir]/
8. Save metadata in database
```
