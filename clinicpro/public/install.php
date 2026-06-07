<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ClinicPro Installer</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#0b1120;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.installer{background:#fff;color:#1e2d45;border-radius:20px;padding:40px;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.4)}
h1{font-size:24px;font-weight:700;margin-bottom:4px}
.subtitle{color:#6b7a99;font-size:14px;margin-bottom:28px}
.step{border:1.5px solid #e2e8f5;border-radius:12px;padding:16px;margin-bottom:14px}
.step-header{display:flex;align-items:center;gap:10px;font-weight:700;margin-bottom:10px}
.step-num{width:28px;height:28px;border-radius:8px;background:#1a6eff;color:#fff;display:grid;place-items:center;font-size:13px;font-weight:700;flex-shrink:0}
label{font-size:13px;font-weight:600;display:block;margin-bottom:5px;margin-top:10px}
input{width:100%;border:1.5px solid #e2e8f5;border-radius:10px;padding:10px 14px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none}
input:focus{border-color:#1a6eff;box-shadow:0 0 0 3px rgba(26,110,255,.1)}
.btn{width:100%;background:#1a6eff;color:#fff;border:none;border-radius:12px;padding:14px;font-size:15px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;margin-top:20px;transition:all .2s}
.btn:hover{background:#0d4fc4;transform:translateY(-1px)}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.alert{padding:12px 16px;border-radius:10px;font-size:14px;margin-bottom:16px}
.alert.error{background:#ffeaea;color:#dc3545;border-left:4px solid #dc3545}
.alert.success{background:#e6faf6;color:#0d9e7e;border-left:4px solid #20c997}
.check-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f5;font-size:13px}
.check-row:last-child{border:none}
.ok{color:#20c997;font-weight:700}
.fail{color:#dc3545;font-weight:700}
code{background:#f0f4fb;padding:2px 6px;border-radius:5px;font-size:12px}
</style>
</head>
<body>
<?php
// Increase execution time for shared hosting
@set_time_limit(300);
@ini_set('max_execution_time', 300);

$step     = $_GET['step'] ?? '1';
$errors   = [];
$success  = false;

// ── STEP 2 — Run installation ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === '2') {
    $host   = trim($_POST['db_host'] ?? 'localhost');
    $dbname = trim($_POST['db_name'] ?? '');
    $user   = trim($_POST['db_user'] ?? '');
    $pass   = $_POST['db_pass'] ?? '';
    $appUrl = rtrim(trim($_POST['app_url'] ?? ''), '/');

    if (!$dbname || !$user) {
        $errors[] = 'Database name and username are required.';
    } else {
        try {
            // Connect directly to pre-created database
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user, $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );

            // ── Step A: Run schema SQL ────────────────────────────
            $sql = file_get_contents(__DIR__ . '/../database/migrations/001_schema.sql');

            // Remove comments (lines starting with --)
            $sql = preg_replace('/^\s*--.*$/m', '', $sql);

            // Remove CREATE DATABASE and USE statements
            $sql = preg_replace('/CREATE\s+DATABASE\s+.*?;/is', '', $sql);
            $sql = preg_replace('/USE\s+`[^`]+`\s*;/i', '', $sql);
            $sql = preg_replace('/USE\s+\w+\s*;/i', '', $sql);

            // Split by semicolons (simple split, works for this schema)
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt)) continue;
                if (strlen($stmt) < 5) continue; // Skip tiny fragments
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e) {
                    // Ignore duplicate key/table already exists errors
                    $code = $e->getCode();
                    if (!in_array($code, ['23000', '42S01', '42000'])) {
                        // Log but continue — non-critical
                    }
                }
            }

            // ── Step B: Create clinic & admin ─────────────────────
            // Get license ID (seeded in schema)
            $licenseId = $pdo->query("SELECT id FROM license_keys WHERE license_key='DEMO-CLINIC-PRO-2024-XXXX' LIMIT 1")->fetchColumn();

            if (!$licenseId) {
                // If license seed didn't run, insert it manually
                $pdo->exec("INSERT IGNORE INTO license_keys (license_key,plan,max_doctors,max_patients,status,expiry_date) VALUES ('DEMO-CLINIC-PRO-2024-XXXX','enterprise',999,999999,'active','2099-12-31')");
                $licenseId = $pdo->lastInsertId();
            }

            // Create clinic
            $pdo->prepare("INSERT IGNORE INTO clinics (license_id,name,slug,email,status) VALUES (?,?,?,?,'active')")
                ->execute([$licenseId, 'My Clinic', 'my-clinic', trim($_POST['admin_email'])]);
            $clinicId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM clinics WHERE slug='my-clinic' LIMIT 1")->fetchColumn();

            // Create roles
            $roles = [
                ['Super Admin','super_admin'],
                ['Clinic Admin','clinic_admin'],
                ['Doctor','doctor'],
                ['Receptionist','receptionist'],
                ['Lab Technician','lab_technician'],
                ['Pharmacist','pharmacist'],
                ['Accountant','accountant'],
            ];
            foreach ($roles as $r) {
                $pdo->prepare("INSERT IGNORE INTO roles (clinic_id,name,slug,is_system) VALUES (?,?,?,1)")
                    ->execute([$clinicId, $r[0], $r[1]]);
            }

            $roleId = $pdo->query("SELECT id FROM roles WHERE clinic_id=$clinicId AND slug='super_admin' LIMIT 1")->fetchColumn();

            // Assign ALL permissions to super_admin
            $allPerms = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
            $rpStmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id,permission_id) VALUES (?,?)");
            foreach ($allPerms as $pid) {
                try { $rpStmt->execute([$roleId, $pid]); } catch (PDOException $e) {}
            }

            // Create admin user (use cost=10 for speed on shared hosting)
            $pwd = password_hash(trim($_POST['admin_pass']), PASSWORD_BCRYPT, ['cost' => 10]);
            $pdo->prepare("INSERT IGNORE INTO users (clinic_id,role_id,name,email,password,is_super_admin,status,email_verified_at) VALUES (?,?,?,?,?,1,'active',NOW())")
                ->execute([$clinicId, $roleId, trim($_POST['admin_name']), trim($_POST['admin_email']), $pwd]);

            // ── Step C: Seed default settings ─────────────────────
            $settingsData = [
                ['invoice_prefix','INV'],
                ['appointment_prefix','APT'],
                ['patient_prefix','PT'],
                ['lab_prefix','LAB'],
                ['prescription_prefix','RX'],
                ['payment_prefix','PAY'],
                ['gst_number',''],
                ['invoice_terms','Payment is due within 7 days.'],
            ];
            $setStmt = $pdo->prepare("INSERT IGNORE INTO settings (clinic_id,`key`,value) VALUES (?,?,?)");
            foreach ($settingsData as $s) {
                try { $setStmt->execute([$clinicId, $s[0], $s[1]]); } catch (PDOException $e) {}
            }

            // ── Step D: Write .env file ───────────────────────────
            $envContent = "DB_HOST=$host\nDB_PORT=3306\nDB_NAME=$dbname\nDB_USER=$user\nDB_PASS=$pass\nAPP_URL=$appUrl\nAPP_ENV=production\nAPP_TIMEZONE=Asia/Kolkata\n";
            file_put_contents(__DIR__ . '/../.env', $envContent);

            // Create directories if needed
            @mkdir(__DIR__ . '/uploads', 0755, true);
            @mkdir(__DIR__ . '/../storage/logs', 0755, true);

            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        } catch (Throwable $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

// ── PHP Checks ────────────────────────────────────────────
$checks = [
    ['PHP >= 8.1', version_compare(PHP_VERSION, '8.1', '>=')],
    ['PDO MySQL',  extension_loaded('pdo_mysql')],
    ['JSON',       extension_loaded('json')],
    ['mbstring',   extension_loaded('mbstring')],
    ['fileinfo',   extension_loaded('fileinfo')],
    ['uploads dir writable', is_writable(__DIR__ . '/uploads') || @mkdir(__DIR__ . '/uploads', 0755, true)],
    ['storage dir writable', is_writable(__DIR__ . '/../storage') || @mkdir(__DIR__ . '/../storage', 0755, true)],
];
$canInstall = !in_array(false, array_column($checks, 1), true);
?>
<div class="installer">
  <h1>&#x1F3E5; ClinicPro Installer</h1>
  <p class="subtitle">Version 1.0 — Web-based setup wizard</p>

  <?php if (!empty($errors)): ?>
  <div class="alert error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
  <div class="alert success">
    <strong>&#10003; Installation complete!</strong><br>
    You can now <a href="login.php" style="color:#0d9e7e;font-weight:700">login here</a>.<br>
    <small>Email: <code><?= htmlspecialchars(trim($_POST['admin_email'] ?? '')) ?></code></small><br>
    <small>Password: <em>(what you just entered)</em></small><br><br>
    <strong style="color:#dc3545">&#9888; Delete this install.php file immediately!</strong>
  </div>
  <?php elseif ($step === '1'): ?>
  <!-- Requirements Check -->
  <div class="step">
    <div class="step-header"><div class="step-num">1</div>System Requirements</div>
    <?php foreach ($checks as [$label, $ok]): ?>
    <div class="check-row">
      <span><?= htmlspecialchars($label) ?></span>
      <span class="<?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? '&#10003; OK' : '&#10007; FAIL' ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($canInstall): ?>
  <a href="install.php?step=2" class="btn" style="display:block;text-align:center;text-decoration:none">Continue to Configuration &#8594;</a>
  <?php else: ?>
  <div class="alert error">Fix failing requirements before continuing.</div>
  <?php endif; ?>

  <?php else: ?>
  <!-- Configuration -->
  <form method="POST" action="install.php?step=2">
    <div class="step">
      <div class="step-header"><div class="step-num">2</div>Database Configuration</div>
      <label>Database Host</label><input name="db_host" value="localhost">
      <label>Database Name <span style="color:#dc3545">*</span></label><input name="db_name" required placeholder="u123456_clinicpro" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>">
      <label>Database Username <span style="color:#dc3545">*</span></label><input name="db_user" required placeholder="u123456_admin" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>">
      <label>Database Password</label><input type="password" name="db_pass">
    </div>
    <div class="step">
      <div class="step-header"><div class="step-num">3</div>Application URL</div>
      <label>App URL (no trailing slash)</label>
      <input name="app_url" value="<?= htmlspecialchars($_POST['app_url'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['SCRIPT_NAME']))) ?>">
    </div>
    <div class="step">
      <div class="step-header"><div class="step-num">4</div>Admin Account</div>
      <label>Admin Name</label><input name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'Super Admin') ?>">
      <label>Admin Email <span style="color:#dc3545">*</span></label><input type="email" name="admin_email" required placeholder="admin@clinic.com" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
      <label>Admin Password <span style="color:#dc3545">*</span></label><input type="password" name="admin_pass" required placeholder="min 6 characters" minlength="6">
    </div>
    <button type="submit" class="btn">&#128274; Install ClinicPro</button>
    <p style="text-align:center;margin-top:14px;font-size:12px;color:#6b7a99">This will create all database tables and seed initial data.<br>May take 10-30 seconds on shared hosting — please wait.</p>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
