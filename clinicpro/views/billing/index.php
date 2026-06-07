<?php
ob_start();
$action    = $_GET['action'] ?? 'index';
$pageTitle = 'Billing';
?>

<?php if ($action === 'create'): ?>
<!-- ── CREATE INVOICE ──────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/billing.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">New Invoice</h4><small class="text-muted">Create billing for patient services</small></div>
</div>

<form method="POST" action="<?= APP_URL ?>/billing.php?action=store" id="invoiceForm">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <!-- Patient & Doctor -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-person me-2 text-primary"></i>Patient</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-6 cp-form-group mb-0">
              <label>Patient <span class="text-danger">*</span></label>
              <input type="text" id="patientSearch" class="form-control" placeholder="Search patient..." autocomplete="off">
              <input type="hidden" name="patient_id" id="patientId" required>
            </div>
            <div class="col-md-6 cp-form-group mb-0">
              <label>Doctor</label>
              <select name="doctor_id" class="form-select">
                <option value="">Select Doctor</option>
                <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Line Items -->
      <div class="cp-card mb-3">
        <div class="cp-card-header">
          <span class="cp-card-title"><i class="bi bi-list-ul me-2 text-success"></i>Services & Items</span>
          <button type="button" class="btn-cp-primary" onclick="addRow()" style="font-size:12px;padding:6px 14px"><i class="bi bi-plus me-1"></i>Add Row</button>
        </div>
        <div class="cp-card-body p-0">
          <div style="overflow-x:auto">
            <table class="cp-table" id="itemsTable">
              <thead>
                <tr>
                  <th>Type</th><th style="min-width:200px">Description</th>
                  <th>Qty</th><th>Rate</th><th>Discount</th><th>GST%</th><th>Total</th><th></th>
                </tr>
              </thead>
              <tbody id="itemsBody">
                <tr class="item-row">
                  <td><select name="item_type[]" class="form-select form-select-sm" style="min-width:120px">
                    <option value="consultation">Consultation</option>
                    <option value="procedure">Procedure</option>
                    <option value="medicine">Medicine</option>
                    <option value="lab">Lab</option>
                    <option value="other">Other</option>
                  </select></td>
                  <td><input type="text" name="description[]" class="form-control form-control-sm" placeholder="Service description" required></td>
                  <td><input type="number" name="quantity[]" class="form-control form-control-sm calc-input" value="1" min="0.01" step="0.01" style="width:70px"></td>
                  <td><input type="number" name="unit_price[]" class="form-control form-control-sm calc-input" value="0" min="0" step="0.01" style="width:90px"></td>
                  <td><input type="number" name="item_discount[]" class="form-control form-control-sm calc-input" value="0" min="0" step="0.01" style="width:80px"></td>
                  <td><input type="number" name="gst_percent[]" class="form-control form-control-sm calc-input" value="0" min="0" max="28" step="0.01" style="width:70px"></td>
                  <td class="row-total fw-bold" style="color:var(--cp-primary)">₹0.00</td>
                  <td><button type="button" class="btn btn-sm btn-outline-danger remove-row" style="border-radius:8px"><i class="bi bi-trash"></i></button></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="cp-card mb-3">
        <div class="cp-card-body cp-form-group mb-0">
          <label>Notes</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Payment terms, remarks..."></textarea>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="col-lg-4">
      <div class="cp-card mb-3" style="position:sticky;top:80px">
        <div class="cp-card-header"><span class="cp-card-title">Invoice Summary</span></div>
        <div class="cp-card-body">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span id="summSubtotal">₹0.00</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">GST</span>
            <span id="summGst">₹0.00</span>
          </div>
          <div class="cp-form-group">
            <label>Discount</label>
            <div class="input-group">
              <select name="discount_type" class="form-select" style="max-width:100px;border-radius:10px 0 0 10px">
                <option value="fixed">₹</option>
                <option value="percentage">%</option>
              </select>
              <input type="number" name="discount_value" id="discountInput" class="form-control" value="0" min="0" step="0.01" style="border-radius:0 10px 10px 0" oninput="calcTotal()">
            </div>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-3">
            <span style="font-weight:700;font-size:16px">Total</span>
            <span id="summTotal" style="font-weight:700;font-size:18px;color:var(--cp-primary)">₹0.00</span>
          </div>
          <button type="submit" class="btn-cp-primary w-100">
            <i class="bi bi-receipt me-2"></i>Generate Invoice
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<?php elseif ($action === 'show'): ?>
<!-- ── SHOW INVOICE ────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/billing.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-grow-1">
    <h4 class="fw-700 mb-0">Invoice <?= e($invoice['invoice_no']) ?></h4>
    <small class="text-muted"><?= format_date($invoice['invoice_date']) ?> &bull; <span class="cp-badge badge-<?= $invoice['status'] ?>"><?= ucfirst($invoice['status']) ?></span></small>
  </div>
  <button onclick="window.print()" class="btn-cp-outline"><i class="bi bi-printer me-2"></i>Print</button>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="cp-card mb-3" id="printable">
      <div class="cp-card-body">
        <!-- Invoice Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
          <div>
            <h3 style="font-family:'Playfair Display',serif"><?= e($clinic['name']) ?></h3>
            <p class="text-muted small mb-0"><?= e($clinic['address']) ?>, <?= e($clinic['city']) ?></p>
            <p class="text-muted small"><?= e($clinic['phone']) ?> &bull; <?= e($clinic['email']) ?></p>
            <?php if ($clinic['gstin']): ?><p class="small"><strong>GSTIN:</strong> <?= e($clinic['gstin']) ?></p><?php endif; ?>
          </div>
          <div class="text-end">
            <div style="font-size:28px;font-weight:700;color:var(--cp-primary)">INVOICE</div>
            <div style="font-size:18px;font-weight:600"><?= e($invoice['invoice_no']) ?></div>
            <div class="text-muted small">Date: <?= format_date($invoice['invoice_date']) ?></div>
            <span class="cp-badge badge-<?= $invoice['status'] ?> mt-1"><?= ucfirst($invoice['status']) ?></span>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-6">
            <div class="fw-600 mb-1" style="font-size:12px;color:var(--cp-muted);text-transform:uppercase">Bill To</div>
            <div class="fw-700"><?= e($invoice['patient_name']) ?></div>
            <div class="text-muted small"><?= e($invoice['patient_id']) ?></div>
            <div class="text-muted small"><?= e($invoice['patient_phone']) ?></div>
          </div>
          <?php if ($invoice['doctor_name']): ?>
          <div class="col-6">
            <div class="fw-600 mb-1" style="font-size:12px;color:var(--cp-muted);text-transform:uppercase">Doctor</div>
            <div class="fw-700"><?= e($invoice['doctor_name']) ?></div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Items table -->
        <table class="cp-table mb-4">
          <thead><tr><th>#</th><th>Description</th><th>Qty</th><th>Rate</th><th>GST</th><th class="text-end">Total</th></tr></thead>
          <tbody>
            <?php foreach ($invoice['items'] as $i => $item): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td>
                <div class="fw-600"><?= e($item['description']) ?></div>
                <small class="text-muted badge bg-light"><?= ucfirst($item['item_type']) ?></small>
              </td>
              <td><?= $item['quantity'] ?></td>
              <td><?= currency($item['unit_price']) ?></td>
              <td><?= $item['gst_percent'] ?>%</td>
              <td class="text-end fw-600"><?= currency($item['total']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Totals -->
        <div class="row justify-content-end">
          <div class="col-md-5">
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Subtotal</span><span><?= currency($invoice['subtotal']) ?></span></div>
            <?php if ($invoice['discount_amount'] > 0): ?>
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Discount</span><span class="text-success">— <?= currency($invoice['discount_amount']) ?></span></div>
            <?php endif; ?>
            <?php if ($invoice['tax_amount'] > 0): ?>
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">GST</span><span><?= currency($invoice['tax_amount']) ?></span></div>
            <?php endif; ?>
            <hr class="my-2">
            <div class="d-flex justify-content-between mb-1"><strong>Total</strong><strong style="font-size:18px;color:var(--cp-primary)"><?= currency($invoice['total_amount']) ?></strong></div>
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">Paid</span><span class="text-success"><?= currency($invoice['paid_amount']) ?></span></div>
            <?php if ($invoice['balance_amount'] > 0): ?>
            <div class="d-flex justify-content-between"><strong>Balance Due</strong><strong class="text-danger"><?= currency($invoice['balance_amount']) ?></strong></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment Panel -->
  <div class="col-lg-4">
    <?php if ($invoice['balance_amount'] > 0 && has_permission('billing','create')): ?>
    <div class="cp-card mb-3">
      <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-credit-card me-2 text-success"></i>Record Payment</span></div>
      <div class="cp-card-body">
        <div class="cp-form-group">
          <label>Amount</label>
          <input type="number" id="payAmount" class="form-control" value="<?= $invoice['balance_amount'] ?>" step="0.01">
        </div>
        <div class="cp-form-group">
          <label>Method</label>
          <select id="payMethod" class="form-select">
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="card">Card</option>
            <option value="netbanking">Net Banking</option>
            <option value="cheque">Cheque</option>
          </select>
        </div>
        <div class="cp-form-group">
          <label>Reference No</label>
          <input type="text" id="payRef" class="form-control" placeholder="UPI / Transaction ID">
        </div>
        <div class="cp-form-group mb-0">
          <label>Date</label>
          <input type="date" id="payDate" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <button class="btn-cp-primary w-100 mt-3" onclick="recordPayment(<?= $invoice['id'] ?>)">
          <i class="bi bi-check-circle me-2"></i>Record Payment
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Payment History -->
    <?php if (!empty($invoice['payments'])): ?>
    <div class="cp-card">
      <div class="cp-card-header"><span class="cp-card-title">Payment History</span></div>
      <div class="cp-card-body p-0">
        <?php foreach ($invoice['payments'] as $p): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <div>
            <div class="fw-600"><?= currency($p['amount']) ?></div>
            <small class="text-muted"><?= ucfirst($p['method']) ?> &bull; <?= format_date($p['payment_date']) ?></small>
          </div>
          <span class="cp-badge" style="background:#e6faf6;color:var(--cp-success)">Paid</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- ── BILLING INDEX ───────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="fw-700 mb-0">Billing & Invoices</h4></div>
  <?php if (has_permission('billing','create')): ?>
  <a href="<?= APP_URL ?>/billing.php?action=create" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>New Invoice</a>
  <?php endif; ?>
</div>

<!-- Revenue Stats -->
<div class="row g-3 mb-4">
  <?php
  $revItems = [
    ['Gross Revenue', $stats['gross']??0, 'primary'],
    ['Collected', $stats['collected']??0, 'success'],
    ['Pending', $stats['pending']??0, 'danger'],
    ['Total Invoices', $stats['invoices']??0, 'info'],
  ];
  foreach ($revItems as $r):
  ?>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label mb-2"><?= $r[0] ?></div>
      <div class="stat-value" style="font-size:<?= is_numeric($r[1])&&$r[1]>999?'20px':'26px' ?>;color:var(--bs-<?= $r[2] ?>)">
        <?= $r[0]==='Total Invoices' ? $r[1] : currency((float)$r[1]) ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3"><input type="text" name="q" class="form-control" placeholder="Invoice or patient..." value="<?= e($filters['search']) ?>" style="border-radius:10px"></div>
      <div class="col-md-2"><select name="status" class="form-select" style="border-radius:10px">
        <option value="">All Status</option>
        <?php foreach (['pending','partial','paid','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select></div>
      <div class="col-md-2"><input type="date" name="from" class="form-control" value="<?= e($filters['from']) ?>" style="border-radius:10px"></div>
      <div class="col-md-2"><input type="date" name="to" class="form-control" value="<?= e($filters['to']) ?>" style="border-radius:10px"></div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
        <a href="<?= APP_URL ?>/billing.php" class="btn-cp-outline">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Invoice No</th><th>Patient</th><th>Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($invoices)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-receipt" style="font-size:36px"></i><br>No invoices found</td></tr>
        <?php else: ?>
        <?php foreach ($invoices as $inv): ?>
        <tr>
          <td><strong style="color:var(--cp-primary)"><?= e($inv['invoice_no']) ?></strong></td>
          <td><div class="fw-600"><?= e($inv['patient_name']) ?></div><small class="text-muted"><?= e($inv['patient_id']) ?></small></td>
          <td><?= format_date($inv['invoice_date']) ?></td>
          <td class="fw-600"><?= currency($inv['total_amount']) ?></td>
          <td class="text-success"><?= currency($inv['paid_amount']) ?></td>
          <td class="<?= $inv['balance_amount']>0?'text-danger fw-600':'' ?>"><?= currency($inv['balance_amount']) ?></td>
          <td><span class="cp-badge badge-<?= $inv['status'] ?>"><?= ucfirst($inv['status']) ?></span></td>
          <td>
            <a href="<?= APP_URL ?>/billing.php?action=show&id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px"><i class="bi bi-eye"></i></a>
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

// Invoice row calculator
function calcRow(row) {
  const qty = parseFloat(row.querySelector('[name="quantity[]"]')?.value) || 0;
  const rate = parseFloat(row.querySelector('[name="unit_price[]"]')?.value) || 0;
  const disc = parseFloat(row.querySelector('[name="item_discount[]"]')?.value) || 0;
  const gst  = parseFloat(row.querySelector('[name="gst_percent[]"]')?.value) || 0;
  const base = (qty * rate) - disc;
  const total = base + (base * gst / 100);
  const td = row.querySelector('.row-total');
  if (td) td.textContent = '₹' + total.toFixed(2);
  return { base, gst_amt: base*gst/100, total };
}
function calcTotal() {
  let subtotal=0, gstTotal=0;
  document.querySelectorAll('.item-row').forEach(r => {
    const v = calcRow(r);
    subtotal += v.base;
    gstTotal += v.gst_amt;
  });
  const disc = parseFloat(document.getElementById('discountInput')?.value) || 0;
  const total = subtotal + gstTotal - disc;
  const fmt = v => '₹' + Math.max(0,v).toFixed(2);
  document.getElementById('summSubtotal').textContent = fmt(subtotal);
  document.getElementById('summGst').textContent = fmt(gstTotal);
  document.getElementById('summTotal').textContent = fmt(total);
}
document.addEventListener('input', e => { if (e.target.classList.contains('calc-input') || e.target.id==='discountInput') calcTotal(); });
document.addEventListener('click', e => {
  if (e.target.closest('.remove-row')) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) { e.target.closest('.item-row').remove(); calcTotal(); }
  }
});
function addRow() {
  const tbody = document.getElementById('itemsBody');
  const first = tbody.querySelector('.item-row');
  const clone = first.cloneNode(true);
  clone.querySelectorAll('input').forEach(i => { if (i.type!=='select') i.value = i.name.includes('quantity')?'1':'0'; });
  clone.querySelector('.row-total').textContent='₹0.00';
  tbody.appendChild(clone);
}
function recordPayment(invoiceId) {
  const fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('invoice_id', invoiceId);
  fd.append('amount', document.getElementById('payAmount').value);
  fd.append('method', document.getElementById('payMethod').value);
  fd.append('reference_no', document.getElementById('payRef').value);
  fd.append('payment_date', document.getElementById('payDate').value);
  fetch('billing.php?action=payment', { method:'POST', body:fd })
    .then(r=>r.json()).then(d => { if (d.success) location.reload(); });
}
</script>
JS;
include VIEW_PATH . '/layouts/app.php';
?>
