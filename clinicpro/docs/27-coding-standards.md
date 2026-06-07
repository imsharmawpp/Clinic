# 27 — Coding Standards

## PHP Standards

### PSR-12 Compliance
- 4-space indentation
- Opening braces on same line
- Closing braces on own line
- One class per file
- Namespace/class naming follows directory structure

### Type Safety
- Type hints on all method parameters
- Return type declarations
- Strict null checks

### Example
```php
class PatientController {
    private PatientRepository $repo;

    public function __construct() {
        $this->repo = new PatientRepository();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('patients', 'read');
        // ...
    }
}
```

### Security Patterns
```php
// Always sanitize input
$name = sanitize($_POST['name'] ?? '');
$id   = sanitize_int($_GET['id'] ?? 0);

// Always escape output
<?= e($patient['name']) ?>

// Always use prepared statements
$stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);

// Always verify CSRF
verify_csrf();

// Always check permissions
require_permission('module', 'action');
```

## SQL Standards

### Prepared Statements Only
```php
// CORRECT
$stmt = $db->prepare("SELECT * FROM patients WHERE clinic_id = ? AND id = ?");
$stmt->execute([clinic_id(), $id]);

// NEVER
$db->query("SELECT * FROM patients WHERE id = $id"); // SQL Injection!
```

### Indexes
- Primary keys (auto-increment)
- Foreign keys (with ON DELETE actions)
- Composite unique keys for tenant isolation
- Search indexes on frequently queried columns

### Transactions
```php
$db->beginTransaction();
try {
    // multiple operations
    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    throw $e;
}
```

### Foreign Keys
- Always define FK constraints
- Use appropriate ON DELETE: CASCADE, SET NULL, RESTRICT

## Naming Conventions

| Type | Convention | Example |
|------|-----------|---------|
| Classes | PascalCase | PatientController |
| Methods | camelCase | findById() |
| Variables | camelCase | $clinicId |
| Constants | UPPER_SNAKE | APP_NAME |
| DB Tables | snake_case | opd_visits |
| DB Columns | snake_case | patient_id |
| Files | PascalCase for classes | PatientController.php |
| Views | snake_case | patients/index.php |

## Repository Pattern

```php
class PatientRepository {
    private PDO $db;

    public function getAll(int $clinicId, ...): array { }
    public function findById(int $id, int $clinicId): ?array { }
    public function create(array $data): int { }
    public function update(int $id, int $clinicId, array $data): bool { }
    public function count(int $clinicId, ...): int { }
}
```

## Controller Pattern

```php
class PatientController {
    public function index(): void { }    // List
    public function create(): void { }   // Show form
    public function store(): void { }    // Save (POST)
    public function show(int $id): void { }   // View
    public function edit(int $id): void { }   // Edit form
    public function update(int $id): void { } // Update (POST)
}
```
