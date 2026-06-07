<?php
ob_start();
$pageTitle = 'OPD Visit Detail';
?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/opd.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-grow-1">
    <h4 class="fw-700 mb-0">OPD Visit — <?= format_date($visit['visit_date']) ?></h4>
    <small class="text-muted"><?= e($visit['patient_name']) ?> &bull; <?= e($visit['patient_id']) ?></small>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/prescriptions.php?action=create&visit_id=<?= $visit['id'] ?>&patient_id=<?= $visit['patient_id'] ?>&doctor_id=<?= $visit['doctor_id'] ?>" class="btn-cp-primary"><i class="bi bi-capsule me-2"></i>Write Prescription</a>
    <a href="<?= APP_URL ?>/lab.php?action=create&visit_id=<?= $visit['id'] ?>&patient_id=<?= $visit['patient_id'] ?>&doctor_id=<?= $visit['doctor_id'] ?>" class="btn-cp-outline"><i class="bi bi-eyedropper me-2"></i>Lab Order</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <!-- Vitals -->
    <?php if ($visit['vitals_bp'] || $visit['vitals_pulse'] || $visit['vitals_temp']): ?>
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-heart-pulse me-2 text-danger"></i>Vitals</span></div>
      <div class="cp-card-body">
        <div class="row g-2 text-center">
          <?php
          $vitals = [
            ['BP','vitals_bp','mmHg','bi-heart-fill','#dc3545'],
            ['Pulse','vitals_pulse','bpm','bi-activity','#ff6b35'],
            ['Temp','vitals_temp','°C','bi-thermometer-half','#ffc107'],
            ['SpO2','vitals_spo2','%','bi-lungs','#0dcfb4'],
            ['Weight','vitals_weight','kg','bi-person','#1a6eff'],
            ['Height','vitals_height','cm','bi-rulers','#6f42c1'],
          ];
          foreach ($vitals as $vt):
          if (!$visit[$vt[1]]) continue;
          ?>
          <div class="col-6 col-md-2">
            <div style="background:#f7f9ff;border-radius:12px;padding:12px 8px">
              <i class="<?= $vt[3] ?>" style="font-size:20px;color:<?= $vt[4] ?>"></i>
              <div style="font-weight:700;font-size:18px;margin:4px 0"><?= e($visit[$vt[1]]) ?></div>
              <div style="font-size:11px;color:var(--cp-muted)"><?= $vt[0] ?> (<?= $vt[2] ?>)</div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Clinical -->
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-clipboard2-pulse me-2 text-success"></i>Clinical Notes</span></div>
      <div class="cp-card-body">
        <?php $fields = [
          ['Chief Complaint','chief_complaint'],['Symptoms','symptoms'],
          ['Examination','examination'],['Diagnosis','diagnosis'],
          ['Treatment Plan','treatment_plan'],['Notes','notes']
        ]; ?>
        <?php foreach ($fields as [$label,$key]): if (empty($visit[$key])) continue; ?>
        <div class="mb-3">
          <div class="fw-600 mb-1" style="font-size:12px;color:var(--cp-muted);text-transform:uppercase"><?= $label ?></div>
          <div style="white-space:pre-wrap"><?= e($visit[$key]) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($visit['follow_up_date']): ?>
    <div class="cp-card mb-3" style="border-left:4px solid var(--cp-warning)">
      <div class="cp-card-body d-flex align-items-center gap-3">
        <i class="bi bi-calendar-check" style="font-size:24px;color:var(--cp-warning)"></i>
        <div>
          <div class="fw-700">Follow-up: <?= format_date($visit['follow_up_date']) ?></div>
          <?php if ($visit['follow_up_notes']): ?><div class="text-muted small"><?= e($visit['follow_up_notes']) ?></div><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Prescriptions -->
    <?php if (!empty($prescriptions)): ?>
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-capsule me-2 text-info"></i>Prescriptions</span></div>
      <div class="cp-card-body p-0">
        <?php foreach ($prescriptions as $rx): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <div><div class="fw-600"><?= e($rx['prescription_no']) ?></div><small class="text-muted"><?= format_date($rx['prescription_date']) ?></small></div>
          <a href="<?= APP_URL ?>/prescriptions.php?action=show&id=<?= $rx['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px">View</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Lab Orders -->
    <?php if (!empty($labOrders)): ?>
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-eyedropper me-2 text-warning"></i>Lab Orders</span></div>
      <div class="cp-card-body p-0">
        <?php foreach ($labOrders as $lo): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <div><div class="fw-600"><?= e($lo['order_no']) ?> <small>(<?= $lo['test_count'] ?> tests)</small></div><small class="text-muted"><?= format_date($lo['order_date']) ?></small></div>
          <div class="d-flex align-items-center gap-2">
            <span class="cp-badge badge-<?= $lo['status'] ?>"><?= ucfirst(str_replace('_',' ',$lo['status'])) ?></span>
            <a href="<?= APP_URL ?>/lab.php?action=show&id=<?= $lo['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px">View</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title">Doctor</span></div>
      <div class="cp-card-body">
        <div class="fw-700"><?= e($visit['doctor_name']) ?></div>
        <div class="text-muted"><?= e($visit['specialization']) ?></div>
        <div class="text-muted small"><?= e($visit['qualification']) ?></div>
      </div>
    </div>
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title">Quick Actions</span></div>
      <div class="cp-card-body d-grid gap-2">
        <a href="<?= APP_URL ?>/billing.php?action=create&patient_id=<?= $visit['patient_id'] ?>" class="btn btn-outline-success" style="border-radius:10px"><i class="bi bi-receipt me-2"></i>Create Invoice</a>
        <a href="<?= APP_URL ?>/patients.php?action=show&id=<?= $visit['patient_id'] ?>" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-person me-2"></i>Patient Profile</a>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
