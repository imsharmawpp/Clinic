<?php
ob_start();
$action = $_GET['action'] ?? 'index';
$pageTitle = 'Prescriptions';
?>

<?php if ($action === 'create'): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/prescriptions.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">Write Prescription</h4></div>
</div>

<form method="POST" action="<?= APP_URL ?>/prescriptions.php?action=store">
  <?= csrf_field() ?>
  <input type="hidden" name="visit_id" value="<?= e($visitId) ?>">

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Patient & Doctor</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-6 cp-form-group">
              <label>Patient <span class="text-danger">*</span></label>
              <input type="text" id="patientSearch" class="form-control" placeholder="Search patient..."
                value="<?= $patient ? e($patient['name'].' — '.$patient['patient_id']) : '' ?>" autocomplete="off">
              <input type="hidden" name="patient_id" id="patientId" value="<?= e($patient['id'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 cp-form-group">
              <label>Doctor <span class="text-danger">*</span></label>
              <select name="doctor_id" class="form-select" required>
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $d['id']==$doctorId?'selected':'' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 cp-form-group mb-0">
              <label>Diagnosis</label>
              <input type="text" name="diagnosis" class="form-control" placeholder="Clinical diagnosis...">
            </div>
          </div>
        </div>
      </div>

      <!-- Medicine rows -->
      <div class="cp-card mb-3">
        <div class="cp-card-header">
          <span class="cp-card-title"><i class="bi bi-capsule me-2 text-success"></i>Medicines</span>
          <button type="button" class="btn-cp-primary" onclick="addMedRow()" style="font-size:12px;padding:6px 14px"><i class="bi bi-plus me-1"></i>Add Medicine</button>
        </div>
        <div class="cp-card-body" id="medsContainer">
          <div class="med-row border rounded p-3 mb-2" style="border-radius:10px!important">
            <div class="row g-2 align-items-end">
              <div class="col-md-5">
                <label style="font-size:12px;font-weight:600">Medicine</label>
                <input type="text" class="form-control med-search" placeholder="Search medicine..." autocomplete="off">
                <input type="hidden" name="medicine_id[]" class="med-id">
                <input type="hidden" name="medicine_name[]" class="med-name">
              </div>
              <div class="col-md-2">
                <label style="font-size:12px;font-weight:600">Dosage</label>
                <input type="text" name="dosage[]" class="form-control" placeholder="500mg">
              </div>
              <div class="col-md-2">
                <label style="font-size:12px;font-weight:600">Frequency</label>
                <select name="frequency[]" class="form-select">
                  <option>OD</option><option>BD</option><option>TDS</option><option>QDS</option>
                  <option>SOS</option><option>Stat</option><option>Weekly</option>
                </select>
              </div>
              <div class="col-md-2">
                <label style="font-size:12px;font-weight:600">Duration</label>
                <input type="text" name="duration[]" class="form-control" placeholder="5 days">
              </div>
              <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger remove-med-row" style="border-radius:8px;width:100%"><i class="bi bi-trash"></i></button>
              </div>
              <div class="col-12">
                <input type="text" name="instructions[]" class="form-control" placeholder="Instructions (after meals, before sleep...)" style="font-size:12px">
                <input type="hidden" name="route[]" value="oral">
                <input type="hidden" name="quantity[]" value="1">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="cp-card mb-3">
        <div class="cp-card-body cp-form-group mb-0">
          <label>Notes / Advice</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="General advice, dietary restrictions..."></textarea>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Follow-up</span></div>
        <div class="cp-card-body cp-form-group mb-0">
          <label>Follow-up Date</label>
          <input type="date" name="follow_up_date" class="form-control">
        </div>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn-cp-primary py-3"><i class="bi bi-file-medical me-2"></i>Save Prescription</button>
      </div>
    </div>
  </div>
</form>

<?php elseif ($action === 'show'): ?>
<!-- PRINT-READY prescription view -->
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/prescriptions.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-grow-1"><h4 class="fw-700 mb-0"><?= e($rx['prescription_no']) ?></h4></div>
  <button onclick="window.print()" class="btn-cp-outline"><i class="bi bi-printer me-2"></i>Print</button>
