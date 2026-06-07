<?php
ob_start();
$pageTitle = 'Patient: ' . $patient['name'];
?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/patients.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-grow-1">
    <h4 class="fw-700 mb-0"><?= e($patient['name']) ?></h4>
    <small class="text-muted"><?= e($patient['patient_id']) ?> &bull; Registered <?= format_date($patient['created_at']) ?></small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/appointments.php?action=create&patient_id=<?= $patient['id'] ?>" class="btn-cp-primary"><i class="bi bi-calendar-plus me-2"></i>Book Appointment</a>
    <?php if (has_permission('patients','update')): ?>
    <a href="<?= APP_URL ?>/patients.php?action=edit&id=<?= $patient['id'] ?>" class="btn-cp-outline"><i class="bi bi-pencil me-2"></i>Edit</a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <!-- Profile Card -->
  <div class="col-lg-4">
    <div class="cp-card mb-3">
      <div class="cp-card-body text-center py-4">
        <div style="width:70px;height:70px;border-radius:18px;background:<?= $patient['gender']==='female'?'#ffd6e8':'#d6e8ff' ?>;display:grid;place-items:center;font-size:28px;font-weight:700;color:<?= $patient['gender']==='female'?'#c2185b':'#1565c0' ?>;margin:0 auto 16px">
          <?= strtoupper(substr($patient['name'],0,1)) ?>
        </div>
        <h5 class="fw-700"><?= e($patient['name']) ?></h5>
        <p class="text-muted mb-3"><?= e($patient['patient_id']) ?></p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
          <span class="cp-badge" style="background:#e8f0ff;color:var(--cp-primary)"><?= ucfirst($patient['gender']) ?></span>
          <span class="cp-badge" style="background:#e6faf6;color:var(--cp-success)"><?= e($patient['blood_group']) ?></span>
          <span class="cp-badge" style="background:#fff8e6;color:#cc8800"><?= ($patient['age'] ?? age_from_dob($patient['dob']) ?? '?') ?> yrs</span>
        </div>
      </div>
    </div>

    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title">Contact Info</span></div>
      <div class="cp-card-body">
        <div class="mb-2"><i class="bi bi-telephone me-2 text-primary"></i><?= e($patient['phone']) ?></div>
        <?php if ($patient['email']): ?>
        <div class="mb-2"><i class="bi bi-envelope me-2 text-primary"></i><?= e($patient['email']) ?></div>
        <?php endif; ?>
        <?php if ($patient['address']): ?>
        <div><i class="bi bi-geo-alt me-2 text-primary"></i><?= e($patient['address']) ?>, <?= e($patient['city']) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($patient['emergency_name']): ?>
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-telephone-fill me-2 text-danger"></i>Emergency Contact</span></div>
      <div class="cp-card-body">
        <div class="fw-600"><?= e($patient['emergency_name']) ?></div>
        <div class="text-muted small"><?= e($patient['emergency_relation']) ?></div>
        <div class="mt-1"><i class="bi bi-telephone me-1"></i><?= e($patient['emergency_phone']) ?></div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- History & Records -->
  <div class="col-lg-8">
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" style="border-bottom:2px solid var(--cp-border)">
      <li class="nav-item"><a class="nav-link active fw-600" href="#history" data-bs-toggle="tab">Visit History</a></li>
      <li class="nav-item"><a class="nav-link fw-600" href="#invoices" data-bs-toggle="tab">Invoices</a></li>
    </ul>

    <div class="tab-content">
      <!-- Visit History -->
      <div class="tab-pane active" id="history">
        <?php if (empty($history)): ?>
        <div class="cp-card">
          <div class="cp-card-body text-center py-5">
            <i class="bi bi-clipboard2-x" style="font-size:40px;color:var(--cp-muted)"></i>
            <p class="mt-2 text-muted">No visit history yet</p>
            <a href="<?= APP_URL ?>/opd.php?action=create&patient_id=<?= $patient['id'] ?>" class="btn-cp-primary">Start OPD Visit</a>
          </div>
        </div>
        <?php else: ?>
        <?php foreach ($history as $v): ?>
        <div class="cp-card mb-3">
          <div class="cp-card-header">
            <div>
              <div class="cp-card-title"><?= format_date($v['visit_date']) ?></div>
              <small class="text-muted"><?= e($v['doctor_name']) ?> &bull; <?= e($v['specialization']) ?></small>
            </div>
            <a href="<?= APP_URL ?>/opd.php?action=show&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px">View</a>
          </div>
          <div class="cp-card-body">
            <?php if ($v['diagnosis']): ?>
            <div class="mb-2"><strong>Diagnosis:</strong> <?= e($v['diagnosis']) ?></div>
            <?php endif; ?>
            <?php if ($v['chief_complaint']): ?>
            <div class="text-muted small"><?= e($v['chief_complaint']) ?></div>
            <?php endif; ?>
            <?php if ($v['follow_up_date']): ?>
            <div class="mt-2"><span class="cp-badge" style="background:#e8f0ff;color:var(--cp-primary)"><i class="bi bi-calendar me-1"></i>Follow-up: <?= format_date($v['follow_up_date']) ?></span></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Invoices tab -->
      <div class="tab-pane" id="invoices">
        <div class="cp-card">
          <div class="cp-card-body text-center py-4">
            <a href="<?= APP_URL ?>/billing.php?patient_id=<?= $patient['id'] ?>" class="btn-cp-primary">View All Invoices</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
