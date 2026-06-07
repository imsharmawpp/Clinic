<?php
ob_start();
$action    = $_GET['action'] ?? 'index';
$pageTitle = 'Pharmacy';
?>

<?php if ($action === 'add_medicine'): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/pharmacy.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">Add Medicine</h4></div>
</div>
<div class="row justify-content-center">
  <div class="col-lg-8">
    <form method="POST" action="<?= APP_URL ?>/pharmacy.php?action=storeMedicine">
      <?= csrf_field() ?>
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Medicine Details</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-6 cp-form-group"><label>Medicine Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="col-md-6 cp-form-group"><label>Generic Name</label><input type="text" name="generic_name" class="form-control"></div>
            <div class="col-md-4 cp-form-group"><label>Type</label><select name="type" class="form-select">
              <?php foreach (['tablet','capsule','syrup','injection','cream','drops','inhaler','other'] as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
              <?php endforeach; ?>
            </select></div>
            <div class="col-md-4 cp-form-group"><label>Category</label><input type="text" name="category" class="form-control" placeholder="Antibiotic, Analgesic..."></div>
            <div class="col-md-4 cp-form-group"><label>Unit</label><input type="text" name="unit" class="form-control" value="mg"></div>
            <div class="col-md-4 cp-form-group"><label>Manufacturer</label><input type="text" name="manufacturer" class="form-control"></div>
            <div class="col-md-4 cp-form-group"><label>GST %</label><input type="number" name="gst_percent" class="form-control" value="12" step="0.01"></div>
          </div>
        </div>
      </div>
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Pricing</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-4 cp-form-group mb-0"><label>Purchase Price</label><input type="number" name="purchase_price" class="form-control" value="0" step="0.01"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>Selling Price</label><input type="number" name="selling_price" class="form-control" value="0" step="0.01"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>MRP</label><input type="number" name="mrp" class="form-control" value="0" step="0.01"></div>
          </div>
        </div>
      </div>
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Initial Stock</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-4 cp-form-group mb-0"><label>Opening Qty</label><input type="number" name="initial_qty" class="form-control" value="0" min="0"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>Batch No</label><input type="text" name="batch_no" class="form-control"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-plus-circle me-2"></i>Add Medicine</button>
    </form>
  </div>
</div>

<?php elseif ($action === 'add_stock'): ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/pharmacy.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">Add Stock</h4></div>
</div>
<div class="row justify-content-center">
  <div class="col-lg-7">
    <form method="POST" action="<?= APP_URL ?>/pharmacy.php?action=storeStock">
      <?= csrf_field() ?>
      <div class="cp-card mb-3">
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-12 cp-form-group"><label>Medicine <span class="text-danger">*</span></label>
              <select name="medicine_id" class="form-select" required>
                <option value="">Select Medicine</option>
                <?php foreach ($medicines as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($medicine && $medicine['id']==$m['id'])?'selected':'' ?>><?= e($m['name']) ?> (<?= $m['type'] ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 cp-form-group"><label>Batch No</label><input type="text" name="batch_no" class="form-control"></div>
            <div class="col-md-6 cp-form-group"><label>Expiry Date</label><input type="date" name="expiry_date" class="form-control"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>Quantity <span class="text-danger">*</span></label><input type="number" name="quantity" class="form-control" required min="1"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>Purchase Price</label><input type="number" name="purchase_price" class="form-control" value="0" step="0.01"></div>
            <div class="col-md-4 cp-form-group mb-0"><label>Selling Price</label><input type="number" name="selling_price" class="form-control" value="0" step="0.01"></div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-box-seam me-2"></i>Add Stock</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- INDEX -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h4 class="fw-700 mb-0">Pharmacy & Stock</h4></div>
  <div class="d-flex gap-2">
    <a href="<?= APP_URL ?>/pharmacy.php?action=add_stock" class="btn-cp-outline"><i class="bi bi-box-seam me-2"></i>Add Stock</a>
    <a href="<?= APP_URL ?>/pharmacy.php?action=add_medicine" class="btn-cp-primary"><i class="bi bi-plus-lg me-2"></i>Add Medicine</a>
  </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?= number_format($stats['total_medicines'] ?? 0) ?></div><div class="stat-label">Total Medicines</div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value"><?= number_format($stats['total_units'] ?? 0) ?></div><div class="stat-label">Total Units</div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value" style="font-size:20px"><?= currency($stats['total_value'] ?? 0) ?></div><div class="stat-label">Stock Value</div></div></div>
  <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-value" style="color:var(--cp-danger)"><?= $stats['low_stock_count'] ?? 0 ?></div><div class="stat-label">Low Stock Items</div></div></div>
</div>

<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="d-flex gap-2 flex-wrap">
      <input type="text" name="q" class="form-control" placeholder="Search medicine..." value="<?= e($search) ?>" style="border-radius:10px;max-width:260px">
      <select name="category" class="form-select" style="border-radius:10px;max-width:180px">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= e($cat) ?>" <?= $category===$cat?'selected':'' ?>><?= e($cat) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="filter" class="form-select" style="border-radius:10px;max-width:160px">
        <option value="">All Stock</option>
        <option value="low_stock" <?= $filter==='low_stock'?'selected':'' ?>>Low Stock (&lt;10)</option>
        <option value="expiring"  <?= $filter==='expiring'?'selected':'' ?>>Expiring Soon</option>
      </select>
      <button type="submit" class="btn-cp-primary">Filter</button>
      <a href="<?= APP_URL ?>/pharmacy.php" class="btn-cp-outline">Clear</a>
    </form>
  </div>
</div>

<div class="cp-card">
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Medicine</th><th>Type</th><th>Category</th><th>Stock</th><th>Selling Price</th><th>MRP</th><th>Nearest Expiry</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($medicines)): ?>
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-bag-heart" style="font-size:36px"></i><br>No medicines found</td></tr>
        <?php else: ?>
        <?php foreach ($medicines as $m): ?>
        <?php $isLow = $m['total_stock'] < 10; ?>
        <tr>
          <td>
            <div class="fw-600"><?= e($m['name']) ?></div>
            <?php if ($m['generic_name']): ?><small class="text-muted"><?= e($m['generic_name']) ?></small><?php endif; ?>
          </td>
          <td><span class="badge bg-light text-dark"><?= ucfirst($m['type']) ?></span></td>
          <td><?= e($m['category'] ?? '—') ?></td>
          <td>
            <span class="fw-700" style="color:<?= $isLow?'var(--cp-danger)':'var(--cp-success)' ?>"><?= $m['total_stock'] ?></span>
            <?php if ($isLow): ?><span class="cp-badge ms-1" style="background:#ffeaea;color:var(--cp-danger);font-size:10px">Low</span><?php endif; ?>
          </td>
          <td><?= currency($m['selling_price']) ?></td>
          <td><?= currency($m['mrp']) ?></td>
          <td>
            <?php if ($m['nearest_expiry']): ?>
            <?php $expDays = (int)((strtotime($m['nearest_expiry'])-time())/86400); ?>
            <span class="cp-badge" style="background:<?= $expDays<30?'#ffeaea':'#e6faf6' ?>;color:<?= $expDays<30?'var(--cp-danger)':'var(--cp-success)' ?>">
              <?= format_date($m['nearest_expiry']) ?>
            </span>
            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
          </td>
          <td><a href="<?= APP_URL ?>/pharmacy.php?action=add_stock&medicine_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px" title="Add Stock"><i class="bi bi-box-seam"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