</div>
<div class="cp-card" id="printable" style="max-width:800px;margin:auto">
  <div class="cp-card-body p-5">
    <!-- Header -->
    <div class="d-flex justify-content-between mb-4 pb-3" style="border-bottom:2px solid var(--cp-primary)">
      <div>
        <h3 style="font-family:'Playfair Display',serif;color:var(--cp-primary)"><?= e($clinic['name']) ?></h3>
        <div class="text-muted small"><?= e($clinic['address']) ?>, <?= e($clinic['city']) ?></div>
        <div class="text-muted small"><?= e($clinic['phone']) ?></div>
      </div>
      <div class="text-end">
        <div class="fw-700 text-primary">PRESCRIPTION</div>
        <div><?= e($rx['prescription_no']) ?></div>
        <div class="text-muted small"><?= format_date($rx['prescription_date']) ?></div>
      </div>
    </div>

    <!-- Doctor -->
    <div class="row mb-4">
      <div class="col-6">
        <div style="font-size:11px;text-transform:uppercase;color:var(--cp-muted);font-weight:600">Doctor</div>
        <div class="fw-700"><?= e($rx['doctor_name']) ?></div>
        <div class="text-muted small"><?= e($rx['specialization']) ?> &bull; <?= e($rx['qualification']) ?></div>
        <?php if ($rx['doctor_reg']): ?><div class="text-muted small">Reg: <?= e($rx['doctor_reg']) ?></div><?php endif; ?>
      </div>
      <div class="col-6">
        <div style="font-size:11px;text-transform:uppercase;color:var(--cp-muted);font-weight:600">Patient</div>
        <div class="fw-700"><?= e($rx['patient_name']) ?></div>
        <div class="text-muted small"><?= e($rx['patient_id']) ?> &bull; <?= age_from_dob($rx['dob']) ?> yrs &bull; <?= ucfirst($rx['gender']) ?></div>
        <div class="text-muted small"><?= e($rx['patient_phone']) ?></div>
      </div>
    </div>

    <?php if ($rx['diagnosis']): ?>
    <div class="mb-4 p-3" style="background:#f0f4ff;border-radius:10px">
      <div style="font-size:11px;text-transform:uppercase;color:var(--cp-muted);font-weight:600">Diagnosis</div>
      <div><?= e($rx['diagnosis']) ?></div>
    </div>
    <?php endif; ?>

    <!-- Rx symbol -->
    <div style="font-size:36px;font-weight:700;color:var(--cp-primary);line-height:1;margin-bottom:16px">℞</div>

    <!-- Medicines -->
    <?php foreach ($rx['items'] as $i => $item): ?>
    <div class="mb-3 p-3" style="border-left:3px solid var(--cp-primary);background:#f7f9ff;border-radius:0 10px 10px 0">
      <div class="d-flex justify-content-between">
        <div class="fw-700"><?= ($i+1) ?>. <?= e($item['medicine_name']) ?> <?= e($item['dosage']) ?></div>
        <div class="text-muted small">Qty: <?= $item['quantity'] ?></div>
      </div>
      <div class="text-muted"><?= e($item['frequency']) ?> <?= $item['duration']?'for '.$item['duration']:'' ?></div>
      <?php if ($item['instructions']): ?><div class="text-muted small"><?= e($item['instructions']) ?></div><?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if ($rx['notes']): ?>
    <div class="mt-4 p-3" style="background:#fff8e6;border-radius:10px">
      <div style="font-size:11px;text-transform:uppercase;color:var(--cp-muted);font-weight:600">Advice</div>
      <div><?= e($rx['notes']) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($rx['follow_up_date']): ?>
    <div class="mt-3 p-3" style="background:#e6faf6;border-radius:10px">
      <i class="bi bi-calendar-check me-2 text-success"></i>
      <strong>Follow-up:</strong> <?= format_date($rx['follow_up_date']) ?>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mt-5 pt-3" style="border-top:1px solid var(--cp-border)">
      <div class="text-center">
        <div style="height:40px"></div>
        <div class="fw-700"><?= e($rx['doctor_name']) ?></div>
        <div class="text-muted small">Signature</div>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- INDEX -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h4 class="fw-700 mb-0">Prescriptions</h4><small class="text-muted"><?= number_format($total) ?> records</small></div>
  <?php if (has_permission('prescriptions','create')): ?>
  <a href="<?= APP_URL ?>/prescriptions.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>Write Prescription</a>
  <?php endif; ?>
