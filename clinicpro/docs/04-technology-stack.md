# 04 — Technology Stack

## Frontend

| Technology | Version | Purpose |
|-----------|---------|---------|
| HTML5 | - | Markup |
| CSS3 | - | Styling |
| Bootstrap | 5.3.2 | UI Framework |
| JavaScript | ES6+ | Interactivity |
| jQuery | 3.7.1 | DOM & AJAX |
| DataTables | 1.13.6 | Table management |
| Chart.js | 4.4.1 | Charts & graphs |
| Bootstrap Icons | 1.11.3 | Iconography |
| DM Sans | - | Primary font |

## Backend

| Technology | Version | Purpose |
|-----------|---------|---------|
| PHP | 8.3+ | Server language |
| PDO | - | Database abstraction |
| MVC | Custom | Architecture pattern |
| Sessions | Native | Authentication |

## Database

| Technology | Version | Purpose |
|-----------|---------|---------|
| MySQL | 8.0+ | RDBMS |
| InnoDB | - | Storage engine |
| utf8mb4 | - | Character set (emoji support) |

## Server Requirements

| Component | Minimum |
|-----------|---------|
| PHP | 8.1 (recommended 8.3) |
| MySQL | 8.0 |
| Apache | 2.4 with mod_rewrite |
| RAM | 512MB |
| Storage | 1GB |
| SSL | Required for production |

## PHP Extensions Required

- pdo_mysql
- json
- mbstring
- fileinfo
- openssl
- session
- gd (for image processing)

## No External Dependencies

- No Composer required
- No Node.js/npm required
- No build step required
- Pure PHP with CDN-loaded frontend libraries
- Deploy by uploading files

## CDN Libraries Used

```
Bootstrap CSS: cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css
Bootstrap JS:  cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js
jQuery:        code.jquery.com/jquery-3.7.1.min.js
DataTables:    cdn.datatables.net/1.13.6/
Chart.js:      cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js
Icons:         cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/
Google Fonts:  fonts.googleapis.com (DM Sans)
```
