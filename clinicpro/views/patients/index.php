<?php
ob_start();
$pageTitle = 'Patients';
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-700 mb-0">Patient Management</h4>
    <small class="text-muted">Total: <?= number_format($total) ?> patients</small>
  </div>
  <?php if (has_permission('patients','create')): ?>
  <a href="<?= APP_URL ?>/patients.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>New Patient</a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Search name, phone, patient ID..." value="<?= e($filters['search']) ?>" style="border-radius:10px">
      </div>
      <div class="col-md-2">
        <select name="gender" class="form-select" style="border-radius:10px">
          <option value="">All Gender</option>
          <option value="male" <?= $filters['gender']==='male'?'selected':'' ?>>Male</option>
          <option value="female" <?= $filters['gender']==='female'?'selected':'' ?>>Female</option>
          <option value="other" <?= $filters['gender']==='other'?'selected':'' ?>>Other</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select" style="border-radius:10px">
          <option value="">All Status</option>
          <option value="active" <?= $filters['status']==='active'?'selected':'' ?>>Active</option>
          <option value="inactive" <?= $filters['status']==='inactive'?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-search me-1"></i>Search</button>
        <a href="<?= APP_URL ?>/patients.php" class="btn-cp-outline">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead>
        <tr>
          <th>Patient ID</th><th>Name</th><th>Age / Gender</th>
          <th>Phone</th><th>Blood Group</th><th>Status</th><th>Registered</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($patients)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted">
          <i class="bi bi-people" style="font-size:36px"></i><br>No patients found
        </td></tr>
        <?php else: ?>
        <?php foreach ($patients as $p): ?>
        <tr>
          <td><strong style="color:var(--cp-primary)"><?= e($p['patient_id']) ?></strong></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div style="width:34px;height:34px;border-radius:10px;background:<?= $p['gender']==='female'?'#ffd6e8':'#d6e8ff' ?>;display:grid;place-items:center;font-weight:700;color:<?= $p['gender']==='female'?'#c2185b':'#1565c0' ?>;flex-shrink:0">
                <?= strtoupper(substr($p['name'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:600"><?= e($p['name']) ?></div>
                <small class="text-muted"><?= e($p['email'] ?? '—') ?></small>
              </div>
            </div>
          </td>
          <td><?= $p['age'] ?? (age_from_dob($p['dob']) ?? '—') ?> yrs / <?= ucfirst($p['gender']) ?></td>
          <td><?= e($p['phone']) ?></td>
          <td><span class="badge bg-secondary bg-opacity-10 text-dark"><?= e($p['blood_group'] ?? '—') ?></span></td>
          <td>
            <span class="cp-badge" style="background:<?= $p['status']==='active'?'#e6faf6':'#ffeaea' ?>;color:<?= $p['status']==='active'?'#0d9e7e':'#dc3545' ?>">
              <?= ucfirst($p['status']) ?>
            </span>
          </td>
          <td><small><?= format_date($p['created_at']) ?></small></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= APP_URL ?>/patients.php?action=show&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px" title="View"><i class="bi bi-eye"></i></a>
              <?php if (has_permission('patients','update')): ?>
              <a href="<?= APP_URL ?>/patients.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px" title="Edit"><i class="bi bi-pencil"></i></a>
              <?php endif; ?>
              <a href="<?= APP_URL ?>/appointments.php?action=create&patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success" style="border-radius:8px" title="Book Appointment"><i class="bi bi-calendar-plus"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pages['last'] > 1): ?>
  <div class="d-flex justify-content-between align-items-center p-3 border-top">
    <small class="text-muted">Page <?= $pages['current'] ?> of <?= $pages['last'] ?></small>
    <div class="d-flex gap-1">
      <?php if ($pages['has_prev']): ?>
      <a href="?page=<?= $pages['current']-1 ?>&q=<?= urlencode($filters['search']) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">Prev</a>
      <?php endif; ?>
      <?php if ($pages['has_next']): ?>
      <a href="?page=<?= $pages['current']+1 ?>&q=<?= urlencode($filters['search']) ?>" class="btn btn-sm btn-primary" style="border-radius:8px">Next</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
