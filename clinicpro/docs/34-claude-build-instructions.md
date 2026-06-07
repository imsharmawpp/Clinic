# 34 — Build Instructions for AI Agents

## Overview

This document provides instructions for AI coding agents (Claude, Cursor, Windsurf, etc.) to generate production-grade code for ClinicPro.

## Architecture Rules

1. **MVC Pattern**: Controllers → Repositories → Database
2. **No Composer**: Pure PHP, no package manager
3. **No Build Step**: No npm, no webpack, no compilation
4. **CDN Libraries**: Bootstrap, jQuery, DataTables from CDN
5. **Multi-Tenant**: Every query must include `clinic_id`
6. **Repository Pattern**: All database access via repository classes
7. **Prepared Statements**: NEVER use raw query concatenation

## Code Generation Requirements

### Controllers Must:
- Call `AuthMiddleware::check()` first
- Call `require_permission()` for the action
- Use `verify_csrf()` on POST requests
- Use `sanitize()` / `sanitize_int()` on all inputs
- Use `clinic_id()` for tenant filtering
- Call `audit()` on create/update/delete
- Use `flash()` for user messages
- Use `redirect()` for navigation
- Use `json_response()` for AJAX endpoints

### Views Must:
- Use `e()` for ALL output escaping
- Use `csrf_field()` in all forms
- Use `ob_start()` / `ob_get_clean()` pattern
- Include `layouts/app.php` at the end
- Set `$pageTitle`, `$content`, `$scripts` variables
- Use Bootstrap 5 classes
- Use `.cp-*` custom classes for consistent styling

### Repositories Must:
- Accept `$clinicId` as first parameter
- Use prepared statements only
- Return arrays (not objects)
- Handle null returns gracefully
- Support pagination (limit/offset)

### Database Queries Must:
- Always filter by `clinic_id`
- Use prepared statements
- Use indexes for frequently searched columns
- Use transactions for multi-table operations

## File Naming

| Type | Pattern | Example |
|------|---------|---------|
| Controller | `{Module}Controller.php` | PatientController.php |
| Repository | `{Module}Repository.php` | PatientRepository.php |
| View | `{module}/{action}.php` | patients/index.php |
| Migration | `{NNN}_{description}.sql` | 001_schema.sql |
| Public | `{module}.php` | patients.php |

## Security Checklist for Generated Code

- [ ] No `echo $_GET['x']` — always `e($_GET['x'])`
- [ ] No `$db->query("... $var ...")` — always prepared statements
- [ ] No direct file includes from user input
- [ ] No `eval()`, `exec()`, `system()`, `shell_exec()`
- [ ] All forms have CSRF token
- [ ] All controllers check authentication
- [ ] All controllers check permissions
- [ ] All mutations create audit log entries

## Important: Never Generate

- Demo/placeholder code
- TODO comments without implementation
- Incomplete error handling
- Hardcoded credentials
- Development-only features in production code
- Console.log statements
- Commented-out code blocks
