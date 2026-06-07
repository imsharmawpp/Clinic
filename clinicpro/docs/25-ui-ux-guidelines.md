# 25 — UI/UX Guidelines

## Design Principles

- **Modern** — Clean, minimal, professional
- **Medical Theme** — Blue/teal primary colors, health-oriented icons
- **Responsive** — Works on desktop, tablet, mobile
- **Mobile First** — Sidebar collapses on mobile, touch-friendly
- **Accessible** — Good contrast, readable fonts, clear labels

## Color Palette

| Variable | Color | Usage |
|----------|-------|-------|
| --cp-primary | #1a6eff | Buttons, links, active states |
| --cp-primary-dk | #0d4fc4 | Hover states |
| --cp-secondary | #0dcfb4 | Accents |
| --cp-sidebar | #0b1120 | Sidebar background |
| --cp-success | #20c997 | Success states |
| --cp-warning | #ffc107 | Warning states |
| --cp-danger | #dc3545 | Error/danger states |
| --cp-body-bg | #f0f4fb | Page background |
| --cp-card-bg | #ffffff | Card backgrounds |
| --cp-text | #1e2d45 | Primary text |
| --cp-muted | #6b7a99 | Secondary text |
| --cp-border | #e2e8f5 | Borders |

## Typography

- **Font**: DM Sans (Google Fonts)
- **Headings**: 600-700 weight
- **Body**: 400-500 weight
- **Sizes**: 11px (tiny) → 28px (stat values)

## Component Styles

### Cards (.cp-card)
- White background
- 12px border radius
- Subtle border + shadow
- Header with title + action button
- Hover: slight lift + deeper shadow

### Stat Cards (.stat-card)
- Icon (52px, rounded, colored bg)
- Large value (28px, bold)
- Label (13px, muted)
- Trend indicator (up/down)

### Tables (.cp-table)
- Header: light gray bg, uppercase labels
- Rows: hover highlight
- Responsive horizontal scroll on mobile

### Badges (.cp-badge)
- Rounded pill shape
- Color-coded by status
- 12px font, 600 weight

### Buttons
- .btn-cp-primary: Filled blue, white text
- .btn-cp-outline: Blue border, transparent bg
- 10px border radius
- 600 weight

### Forms (.cp-form-group)
- Labeled inputs
- 10px padding, 10px border radius
- Focus: blue border + blue shadow glow
- Error: red border

## Layout Structure

```
┌─────────────────────────────────────────┐
│ [Sidebar 260px] │ [Main Content]        │
│                 │ ┌─────────────────┐   │
│ Brand           │ │ Topbar          │   │
│ Navigation      │ ├─────────────────┤   │
│                 │ │ Page Content    │   │
│                 │ │                 │   │
│                 │ └─────────────────┘   │
└─────────────────────────────────────────┘
```

## Responsive Breakpoints

- < 992px: Sidebar hidden (toggle button), full-width content
- >= 992px: Sidebar visible, content with left margin

## Flash Messages

- Fixed position (top-right)
- Auto-dismiss after 4 seconds
- Slide-in animation
- Color-coded (green success, red error)
