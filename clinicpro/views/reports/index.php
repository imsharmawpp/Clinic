<?php
ob_start();
$pageTitle = 'Reports & Analytics';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h4 class="fw-700 mb-0">Reports & Analytics</h4></div>
</div>

<div class="cp-card mb-4">
  <div class="cp-card-body py-3">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
      <div>
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Report Type</label>
        <select name="type" class="form-select" style="border-radius:10px;min-width:160px">
          <option value="revenue"      <?= $type==='revenue'?'selected':'' ?>>Revenue</option>
          <option value="appointments" <?= $type==='appointments'?'selected':'' ?>>Appointments</option>
          <option value="patients"     <?= $type==='patients'?'selected':'' ?>>Patients</option>
          <option value="doctors"      <?= $type==='doctors'?'selected':'' ?>>Doctor Performance</option>
        </select>
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">From</label>
        <input type="date" name="from" class="form-control" value="<?= e($from) ?>" style="border-radius:10px">
      </div>
      <div>
        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">To</label>
        <input type="date" name="to" class="form-control" value="<?= e($to) ?>" style="border-radius:10px">
      </div>
      <button type="submit" class="btn-cp-primary">Generate Report</button>
    </form>
  </div>
</div>

<?php if ($type === 'revenue' && isset($data['summary'])): ?>
<!-- Revenue Report -->
<div class="row g-3 mb-4">
  <?php $summItems = [['Collected', $data['summary']['total_collected']??0,'success'],['Pending',$data['summary']['total_pending']??0,'danger'],['Invoices',$data['summary']['total_invoices']??0,'primary'],['Avg Invoice',$data['summary']['avg_invoice']??0,'warning']]; ?>
  <?php foreach ($summItems as $s): ?>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-value" style="font-size:20px;color:var(--bs-<?= $s[2] ?>)"><?= $s[0]==='Invoices'?number_format($s[1]):currency((float)$s[1]) ?></div>
      <div class="stat-label"><?= $s[0] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if (!empty($data['by_method'])): ?>
<div class="row g-3 mb-4">
  <div class="col-lg-5">
    <div class="cp-card h-100">
      <div class="cp-card-header"><span class="cp-card-title">Revenue by Payment Method</span></div>
      <div class="cp-card-body"><canvas id="methodChart" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="cp-card h-100">
      <div class="cp-card-header"><span class="cp-card-title">Payment Method Breakdown</span></div>
      <div class="cp-card-body p-0">
        <table class="cp-table">
          <thead><tr><th>Method</th><th>Amount</th></tr></thead>
          <tbody>
            <?php foreach ($data['by_method'] as $m): ?>
            <tr><td><?= ucfirst($m['method']) ?></td><td class="fw-600"><?= currency($m['total']) ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php elseif ($type === 'appointments' && !empty($data['rows'])): ?>
<div class="cp-card">
  <div class="cp-card-header"><span class="cp-card-title">Appointments by Doctor</span></div>
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Doctor</th><th>Specialization</th><th>Total</th><th>Completed</th><th>Cancelled</th><th>No Show</th><th>Completion %</th></tr></thead>
      <tbody>
        <?php foreach ($data['rows'] as $r): ?>
        <tr>
          <td class="fw-600"><?= e($r['doctor_name']) ?></td>
          <td><?= e($r['specialization']) ?></td>
          <td><?= $r['total'] ?></td>
          <td class="text-success"><?= $r['completed'] ?></td>
          <td class="text-danger"><?= $r['cancelled'] ?></td>
          <td class="text-warning"><?= $r['no_show'] ?></td>
          <td>
            <?php $pct = $r['total']>0?round($r['completed']/$r['total']*100):0; ?>
            <div class="d-flex align-items-center gap-2">
              <div style="height:6px;background:#e2e8f5;border-radius:3px;width:80px;overflow:hidden">
                <div style="height:100%;width:<?= $pct ?>%;background:var(--cp-success);border-radius:3px"></div>
              </div>
              <span><?= $pct ?>%</span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php elseif ($type === 'doctors' && !empty($data['rows'])): ?>
<div class="cp-card">
  <div class="cp-card-header"><span class="cp-card-title">Doctor Performance</span></div>
  <div style="overflow-x:auto">
    <table class="cp-table">
      <thead><tr><th>Doctor</th><th>Specialization</th><th>Visits</th><th>Prescriptions</th><th>Revenue</th></tr></thead>
      <tbody>
        <?php foreach ($data['rows'] as $r): ?>
        <tr>
          <td class="fw-600"><?= e($r['name']) ?></td>
          <td><?= e($r['specialization']) ?></td>
          <td><?= $r['visits'] ?></td>
          <td><?= $r['prescriptions'] ?></td>
          <td class="fw-600 text-success"><?= currency($r['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<div class="cp-card"><div class="cp-card-body text-center py-5 text-muted"><i class="bi bi-bar-chart-line" style="font-size:48px"></i><p class="mt-2">Select a report type and date range to generate the report.</p></div></div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$scripts = '';
if ($type === 'revenue' && !empty($data['by_method'])) {
    $labels  = json_encode(array_column($data['by_method'], 'method'));
    $amounts = json_encode(array_column($data['by_method'], 'total'));
    $scripts = <<<JS
<script>
new Chart(document.getElementById('methodChart'), {
  type: 'doughnut',
  data: {
    labels: $labels,
    datasets: [{
      data: $amounts,
      backgroundColor: ['#1a6eff','#0dcfb4','#ff6b35','#ffc107','#6f42c1','#20c997'],
      borderWidth: 0,
    }]
  },
  options: { plugins: { legend: { position: 'bottom' } }, cutout: '65%' }
});
</script>
JS;
}
include VIEW_PATH . '/layouts/app.php';
?>
