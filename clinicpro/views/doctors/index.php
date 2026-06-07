<?php
ob_start();
$action    = $_GET['action'] ?? 'index';
$pageTitle = 'Doctors';
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>

<?php if (in_array($action, ['create','edit'])): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/doctors.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0"><?= $doctor ? 'Edit Doctor' : 'Add Doctor' ?></h4></div>
</div>

<?php
$formAction = $doctor ? APP_URL.'/doctors.php?action=update&id='.$doctor['id'] : APP_URL.'/doctors.php?action=store';
$existSched = [];
if (!empty($existingSchedules)) {
    foreach ($existingSchedules as $s) $existSched[$s['day_of_week']] = $s;
}
?>
<form method="POST" action="<?= $formAction ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Doctor Information</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-6 cp-form-group"><label>Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= e($doctor['name'] ?? '') ?>" required></div>
            <div class="col-md-6 cp-form-group"><label>Specialization</label><input type="text" name="specialization" class="form-control" value="<?= e($doctor['specialization'] ?? '') ?>"></div>
            <div class="col-md-6 cp-form-group"><label>Qualification</label><input type="text" name="qualification" class="form-control" value="<?= e($doctor['qualification'] ?? '') ?>" placeholder="MBBS, MD..."></div>
            <div class="col-md-6 cp-form-group"><label>Registration No</label><input type="text" name="registration_no" class="form-control" value="<?= e($doctor['registration_no'] ?? '') ?>"></div>
            <div class="col-md-6 cp-form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= e($doctor['email'] ?? '') ?>"></div>
            <div class="col-md-6 cp-form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($doctor['phone'] ?? '') ?>"></div>
            <div class="col-md-4 cp-form-group"><label>Experience (years)</label><input type="number" name="experience_years" class="form-control" value="<?= e($doctor['experience_years'] ?? 0) ?>" min="0"></div>
            <div class="col-md-4 cp-form-group"><label>Consultation Fee (₹)</label><input type="number" name="consultation_fee" class="form-control" value="<?= e($doctor['consultation_fee'] ?? 0) ?>" step="0.01"></div>
            <div class="col-md-4 cp-form-group"><label>Status</label><select name="status" class="form-select">
              <option value="active" <?= ($doctor['status']??'')==='active'?'selected':'' ?>>Active</option>
              <option value="inactive" <?= ($doctor['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
              <option value="on_leave" <?= ($doctor['status']??'')==='on_leave'?'selected':'' ?>>On Leave</option>
            </select></div>
            <div class="col-12 cp-form-group mb-0"><label>Bio</label><textarea name="bio" class="form-control" rows="2"><?= e($doctor['bio'] ?? '') ?></textarea></div>
          </div>
        </div>
      </div>

      <!-- Schedule -->
      <div class="cp-card">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-clock me-2 text-primary"></i>Working Schedule</span></div>
        <div class="cp-card-body">
          <?php foreach ($days as $dow => $dayName): ?>
          <?php $s = $existSched[$dow] ?? null; ?>
          <div class="d-flex align-items-center gap-3 mb-2 p-2" style="border:1px solid var(--cp-border);border-radius:10px">
            <div style="width:110px">
              <label class="d-flex align-items-center gap-2" style="cursor:pointer">
                <input type="checkbox" name="schedule[<?= $dow ?>][enabled]" value="1" <?= $s?'checked':'' ?>>
                <span style="font-weight:600;font-size:13px"><?= $dayName ?></span>
              </label>
            </div>
            <input type="time" name="schedule[<?= $dow ?>][start]" class="form-control form-control-sm" value="<?= $s['start_time'] ?? '09:00' ?>" style="width:110px;border-radius:8px">
            <span class="text-muted small">to</span>
            <input type="time" name="schedule[<?= $dow ?>][end]" class="form-control form-control-sm" value="<?= $s['end_time'] ?? '17:00' ?>" style="width:110px;border-radius:8px">
            <select name="schedule[<?= $dow ?>][slot]" class="form-select form-select-sm" style="width:100px;border-radius:8px">
              <?php foreach ([10,15,20,30] as $min): ?>
              <option value="<?= $min ?>" <?= ($s['slot_mins']??15)===$min?'selected':'' ?>><?= $min ?> min</option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="d-grid gap-2">
        <button type="submit" class="btn-cp-primary py-3"><i class="bi bi-check-circle me-2"></i><?= $doctor ? 'Update Doctor' : 'Add Doctor' ?></button>
        <a href="<?= APP_URL ?>/doctors.php" class="btn-cp-outline text-center text-decoration-none">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php elseif ($action === 'show'): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/doctors.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-grow-1"><h4 class="fw-700 mb-0"><?= e($doctor['name']) ?></h4><small class="text-muted"><?= e($doctor['specialization']) ?> &bull; <?= e($doctor['qualification']) ?></small></div>
  <a href="<?= APP_URL ?>/doctors.php?action=edit&id=<?= $doctor['id'] ?>" class="btn-cp-outline"><i class="bi bi-pencil me-2"></i>Edit</a>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="cp-card mb-3">
      <div class="cp-card-body text-center py-4">
        <div style="width:70px;height:70px;border-radius:18px;background:#d6e8ff;display:grid;place-items:center;font-size:28px;font-weight:700;color:#1565c0;margin:0 auto 14px"><?= strtoupper(substr($doctor['name'],0,1)) ?></div>
        <h5 class="fw-700"><?= e($doctor['name']) ?></h5>
        <div class="text-muted"><?= e($doctor['specialization']) ?></div>
        <div class="text-muted small"><?= e($doctor['qualification']) ?></div>
        <div class="mt-2 d-flex justify-content-center gap-2">
          <span class="cp-badge" style="background:<?= $doctor['status']==='active'?'#e6faf6':'#ffeaea' ?>;color:<?= $doctor['status']==='active'?'var(--cp-success)':'var(--cp-danger)' ?>"><?= ucfirst($doctor['status']) ?></span>
        </div>
      </div>
    </div>
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title">Contact</span></div>
      <div class="cp-card-body">
        <?php if ($doctor['phone']): ?><div class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i><?= e($doctor['phone']) ?></div><?php endif; ?>
        <?php if ($doctor['email']): ?><div class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= e($doctor['email']) ?></div><?php endif; ?>
        <div><i class="bi bi-briefcase me-2 text-primary"></i><?= e($doctor['experience_years']) ?> years experience</div>
      </div>
    </div>
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title">Stats</span></div>
      <div class="cp-card-body">
        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Revenue</span><strong><?= currency($stats['revenue'] ?? 0) ?></strong></div>
        <div class="d-flex justify-content-between"><span class="text-muted">Consultation Fee</span><strong><?= currency($doctor['consultation_fee']) ?></strong></div>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <!-- Schedule -->
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-clock me-2 text-primary"></i>Schedule</span></div>
      <div class="cp-card-body">
        <?php if (empty($schedules)): ?>
        <p class="text-muted">No schedule configured.</p>
        <?php else: ?>
        <?php foreach ($schedules as $s): ?>
        <div class="d-flex align-items-center gap-3 mb-2 p-2" style="background:#f7f9ff;border-radius:10px">
          <span style="font-weight:600;width:100px"><?= $days[$s['day_of_week']] ?></span>
          <span><?= substr($s['start_time'],0,5) ?> — <?= substr($s['end_time'],0,5) ?></span>
          <span class="text-muted small"><?= $s['slot_mins'] ?> min slots</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <!-- Recent Appointments -->
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title">Recent Appointments</span></div>
      <div style="overflow-x:auto">
        <table class="cp-table">
          <thead><tr><th>Date</th><th>Patient</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($appointments)): ?>
            <tr><td colspan="3" class="text-center py-4 text-muted">No appointments</td></tr>
            <?php else: ?>
            <?php foreach ($appointments as $a): ?>
            <tr>
              <td><?= format_date($a['appointment_date']) ?></td>
              <td><?= e($a['patient_name']) ?></td>
              <td><span class="cp-badge badge-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- INDEX -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h4 class="fw-700 mb-0">Doctors</h4><small class="text-muted"><?= count($doctors) ?> registered</small></div>
  <?php if (has_permission('doctors','create')): ?>
  <a href="<?= APP_URL ?>/doctors.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>Add Doctor</a>
  <?php endif; ?>
</div>
<div class="row g-3">
  <?php if (empty($doctors)): ?>
  <div class="col-12"><div class="cp-card"><div class="cp-card-body text-center py-5 text-muted"><i class="bi bi-person-badge" style="font-size:48px"></i><p class="mt-2">No doctors yet</p><a href="<?= APP_URL ?>/doctors.php?action=create" class="btn-cp-primary">Add First Doctor</a></div></div></div>
  <?php else: ?>
  <?php foreach ($doctors as $d): ?>
  <div class="col-md-6 col-lg-4">
    <div class="cp-card h-100">
      <div class="cp-card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div style="width:52px;height:52px;border-radius:14px;background:#d6e8ff;display:grid;place-items:center;font-size:22px;font-weight:700;color:#1565c0;flex-shrink:0"><?= strtoupper(substr($d['name'],0,1)) ?></div>
          <div>
            <div class="fw-700"><?= e($d['name']) ?></div>
            <div class="text-muted small"><?= e($d['specialization'] ?? '—') ?></div>
          </div>
          <div class="ms-auto">
            <span class="cp-badge" style="background:<?= $d['status']==='active'?'#e6faf6':'#ffeaea' ?>;color:<?= $d['status']==='active'?'var(--cp-success)':'var(--cp-danger)' ?>"><?= ucfirst($d['status']) ?></span>
          </div>
        </div>
        <div class="d-flex justify-content-around text-center mb-3">
          <div><div class="fw-700 text-primary"><?= number_format($d['total_appointments']) ?></div><div style="font-size:11px;color:var(--cp-muted)">Appointments</div></div>
          <div><div class="fw-700 text-success"><?= number_format($d['total_visits']) ?></div><div style="font-size:11px;color:var(--cp-muted)">Visits</div></div>
          <div><div class="fw-700"><?= currency($d['consultation_fee']) ?></div><div style="font-size:11px;color:var(--cp-muted)">Fee</div></div>
        </div>
        <div class="d-flex gap-2">
          <a href="<?= APP_URL ?>/doctors.php?action=show&id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary flex-grow-1" style="border-radius:8px">View</a>
          <?php if (has_permission('doctors','update')): ?>
          <a href="<?= APP_URL ?>/doctors.php?action=edit&id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="bi bi-pencil"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
