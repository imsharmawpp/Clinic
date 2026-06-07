<?php
ob_start();
$pageTitle = 'OPD Visits';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h4 class="fw-700 mb-0">OPD Visits</h4><small class="text-muted"><?= number_format($total) ?> records</small></div>
  <?php if (has_permission('opd','create')): ?>
  <a href="<?= APP_URL ?>/opd.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>New OPD Visit</a>
  <?php endif; ?>
</div>

<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4"><input type="text" name="q" class="form-control" placeholder="Search patient..." value="<?= e($filters['search']) ?>" style="border-radius:10px"></div>
      <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?= e($filters['date']) ?>" style="border-radius:10px"></div>
      <div class="col-md-3"><select name="doctor" class="form-select" style="border-radius:10px">
        <option value="">All Doctors</option>
        <?php foreach ($doctors as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $filters['doctor_id']==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn-cp-primary w-100">Filter</button>
        <a href="<?= APP_URL ?>/opd.php" class="btn-cp-outline">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Follow-up</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($visits)): ?>
        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-clipboard2-x" style="font-size:36px"></i><br>No visits found</td></tr>
        <?php else: ?>
        <?php foreach ($visits as $v): ?>
        <tr>
          <td><?= format_date($v['visit_date']) ?></td>
          <td><div class="fw-600"><?= e($v['patient_name']) ?></div><small class="text-muted"><?= e($v['patient_id']) ?></small></td>
          <td><?= e($v['doctor_name']) ?></td>
          <td><small><?= e(mb_substr($v['diagnosis'] ?? '—', 0, 60)) ?><?= strlen($v['diagnosis']??'')>60?'...':'' ?></small></td>
          <td><?= $v['follow_up_date'] ? '<span class="cp-badge" style="background:#e8f0ff;color:var(--cp-primary)">'.format_date($v['follow_up_date']).'</span>' : '—' ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= APP_URL ?>/opd.php?action=show&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px"><i class="bi bi-eye"></i></a>
              <a href="<?= APP_URL ?>/prescriptions.php?action=create&visit_id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-success" style="border-radius:8px" title="Prescription"><i class="bi bi-capsule"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages['last'] > 1): ?>
  <div class="d-flex justify-content-between p-3 border-top">
    <small class="text-muted">Page <?= $pages['current'] ?> of <?= $pages['last'] ?></small>
    <div class="d-flex gap-1">
      <?php if ($pages['has_prev']): ?><a href="?page=<?= $pages['current']-1 ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">Prev</a><?php endif; ?>
      <?php if ($pages['has_next']): ?><a href="?page=<?= $pages['current']+1 ?>" class="btn btn-sm btn-primary" style="border-radius:8px">Next</a><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
