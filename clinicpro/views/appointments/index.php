<?php
ob_start();
$action = $_GET['action'] ?? 'index';
$pageTitle = 'Appointments';
?>

<?php if ($action === 'create'): ?>
<!-- ── CREATE APPOINTMENT ──────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/appointments.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">Book Appointment</h4><small class="text-muted">Schedule a new patient visit</small></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <form method="POST" action="<?= APP_URL ?>/appointments.php?action=store" id="apptForm">
      <?= csrf_field() ?>
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-person me-2 text-primary"></i>Patient</span></div>
        <div class="cp-card-body">
          <div class="cp-form-group mb-0">
            <label>Search Patient <span class="text-danger">*</span></label>
            <input type="text" id="patientSearch" class="form-control" placeholder="Type name or phone..." autocomplete="off"
              value="<?= isset($_GET['patient_id']) ? '' : '' ?>">
            <input type="hidden" name="patient_id" id="patientId" value="<?= e($_GET['patient_id'] ?? '') ?>" required>
            <small class="text-muted">Type at least 2 characters to search</small>
          </div>
        </div>
      </div>

      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-person-badge me-2 text-success"></i>Doctor & Schedule</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-6 cp-form-group">
              <label>Doctor <span class="text-danger">*</span></label>
              <select name="doctor_id" id="doctorSelect" class="form-select" required onchange="loadSlots()">
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?> — <?= e($d['specialization']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 cp-form-group">
              <label>Date <span class="text-danger">*</span></label>
              <input type="date" name="appointment_date" id="apptDate" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required onchange="loadSlots()">
            </div>
            <div class="col-md-6 cp-form-group mb-0">
              <label>Time Slot <span class="text-danger">*</span></label>
              <select name="appointment_time" id="timeSlot" class="form-select" required>
                <option value="">Select doctor & date first</option>
              </select>
            </div>
            <div class="col-md-6 cp-form-group mb-0">
              <label>Type</label>
              <select name="type" class="form-select">
                <option value="opd">OPD</option>
                <option value="walkin">Walk-in</option>
                <option value="online">Online</option>
                <option value="followup">Follow-up</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-chat-text me-2 text-info"></i>Reason</span></div>
        <div class="cp-card-body cp-form-group mb-0">
          <textarea name="reason" class="form-control" rows="3" placeholder="Chief complaint / reason for visit..."></textarea>
        </div>
      </div>

      <button type="submit" class="btn-cp-primary w-100 py-3">
        <i class="bi bi-calendar-check me-2"></i>Confirm Appointment
      </button>
    </form>
  </div>

  <div class="col-lg-5">
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title">Availability Info</span></div>
      <div class="cp-card-body">
        <div id="slotsInfo" class="text-muted text-center py-4">
          <i class="bi bi-calendar3" style="font-size:36px"></i><br>
          Select doctor and date to see available slots
        </div>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ── APPOINTMENTS INDEX ──────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-700 mb-0">Appointments</h4>
    <small class="text-muted"><?= format_date($filters['date']) ?></small>
  </div>
  <?php if (has_permission('appointments','create')): ?>
  <a href="<?= APP_URL ?>/appointments.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>Book Appointment</a>
  <?php endif; ?>
</div>

<!-- Quick Stats -->
<div class="row g-3 mb-4">
  <?php
  $qstats = [
    ['Total', $stats['total']??0, 'primary', 'calendar3'],
    ['Waiting', $stats['waiting']??0, 'warning', 'hourglass-split'],
    ['Completed', $stats['completed']??0, 'success', 'check-circle'],
    ['Cancelled', $stats['cancelled']??0, 'danger', 'x-circle'],
  ];
  foreach ($qstats as $s):
  ?>
  <div class="col-6 col-md-3">
    <div class="cp-card text-center p-3">
      <i class="bi bi-<?= $s[3] ?>" style="font-size:24px;color:var(--bs-<?= $s[0]==='Total'?'primary':$s[1] ?>)"></i>
      <div style="font-size:22px;font-weight:700;margin:6px 0"><?= $s[1] ?></div>
      <div style="font-size:12px;color:var(--cp-muted)"><?= $s[0] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <input type="date" name="date" class="form-control" value="<?= e($filters['date']) ?>" style="border-radius:10px">
      </div>
      <div class="col-md-3">
        <select name="doctor" class="form-select" style="border-radius:10px">
          <option value="">All Doctors</option>
          <?php foreach ($doctors as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($filters['doctor_id']==$d['id'])?'selected':'' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select" style="border-radius:10px">
          <option value="">All Status</option>
          <?php foreach (['scheduled','confirmed','waiting','in_progress','completed','cancelled','no_show'] as $s): ?>
          <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
        <a href="<?= APP_URL ?>/appointments.php" class="btn-cp-outline">Today</a>
      </div>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead>
        <tr><th>Token</th><th>Appt No</th><th>Patient</th><th>Doctor</th><th>Time</th><th>Type</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($appointments)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted">
          <i class="bi bi-calendar2-x" style="font-size:36px"></i><br>No appointments found
        </td></tr>
        <?php else: ?>
        <?php foreach ($appointments as $a): ?>
        <tr>
          <td><span style="font-weight:700;font-size:16px;color:var(--cp-primary)">#<?= $a['token_no'] ?></span></td>
          <td><small class="text-muted"><?= e($a['appointment_no']) ?></small></td>
          <td>
            <div style="font-weight:600"><?= e($a['patient_name']) ?></div>
            <small class="text-muted"><?= e($a['patient_phone']) ?></small>
          </td>
          <td>
            <?= e($a['doctor_name']) ?><br>
            <small class="text-muted"><?= e($a['specialization']) ?></small>
          </td>
          <td><?= substr($a['appointment_time'],0,5) ?></td>
          <td><span class="badge bg-light text-dark"><?= ucfirst($a['type']) ?></span></td>
          <td>
            <select class="form-select form-select-sm status-changer" data-id="<?= $a['id'] ?>" style="border-radius:8px;font-size:12px;width:140px">
              <?php foreach (['scheduled','confirmed','waiting','in_progress','completed','cancelled','no_show'] as $s): ?>
              <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= APP_URL ?>/opd.php?action=create&appointment_id=<?= $a['id'] ?>&patient_id=<?= $a['patient_id'] ?>&doctor_id=<?= $a['doctor_id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px" title="Start OPD"><i class="bi bi-clipboard2-pulse"></i></a>
              <a href="<?= APP_URL ?>/billing.php?action=create&patient_id=<?= $a['patient_id'] ?>" class="btn btn-sm btn-outline-success" style="border-radius:8px" title="Create Invoice"><i class="bi bi-receipt"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$scripts = <<<'JS'
<script>
// Slot loader
function loadSlots() {
  const doc  = document.getElementById('doctorSelect');
  const date = document.getElementById('apptDate');
  const slot = document.getElementById('timeSlot');
  const info = document.getElementById('slotsInfo');
  if (!doc || !doc.value || !date.value) return;
  slot.innerHTML = '<option>Loading...</option>';
  fetch(`appointments.php?action=slots&doctor_id=${doc.value}&date=${date.value}`)
    .then(r => r.json())
    .then(slots => {
      if (!slots.length) { slot.innerHTML='<option>No slots available</option>'; return; }
      slot.innerHTML = slots.map(s =>
        `<option value="${s.time}" ${s.available?'':'disabled'}>
          ${s.time.substr(0,5)} ${s.available?'✓ Available':'✗ Booked'}
        </option>`
      ).join('');
      const avail = slots.filter(s=>s.available).length;
      if (info) info.innerHTML = `<div class="alert alert-info"><strong>${avail}</strong> slots available</div>`;
    });
}

// Patient search
const ps = document.getElementById('patientSearch');
const ph = document.getElementById('patientId');
if (ps) patientSearch(ps, ph);

// Status changer
document.querySelectorAll('.status-changer').forEach(sel => {
  sel.addEventListener('change', () => {
    const id = sel.dataset.id;
    const status = sel.value;
    const fd = new FormData();
    fd.append('_csrf', CSRF);
    fd.append('id', id);
    fd.append('status', status);
    fetch('appointments.php?action=updateStatus', { method:'POST', body: fd })
      .then(r=>r.json()).then(d => {
        if (d.success) sel.style.borderColor = 'var(--cp-success)';
      });
  });
});
</script>
JS;
include VIEW_PATH . '/layouts/app.php';
?>
