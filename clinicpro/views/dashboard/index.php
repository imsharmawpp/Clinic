<?php
ob_start();
$pageTitle = 'Dashboard';
?>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e8f0ff;color:var(--cp-primary)"><i class="bi bi-calendar2-check"></i></div>
      <div class="stat-value"><?= $apptStats['total'] ?? 0 ?></div>
      <div class="stat-label">Today's Appointments</div>
      <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i><?= $apptStats['completed']??0 ?> completed</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e6faf6;color:var(--cp-success)"><i class="bi bi-people"></i></div>
      <div class="stat-value"><?= number_format($totalPatients) ?></div>
      <div class="stat-label">Total Patients</div>
      <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i>+<?= $newPatients ?> this month</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fff8e6;color:#cc8800"><i class="bi bi-currency-rupee"></i></div>
      <div class="stat-value" style="font-size:22px"><?= currency($revenueToday) ?></div>
      <div class="stat-label">Revenue Today</div>
      <div class="stat-trend up"><i class="bi bi-calendar3"></i> <?= currency($revenueMonth) ?> this month</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#ffeaea;color:var(--cp-danger)"><i class="bi bi-receipt"></i></div>
      <div class="stat-value" style="font-size:22px"><?= currency($pendingBills) ?></div>
      <div class="stat-label">Pending Collections</div>
      <div class="stat-trend down"><i class="bi bi-exclamation-circle"></i> Needs follow-up</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- Today's Appointments -->
  <div class="col-lg-7">
    <div class="cp-card h-100">
      <div class="cp-card-header">
        <span class="cp-card-title"><i class="bi bi-calendar2-week me-2 text-primary"></i>Today's Appointments</span>
        <a href="<?= APP_URL ?>/appointments.php" class="btn-cp-outline btn-sm" style="font-size:12px;padding:5px 12px">View All</a>
      </div>
      <?php if (empty($todayAppts)): ?>
      <div class="cp-card-body text-center py-5">
        <i class="bi bi-calendar2-x" style="font-size:40px;color:var(--cp-muted)"></i>
        <p class="mt-2 text-muted">No appointments today</p>
        <a href="<?= APP_URL ?>/appointments.php?action=create" class="btn-cp-primary">+ Book Appointment</a>
      </div>
      <?php else: ?>
      <div style="overflow-x:auto">
        <table class="cp-table">
          <thead><tr><th>Token</th><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($todayAppts as $a): ?>
          <tr>
            <td><span style="font-weight:700;color:var(--cp-primary)">#<?= $a['token_no'] ?></span></td>
            <td>
              <strong><?= e($a['patient_name']) ?></strong><br>
              <small class="text-muted"><?= e($a['patient_phone']) ?></small>
            </td>
            <td>
              <?= e($a['doctor_name']) ?><br>
              <small class="text-muted"><?= e($a['specialization']) ?></small>
            </td>
            <td><?= substr($a['appointment_time'],0,5) ?></td>
            <td><span class="cp-badge badge-<?= $a['status'] ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span></td>
            <td>
              <a href="<?= APP_URL ?>/appointments.php?action=show&id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:11px">
                <i class="bi bi-arrow-right"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Revenue Chart -->
  <div class="col-lg-5">
    <div class="cp-card h-100">
      <div class="cp-card-header">
        <span class="cp-card-title"><i class="bi bi-graph-up me-2 text-success"></i>Revenue (Last 7 Days)</span>
      </div>
      <div class="cp-card-body">
        <canvas id="revenueChart" height="180"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Quick Stats -->
  <div class="col-lg-4">
    <div class="cp-card h-100">
      <div class="cp-card-header">
        <span class="cp-card-title"><i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Actions</span>
      </div>
      <div class="cp-card-body">
        <div class="d-grid gap-2">
          <a href="<?= APP_URL ?>/appointments.php?action=create" class="btn btn-primary" style="border-radius:10px;font-weight:600">
            <i class="bi bi-plus-circle me-2"></i>New Appointment
          </a>
          <a href="<?= APP_URL ?>/patients.php?action=create" class="btn btn-outline-primary" style="border-radius:10px;font-weight:600">
            <i class="bi bi-person-plus me-2"></i>Register Patient
          </a>
          <a href="<?= APP_URL ?>/billing.php?action=create" class="btn btn-outline-success" style="border-radius:10px;font-weight:600">
            <i class="bi bi-receipt me-2"></i>Create Invoice
          </a>
          <a href="<?= APP_URL ?>/opd.php?action=create" class="btn btn-outline-info" style="border-radius:10px;font-weight:600">
            <i class="bi bi-clipboard2-pulse me-2"></i>Start OPD Visit
          </a>
        </div>

        <?php if (!empty($lowStock)): ?>
        <div class="mt-4">
          <div class="fw-bold mb-2" style="font-size:13px;color:var(--cp-danger)"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock Alert</div>
          <?php foreach ($lowStock as $s): ?>
          <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:13px">
            <span><?= e($s['name']) ?></span>
            <span class="badge bg-danger"><?= $s['qty'] ?> left</span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Patients -->
  <div class="col-lg-4">
    <div class="cp-card h-100">
      <div class="cp-card-header">
        <span class="cp-card-title"><i class="bi bi-people me-2 text-primary"></i>Recent Patients</span>
        <a href="<?= APP_URL ?>/patients.php" style="font-size:12px;color:var(--cp-primary)">View All</a>
      </div>
      <div class="cp-card-body p-0">
        <?php foreach ($recentPatients as $p): ?>
        <a href="<?= APP_URL ?>/patients.php?action=show&id=<?= $p['id'] ?>" class="d-flex align-items-center p-3 border-bottom text-decoration-none" style="color:inherit">
          <div style="width:38px;height:38px;border-radius:10px;background:var(--cp-primary);display:grid;place-items:center;color:#fff;font-weight:700;flex-shrink:0">
            <?= strtoupper(substr($p['name'],0,1)) ?>
          </div>
          <div class="ms-3 flex-grow-1 overflow-hidden">
            <div style="font-weight:600;font-size:13px"><?= e($p['name']) ?></div>
            <div style="font-size:12px;color:var(--cp-muted)"><?= e($p['patient_id']) ?> &bull; <?= e($p['phone']) ?></div>
          </div>
          <small class="text-muted" style="font-size:11px;flex-shrink:0"><?= date('d M', strtotime($p['created_at'])) ?></small>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Appointment Summary -->
  <div class="col-lg-4">
    <div class="cp-card h-100">
      <div class="cp-card-header">
        <span class="cp-card-title"><i class="bi bi-pie-chart me-2 text-info"></i>Today's Summary</span>
      </div>
      <div class="cp-card-body">
        <?php
        $items = [
          ['Scheduled',  $apptStats['scheduled'] ?? 0,  'primary'],
          ['Waiting',    $apptStats['waiting'] ?? 0,    'warning'],
          ['Completed',  $apptStats['completed'] ?? 0,  'success'],
          ['Cancelled',  $apptStats['cancelled'] ?? 0,  'danger'],
        ];
        ?>
        <?php foreach ($items as $item): ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span style="font-size:14px"><?= $item[0] ?></span>
          <div class="d-flex align-items-center gap-2">
            <div style="height:6px;background:#e2e8f5;border-radius:3px;width:80px;overflow:hidden">
              <?php $pct = $apptStats['total']>0 ? ($item[1]/$apptStats['total']*100) : 0; ?>
              <div style="height:100%;width:<?= $pct ?>%;background:var(--bs-<?= $item[2] ?>);border-radius:3px;transition:width .5s"></div>
            </div>
            <span style="font-weight:700;min-width:24px;text-align:right"><?= $item[1] ?></span>
          </div>
        </div>
        <?php endforeach; ?>

        <hr>
        <div class="d-flex justify-content-between align-items-center">
          <span style="font-size:13px;color:var(--cp-muted)">Active Doctors</span>
          <span style="font-weight:700;color:var(--cp-primary)"><?= $activeDoctors ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$scripts = '<script>
const labels = ' . json_encode(array_column($chartData, 'date')) . ';
const revenue = ' . json_encode(array_column($chartData, 'revenue')) . ';
new Chart(document.getElementById("revenueChart"), {
  type: "bar",
  data: {
    labels,
    datasets: [{
      label: "Revenue",
      data: revenue,
      backgroundColor: "rgba(26,110,255,.15)",
      borderColor: "#1a6eff",
      borderWidth: 2,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { grid: { color: "#e2e8f5" }, ticks: { callback: v => "₹"+v.toLocaleString("en-IN") } },
      x: { grid: { display: false } }
    }
  }
});
</script>';

include VIEW_PATH . '/layouts/app.php';
