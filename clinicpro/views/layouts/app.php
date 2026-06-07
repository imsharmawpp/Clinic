<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? APP_NAME) ?></title>
<link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/images/favicon.png">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- Custom CSS -->
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/clinicpro.css">

<style>
:root {
  --cp-primary:    #1a6eff;
  --cp-primary-dk: #0d4fc4;
  --cp-secondary:  #0dcfb4;
  --cp-sidebar:    #0b1120;
  --cp-sidebar-w:  260px;
  --cp-accent:     #ff6b35;
  --cp-success:    #20c997;
  --cp-warning:    #ffc107;
  --cp-danger:     #dc3545;
  --cp-body-bg:    #f0f4fb;
  --cp-card-bg:    #ffffff;
  --cp-text:       #1e2d45;
  --cp-muted:      #6b7a99;
  --cp-border:     #e2e8f5;
  --cp-radius:     12px;
  --cp-shadow:     0 2px 20px rgba(26,110,255,.08);
  --cp-font:       'DM Sans', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: var(--cp-font); background: var(--cp-body-bg); color: var(--cp-text); margin: 0; }

/* ── SIDEBAR ─────────────────────────────────────────────── */
.cp-sidebar {
  position: fixed; top: 0; left: 0; bottom: 0;
  width: var(--cp-sidebar-w); background: var(--cp-sidebar);
  overflow-y: auto; z-index: 1000; transition: transform .3s ease;
}
.cp-sidebar-brand {
  display: flex; align-items: center; gap: 12px;
  padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,.07);
}
.cp-sidebar-brand .brand-icon {
  width: 38px; height: 38px; background: var(--cp-primary);
  border-radius: 10px; display: grid; place-items: center;
  font-size: 20px; color: #fff; flex-shrink: 0;
}
.cp-sidebar-brand .brand-name { color: #fff; font-weight: 700; font-size: 17px; line-height: 1.2; }
.cp-sidebar-brand .brand-tagline { color: rgba(255,255,255,.4); font-size: 10px; }

.cp-nav { padding: 12px 0; }
.cp-nav-section { padding: 16px 20px 6px; color: rgba(255,255,255,.3); font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
.cp-nav-item a {
  display: flex; align-items: center; gap: 12px;
  padding: 9px 20px; color: rgba(255,255,255,.65); text-decoration: none;
  font-size: 14px; font-weight: 500; border-radius: 0;
  transition: all .2s; position: relative;
}
.cp-nav-item a:hover, .cp-nav-item a.active {
  color: #fff; background: rgba(255,255,255,.07);
}
.cp-nav-item a.active::before {
  content: ''; position: absolute; left: 0; top: 4px; bottom: 4px;
  width: 3px; background: var(--cp-primary); border-radius: 0 2px 2px 0;
}
.cp-nav-item a i { font-size: 17px; width: 20px; text-align: center; }
.cp-nav-badge {
  margin-left: auto; background: var(--cp-primary);
  color: #fff; font-size: 10px; padding: 1px 7px;
  border-radius: 20px; font-weight: 600;
}

/* ── TOPBAR ──────────────────────────────────────────────── */
.cp-main { margin-left: var(--cp-sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
.cp-topbar {
  position: sticky; top: 0; z-index: 900;
  background: rgba(240,244,251,.92); backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--cp-border);
  padding: 12px 28px; display: flex; align-items: center; gap: 16px;
}
.cp-topbar .page-title { font-size: 20px; font-weight: 700; flex: 1; }
.cp-topbar .topbar-right { display: flex; align-items: center; gap: 12px; }
.cp-topbar .notif-btn {
  position: relative; width: 38px; height: 38px; border: none;
  background: #fff; border-radius: 10px; font-size: 17px; cursor: pointer;
  display: grid; place-items: center; box-shadow: var(--cp-shadow);
  color: var(--cp-text);
}
.cp-topbar .notif-dot {
  position: absolute; top: 6px; right: 6px; width: 8px; height: 8px;
  background: var(--cp-danger); border-radius: 50%;
}
.cp-topbar .user-chip {
  display: flex; align-items: center; gap: 10px; background: #fff;
  border-radius: 12px; padding: 6px 12px 6px 6px; cursor: pointer;
  text-decoration: none; color: var(--cp-text); box-shadow: var(--cp-shadow);
}
.cp-topbar .user-avatar {
  width: 30px; height: 30px; border-radius: 8px; background: var(--cp-primary);
  display: grid; place-items: center; color: #fff; font-weight: 700; font-size: 13px;
}
.cp-topbar .user-name { font-size: 13px; font-weight: 600; }
.cp-topbar .user-role { font-size: 11px; color: var(--cp-muted); }

/* ── CONTENT ─────────────────────────────────────────────── */
.cp-content { flex: 1; padding: 28px; }

/* ── STAT CARDS ──────────────────────────────────────────── */
.stat-card {
  background: var(--cp-card-bg); border-radius: var(--cp-radius);
  padding: 22px 24px; box-shadow: var(--cp-shadow); border: 1px solid var(--cp-border);
  position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 28px rgba(26,110,255,.13); }
.stat-card .stat-icon {
  width: 52px; height: 52px; border-radius: 14px; display: grid;
  place-items: center; font-size: 24px; margin-bottom: 14px;
}
.stat-card .stat-value { font-size: 28px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
.stat-card .stat-label { font-size: 13px; color: var(--cp-muted); font-weight: 500; }
.stat-card .stat-trend { font-size: 12px; margin-top: 8px; }
.stat-card .stat-trend.up   { color: var(--cp-success); }
.stat-card .stat-trend.down { color: var(--cp-danger); }

/* ── CARDS ───────────────────────────────────────────────── */
.cp-card {
  background: var(--cp-card-bg); border-radius: var(--cp-radius);
  border: 1px solid var(--cp-border); box-shadow: var(--cp-shadow); overflow: hidden;
}
.cp-card-header {
  padding: 18px 22px; border-bottom: 1px solid var(--cp-border);
  display: flex; align-items: center; justify-content: space-between;
}
.cp-card-header .cp-card-title { font-weight: 700; font-size: 15px; }
.cp-card-body { padding: 22px; }

/* ── TABLE ───────────────────────────────────────────────── */
.cp-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.cp-table thead th { background: #f7f9ff; padding: 11px 16px; font-size: 12px; font-weight: 600; color: var(--cp-muted); text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid var(--cp-border); }
.cp-table tbody td { padding: 13px 16px; border-bottom: 1px solid var(--cp-border); font-size: 14px; vertical-align: middle; }
.cp-table tbody tr:hover td { background: #f7f9ff; }
.cp-table tbody tr:last-child td { border-bottom: none; }

/* ── BADGES ──────────────────────────────────────────────── */
.badge-scheduled { background: #e8f0ff; color: #1a6eff; }
.badge-confirmed  { background: #e6faf6; color: #0d9e7e; }
.badge-waiting    { background: #fff8e6; color: #cc8800; }
.badge-completed  { background: #e6faf6; color: var(--cp-success); }
.badge-cancelled  { background: #ffeaea; color: var(--cp-danger); }
.badge-pending    { background: #fff8e6; color: #cc8800; }
.badge-paid       { background: #e6faf6; color: var(--cp-success); }
.badge-partial    { background: #e8f0ff; color: var(--cp-primary); }

.cp-badge {
  display: inline-block; padding: 3px 10px; border-radius: 20px;
  font-size: 12px; font-weight: 600;
}

/* ── BUTTONS ─────────────────────────────────────────────── */
.btn-cp-primary { background: var(--cp-primary); color: #fff; border: none; border-radius: 10px; padding: 9px 20px; font-weight: 600; font-size: 14px; transition: all .2s; cursor: pointer; }
.btn-cp-primary:hover { background: var(--cp-primary-dk); color: #fff; transform: translateY(-1px); }
.btn-cp-outline { background: transparent; color: var(--cp-primary); border: 1.5px solid var(--cp-primary); border-radius: 10px; padding: 8px 18px; font-weight: 600; font-size: 14px; transition: all .2s; }
.btn-cp-outline:hover { background: var(--cp-primary); color: #fff; }

/* ── FORMS ───────────────────────────────────────────────── */
.cp-form-group { margin-bottom: 18px; }
.cp-form-group label { font-size: 13px; font-weight: 600; color: var(--cp-text); margin-bottom: 6px; display: block; }
.cp-form-group .form-control, .cp-form-group .form-select {
  border: 1.5px solid var(--cp-border); border-radius: 10px; padding: 10px 14px;
  font-size: 14px; font-family: var(--cp-font); color: var(--cp-text);
  transition: border-color .2s, box-shadow .2s;
}
.cp-form-group .form-control:focus, .cp-form-group .form-select:focus {
  border-color: var(--cp-primary); box-shadow: 0 0 0 3px rgba(26,110,255,.1); outline: none;
}

/* ── FLASH ───────────────────────────────────────────────── */
.flash-container { position: fixed; top: 80px; right: 24px; z-index: 9999; min-width: 300px; }
.flash-alert {
  padding: 14px 18px; border-radius: 12px; margin-bottom: 8px;
  font-size: 14px; font-weight: 500; animation: slideIn .3s ease;
  display: flex; align-items: center; gap: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,.12);
}
.flash-alert.success { background: #e6faf6; color: #0d9e7e; border-left: 4px solid var(--cp-success); }
.flash-alert.error   { background: #ffeaea; color: var(--cp-danger); border-left: 4px solid var(--cp-danger); }
@keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 991px) {
  .cp-sidebar { transform: translateX(-100%); }
  .cp-sidebar.open { transform: translateX(0); }
  .cp-main { margin-left: 0; }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="cp-sidebar" id="sidebar">
  <div class="cp-sidebar-brand">
    <div class="brand-icon"><i class="bi bi-hospital"></i></div>
    <div>
      <div class="brand-name"><?= e(APP_NAME) ?></div>
      <div class="brand-tagline"><?= e(auth()['clinic_name'] ?? '') ?></div>
    </div>
  </div>

  <nav class="cp-nav">
    <div class="cp-nav-section">Main</div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/dashboard.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='dashboard.php'?'active':'' ?>">
        <i class="bi bi-grid-1x2"></i> Dashboard
      </a>
    </div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/appointments.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='appointments.php'?'active':'' ?>">
        <i class="bi bi-calendar2-check"></i> Appointments
      </a>
    </div>

    <div class="cp-nav-section">Clinical</div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/patients.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='patients.php'?'active':'' ?>">
        <i class="bi bi-people"></i> Patients
      </a>
    </div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/opd.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='opd.php'?'active':'' ?>">
        <i class="bi bi-clipboard2-pulse"></i> OPD
      </a>
    </div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/prescriptions.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='prescriptions.php'?'active':'' ?>">
        <i class="bi bi-capsule"></i> Prescriptions
      </a>
    </div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/lab.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='lab.php'?'active':'' ?>">
        <i class="bi bi-eyedropper"></i> Laboratory
      </a>
    </div>

    <div class="cp-nav-section">Operations</div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/billing.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='billing.php'?'active':'' ?>">
        <i class="bi bi-receipt"></i> Billing
      </a>
    </div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/pharmacy.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='pharmacy.php'?'active':'' ?>">
        <i class="bi bi-bag-heart"></i> Pharmacy
      </a>
    </div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/doctors.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='doctors.php'?'active':'' ?>">
        <i class="bi bi-person-badge"></i> Doctors
      </a>
    </div>

    <div class="cp-nav-section">Analytics</div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/reports.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='reports.php'?'active':'' ?>">
        <i class="bi bi-bar-chart-line"></i> Reports
      </a>
    </div>

    <div class="cp-nav-section">System</div>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/settings.php" class="<?= basename($_SERVER['SCRIPT_NAME'])==='settings.php'?'active':'' ?>">
        <i class="bi bi-gear"></i> Settings
      </a>
    </div>
    <?php if (auth()['is_super'] ?? false): ?>
    <div class="cp-nav-item">
      <a href="<?= APP_URL ?>/admin.php">
        <i class="bi bi-shield-lock"></i> Super Admin
      </a>
    </div>
    <?php endif; ?>
    <div class="cp-nav-item" style="margin-top:8px">
      <a href="<?= APP_URL ?>/logout.php" style="color:rgba(255,100,100,.7)">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </div>
  </nav>
</aside>

<!-- MAIN -->
<div class="cp-main">
  <!-- TOPBAR -->
  <header class="cp-topbar">
    <button class="d-lg-none btn btn-sm btn-outline-secondary me-2" onclick="document.getElementById('sidebar').classList.toggle('open')">
      <i class="bi bi-list" style="font-size:18px"></i>
    </button>
    <div class="page-title"><?= e($pageTitle ?? '') ?></div>
    <div class="topbar-right">
      <div style="font-size:13px; color:var(--cp-muted)"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></div>
      <button class="notif-btn"><i class="bi bi-bell"></i><span class="notif-dot"></span></button>
      <a href="<?= APP_URL ?>/settings.php" class="user-chip text-decoration-none">
        <div class="user-avatar"><?= strtoupper(substr(auth()['name']??'A',0,1)) ?></div>
        <div>
          <div class="user-name"><?= e(auth()['name']??'') ?></div>
          <div class="user-role"><?= e(auth()['role']??'') ?></div>
        </div>
      </a>
    </div>
  </header>

  <!-- FLASH MESSAGES -->
  <?php $flashes = get_flash(); if ($flashes): ?>
  <div class="flash-container" id="flashContainer">
    <?php foreach ($flashes as $f): ?>
    <div class="flash-alert <?= e($f['type']) ?>">
      <i class="bi bi-<?= $f['type']==='success'?'check-circle':'exclamation-circle' ?>"></i>
      <?= e($f['message']) ?>
    </div>
    <?php endforeach; ?>
  </div>
  <script>setTimeout(()=>{const c=document.getElementById('flashContainer');if(c)c.remove();},4000);</script>
  <?php endif; ?>

  <!-- PAGE CONTENT -->
  <div class="cp-content">
    <?= $content ?? '' ?>
  </div>
</div>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// CSRF for AJAX
const CSRF = '<?= csrf_token() ?>';
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': CSRF } });

// Global patient search (Select2-like)
function patientSearch(inputEl, hiddenEl, onSelect) {
  let timer;
  inputEl.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      const q = inputEl.value.trim();
      if (q.length < 2) return;
      fetch('<?= APP_URL ?>/patients.php?action=search&q='+encodeURIComponent(q))
        .then(r=>r.json()).then(data => {
          let dd = document.getElementById('patientDropdown');
          if (!dd) { dd = document.createElement('div'); dd.id='patientDropdown'; dd.className='position-absolute bg-white border rounded shadow-sm w-100'; dd.style.zIndex='9999'; dd.style.maxHeight='200px'; dd.style.overflowY='auto'; inputEl.parentElement.style.position='relative'; inputEl.parentElement.appendChild(dd); }
          dd.innerHTML = data.map(p=>`<div class="p-2 border-bottom" style="cursor:pointer;font-size:13px" data-id="${p.id}" data-name="${p.text}"><strong>${p.name}</strong> <span class="text-muted">${p.pid} &bull; ${p.phone}</span></div>`).join('');
          dd.querySelectorAll('div').forEach(el => {
            el.addEventListener('click', () => {
              inputEl.value = el.dataset.name.split(' — ')[0];
              hiddenEl.value = el.dataset.id;
              dd.remove();
              if (onSelect) onSelect(el.dataset.id);
            });
          });
        });
    }, 300);
  });
}
</script>
<?= $scripts ?? '' ?>
</body>
</html>