</div>

<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="q" class="form-control" placeholder="Search patient or Rx number..." value="<?= e($search) ?>" style="border-radius:10px;max-width:300px">
      <button type="submit" class="btn-cp-primary">Search</button>
      <a href="<?= APP_URL ?>/prescriptions.php" class="btn-cp-outline">Clear</a>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Rx No</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Diagnosis</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($prescriptions)): ?>
        <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-capsule" style="font-size:36px"></i><br>No prescriptions found</td></tr>
        <?php else: ?>
        <?php foreach ($prescriptions as $rx): ?>
        <tr>
          <td><strong style="color:var(--cp-primary)"><?= e($rx['prescription_no']) ?></strong></td>
          <td><div class="fw-600"><?= e($rx['patient_name']) ?></div><small class="text-muted"><?= e($rx['patient_id']) ?></small></td>
          <td><?= e($rx['doctor_name']) ?></td>
          <td><?= format_date($rx['prescription_date']) ?></td>
          <td><small><?= e(mb_substr($rx['diagnosis']??'—',0,50)) ?></small></td>
          <td><span class="cp-badge" style="background:#e6faf6;color:var(--cp-success)"><?= ucfirst($rx['status']) ?></span></td>
          <td>
            <a href="<?= APP_URL ?>/prescriptions.php?action=show&id=<?= $rx['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px"><i class="bi bi-eye"></i></a>
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
const ps = document.getElementById('patientSearch');
const ph = document.getElementById('patientId');
if (ps) patientSearch(ps, ph);

function addMedRow() {
  const c = document.getElementById('medsContainer');
  const first = c.querySelector('.med-row');
  const clone = first.cloneNode(true);
  clone.querySelectorAll('input').forEach(i => { i.value=''; });
  c.appendChild(clone);
  bindMedSearch(clone.querySelector('.med-search'), clone.querySelector('.med-id'), clone.querySelector('.med-name'));
}

function bindMedSearch(inp, idField, nameField) {
  if (!inp) return;
  let timer;
  inp.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      const q = inp.value.trim();
      if (q.length < 2) return;
      fetch(`pharmacy.php?action=search&q=${encodeURIComponent(q)}`)
        .then(r=>r.json()).then(data => {
          let dd = inp.parentElement.querySelector('.med-dropdown');
          if (!dd) { dd=document.createElement('div'); dd.className='med-dropdown position-absolute bg-white border rounded shadow-sm'; dd.style.zIndex='9999'; dd.style.maxHeight='180px'; dd.style.overflowY='auto'; dd.style.minWidth='250px'; inp.parentElement.style.position='relative'; inp.parentElement.appendChild(dd); }
          dd.innerHTML = data.map(m=>`<div class="p-2 border-bottom" style="cursor:pointer;font-size:13px" data-id="${m.id}" data-name="${m.name}">${m.name} <span class="text-muted">${m.type}</span></div>`).join('');
          dd.querySelectorAll('div').forEach(el => {
            el.addEventListener('click', () => {
              inp.value = el.dataset.name; idField.value = el.dataset.id; nameField.value = el.dataset.name; dd.remove();
            });
          });
        });
    }, 250);
  });
}

document.querySelectorAll('.med-search').forEach((inp,i) => {
  const row = inp.closest('.med-row');
  bindMedSearch(inp, row.querySelector('.med-id'), row.querySelector('.med-name'));
});

document.addEventListener('click', e => {
  if (e.target.closest('.remove-med-row')) {
    const rows = document.querySelectorAll('.med-row');
    if (rows.length > 1) e.target.closest('.med-row').remove();
  }
});
</script>
JS;
include VIEW_PATH . '/layouts/app.php';
?>
