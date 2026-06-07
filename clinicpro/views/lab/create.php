<?php
ob_start();
$action    = $_GET['action'] ?? 'index';
$pageTitle = 'Laboratory';
?>

<?php if ($action === 'create'): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/lab.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">New Lab Order</h4></div>
</div>

<form method="POST" action="<?= APP_URL ?>/lab.php?action=store">
  <?= csrf_field() ?>
  <input type="hidden" name="visit_id" value="<?= e($visitId) ?>">

  <div class="row g-3">
    <div class="col-lg-7">
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
            <div class="col-md-6 cp-form-group mb-0">
              <label>Doctor <span class="text-danger">*</span></label>
              <select name="doctor_id" class="form-select" required>
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $d['id']==$doctorId?'selected':'' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Tests -->
      <div class="cp-card">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-eyedropper me-2 text-warning"></i>Select Tests</span></div>
        <div class="cp-card-body">
          <?php
          $testsByCategory = [];
          foreach ($tests as $t) {
              $testsByCategory[$t['category'] ?? 'General'][] = $t;
          }
          ?>
          <?php foreach ($testsByCategory as $cat => $catTests): ?>
          <div class="mb-3">
            <div class="fw-600 mb-2" style="font-size:12px;text-transform:uppercase;color:var(--cp-muted)"><?= e($cat) ?></div>
            <div class="row g-2">
              <?php foreach ($catTests as $t): ?>
              <div class="col-md-6">
                <label class="d-flex align-items-center gap-2 p-2 border rounded" style="cursor:pointer;border-radius:10px!important">
                  <input type="checkbox" name="test_ids[]" value="<?= $t['id'] ?>" style="width:16px;height:16px">
                  <div>
                    <div style="font-size:13px;font-weight:600"><?= e($t['name']) ?></div>
                    <div style="font-size:11px;color:var(--cp-muted)"><?= currency($t['price']) ?> &bull; <?= e($t['turnaround'] ?? '—') ?></div>
                  </div>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="cp-card mb-3" style="position:sticky;top:80px">
        <div class="cp-card-header"><span class="cp-card-title">Order Summary</span></div>
        <div class="cp-card-body">
          <div id="selectedTests" class="mb-3 text-muted" style="font-size:13px">No tests selected</div>
          <hr>
          <div class="d-flex justify-content-between fw-700">
            <span>Total</span><span id="totalCost">₹0.00</span>
          </div>
          <button type="submit" class="btn-cp-primary w-100 mt-3"><i class="bi bi-check-circle me-2"></i>Place Lab Order</button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php elseif ($action === 'show'): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/lab.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-grow-1">
    <h4 class="fw-700 mb-0"><?= e($order['order_no']) ?></h4>
    <small class="text-muted"><?= e($order['patient_name']) ?> &bull; <?= format_date($order['order_date']) ?></small>
  </div>
  <button onclick="window.print()" class="btn-cp-outline"><i class="bi bi-printer me-2"></i>Print</button>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="cp-card mb-3">
      <div class="cp-card-header">
        <span class="cp-card-title">Test Results</span>
        <span class="cp-badge badge-<?= $order['status'] ?>"><?= ucfirst(str_replace('_',' ',$order['status'])) ?></span>
      </div>
      <form method="POST" action="<?= APP_URL ?>/lab.php?action=saveResults">
        <?= csrf_field() ?>
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <div style="overflow-x:auto">
          <table class="cp-table">
            <thead><tr><th>Test</th><th>Result</th><th>Normal Range</th><th>Unit</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td>
                  <input type="hidden" name="item_id[]" value="<?= $item['id'] ?>">
                  <div class="fw-600"><?= e($item['test_name']) ?></div>
                  <small class="text-muted"><?= e($item['category']) ?></small>
                </td>
                <td>
                  <?php if ($item['status'] === 'pending' && has_permission('lab','results')): ?>
                  <input type="text" name="result[]" class="form-control form-control-sm" value="<?= e($item['result'] ?? '') ?>" placeholder="Enter result" style="border-radius:8px;min-width:120px">
                  <?php else: ?>
                  <span class="fw-600"><?= e($item['result'] ?? '—') ?></span>
                  <?php endif; ?>
                </td>
                <td><small class="text-muted"><?= e($item['normal_range'] ?? '—') ?></small></td>
                <td><small><?= e($item['unit'] ?? '—') ?></small></td>
                <td><span class="cp-badge <?= $item['status']==='completed'?'badge-completed':'badge-pending' ?>"><?= ucfirst($item['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (has_permission('lab','results') && $order['status'] !== 'completed'): ?>
        <div class="p-3 border-top">
          <button type="submit" class="btn-cp-primary"><i class="bi bi-check-circle me-2"></i>Save Results</button>
        </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title">Order Info</span></div>
      <div class="cp-card-body">
        <div class="mb-2"><strong>Patient:</strong> <?= e($order['patient_name']) ?></div>
        <div class="mb-2"><strong>Doctor:</strong> <?= e($order['doctor_name']) ?></div>
        <div class="mb-2"><strong>Date:</strong> <?= format_date($order['order_date']) ?></div>
        <div class="mb-2"><strong>Total:</strong> <?= currency($order['total_amount']) ?></div>
        <hr>
        <?php if (has_permission('lab','create')): ?>
        <div class="cp-form-group mb-0">
          <label>Update Status</label>
          <form method="POST" action="<?= APP_URL ?>/lab.php?action=updateStatus" class="d-flex gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $order['id'] ?>">
            <select name="status" class="form-select" style="border-radius:10px">
              <?php foreach (['ordered','sample_collected','processing','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $order['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-cp-primary" style="white-space:nowrap">Update</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- INDEX -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h4 class="fw-700 mb-0">Laboratory</h4><small class="text-muted"><?= number_format($total) ?> orders</small></div>
  <?php if (has_permission('lab','create')): ?>
  <a href="<?= APP_URL ?>/lab.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>New Lab Order</a>
  <?php endif; ?>
</div>

<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="d-flex gap-2 flex-wrap">
      <input type="text" name="q" class="form-control" placeholder="Search patient/order..." value="<?= e($search) ?>" style="border-radius:10px;max-width:260px">
      <select name="status" class="form-select" style="border-radius:10px;max-width:160px">
        <option value="">All Status</option>
        <?php foreach (['ordered','sample_collected','processing','completed','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= ($status??'')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-cp-primary">Filter</button>
      <a href="<?= APP_URL ?>/lab.php" class="btn-cp-outline">Clear</a>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Order No</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Tests</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($orders)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-eyedropper" style="font-size:36px"></i><br>No lab orders found</td></tr>
        <?php else: ?>
        <?php foreach ($orders as $lo): ?>
        <tr>
          <td><strong style="color:var(--cp-primary)"><?= e($lo['order_no']) ?></strong></td>
          <td><div class="fw-600"><?= e($lo['patient_name']) ?></div><small class="text-muted"><?= e($lo['patient_id']) ?></small></td>
          <td><?= e($lo['doctor_name']) ?></td>
          <td><?= format_date($lo['order_date']) ?></td>
          <td><span class="badge bg-secondary bg-opacity-10"><?= $lo['test_count'] ?> tests / <?= $lo['done_count'] ?> done</span></td>
          <td><?= currency($lo['total_amount']) ?></td>
          <td><span class="cp-badge badge-<?= $lo['status']==='completed'?'completed':'pending' ?>"><?= ucfirst(str_replace('_',' ',$lo['status'])) ?></span></td>
          <td><a href="<?= APP_URL ?>/lab.php?action=show&id=<?= $lo['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px"><i class="bi bi-eye"></i></a></td>
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

// Test price tracker
const testPrices = {};
<?php if (isset($tests)): ?>
<?php foreach($tests as $t): ?>
testPrices[<?= $t['id'] ?>] = <?= $t['price'] ?>;
<?php endforeach; ?>
<?php endif; ?>

document.querySelectorAll('[name="test_ids[]"]').forEach(cb => {
  cb.addEventListener('change', updateTotal);
});

function updateTotal() {
  const checked = [...document.querySelectorAll('[name="test_ids[]"]:checked')];
  const selectedDiv = document.getElementById('selectedTests');
  const totalEl = document.getElementById('totalCost');
  if (!selectedDiv) return;
  if (!checked.length) { selectedDiv.innerHTML='<span class="text-muted">No tests selected</span>'; totalEl.textContent='₹0.00'; return; }
  let total = 0;
  selectedDiv.innerHTML = checked.map(c => {
    const label = c.closest('label').querySelector('div div').textContent;
    const price = testPrices[c.value] || 0;
    total += price;
    return `<div class="d-flex justify-content-between" style="font-size:13px"><span>${label}</span><span>₹${price.toFixed(2)}</span></div>`;
  }).join('');
  totalEl.textContent = '₹' + total.toFixed(2);
}
</script>
JS;
include VIEW_PATH . '/layouts/app.php';
?>
