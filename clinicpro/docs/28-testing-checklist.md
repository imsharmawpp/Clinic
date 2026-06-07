# 28 — Testing Checklist

## Functional Testing

### Authentication
- [ ] Login with valid credentials
- [ ] Login with invalid credentials
- [ ] Account lockout after 5 failed attempts
- [ ] Locked account cannot login
- [ ] Session expires after 2 hours
- [ ] Logout destroys session
- [ ] Redirect to login when unauthenticated

### Patient Module
- [ ] Register new patient (all fields)
- [ ] Register patient (minimum fields: name, phone)
- [ ] Search patients by name/phone/ID
- [ ] View patient profile
- [ ] Edit patient details
- [ ] Patient ID auto-generation (PT-XXXXXX)
- [ ] Pagination works correctly
- [ ] AJAX search returns JSON

### Appointment Module
- [ ] Book new appointment
- [ ] Select patient via search
- [ ] Doctor slot availability
- [ ] Status changes work (scheduled → waiting → completed)
- [ ] Cancel appointment with reason
- [ ] Token number assignment
- [ ] Date/doctor filtering

### Billing Module
- [ ] Create invoice with line items
- [ ] Discount calculation (fixed/percentage)
- [ ] GST calculation per item
- [ ] Partial payment
- [ ] Full payment
- [ ] Invoice status updates
- [ ] Print invoice layout

### OPD Module
- [ ] Create OPD visit
- [ ] Record vitals
- [ ] Record diagnosis and treatment
- [ ] Link to appointment
- [ ] Follow-up date setting
- [ ] View visit history

### Prescription Module
- [ ] Create prescription with items
- [ ] Medicine search
- [ ] Print prescription
- [ ] Status management

### Lab Module
- [ ] Create lab order
- [ ] Enter results
- [ ] Status workflow

### Reports
- [ ] Revenue report generates
- [ ] Date range filtering works
- [ ] Permission-gated access

## Security Testing

### SQL Injection
- [ ] Test all form inputs with `' OR 1=1 --`
- [ ] Verify prepared statements used everywhere
- [ ] No raw query concatenation

### XSS (Cross-Site Scripting)
- [ ] Input `<script>alert(1)</script>` in all text fields
- [ ] Verify output escaping in all views
- [ ] Check URL parameters for reflected XSS

### CSRF
- [ ] All forms include CSRF token
- [ ] Submitting without token returns 403
- [ ] Token validated on every POST

### Broken Access Control
- [ ] Access pages without login → redirected
- [ ] Access other clinic's data → denied
- [ ] Access without permission → 403
- [ ] Modify clinic_id in request → filtered

### Brute Force
- [ ] Account locks after 5 attempts
- [ ] Locked account shows message with unlock time

### Session Security
- [ ] Cookies are HttpOnly
- [ ] SameSite: Strict
- [ ] Session regenerated on login

### File Upload
- [ ] Upload PHP file → rejected
- [ ] Upload oversized file → rejected
- [ ] Upload allowed types → accepted
- [ ] Uploaded files have random names
