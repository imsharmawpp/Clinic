# 23 — White Label System

## Client Branding Elements

| Element | Storage | Usage |
|---------|---------|-------|
| Logo | clinics.logo | Sidebar, login page, invoices |
| Favicon | clinics.favicon | Browser tab |
| Clinic Name | clinics.name | Header, documents |
| Tagline | clinics.tagline | Login page |
| Theme Color | clinics.theme_color | CSS variable override |
| Currency Symbol | clinics.currency_symbol | All monetary displays |

## Theme Customization

```css
:root {
    --cp-primary: [clinic.theme_color];
}
```

- Primary color affects: buttons, links, active states, badges
- Dark sidebar always stays consistent
- Theme color configurable from Settings

## Document Branding

### Invoices
- Clinic logo (top-left)
- Clinic name, address, phone, email
- GSTIN
- Registration number

### Prescriptions
- Clinic logo
- Doctor name and qualification
- Registration number
- Digital signature

### Lab Reports
- Clinic branding
- Lab name/section

### Email Templates
- Customizable templates in Settings
- Clinic logo in header
- Branded footer

## White-Label Deployment

Each client installation:
1. Has their own domain (domain lock)
2. Uploads their own logo/favicon
3. Sets their theme color
4. Configures their clinic details
5. No reference to "ClinicPro" visible to end users (Enterprise plan)

## Settings Page

- Logo upload
- Favicon upload
- Theme color picker
- Clinic details (name, address, phone, etc.)
- Invoice settings (prefix, terms, footer)
- Notification settings
