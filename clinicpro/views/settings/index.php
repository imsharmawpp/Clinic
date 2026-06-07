<?php
ob_start();
$pageTitle = 'Settings';
?>
<h4 class="fw-700 mb-4">Settings</h4>

<ul class="nav nav-tabs mb-4" id="settingsTabs">
  <li class="nav-item"><a class="nav-link active fw-600" href="#clinic" data-bs-toggle="tab">Clinic Profile</a></li>
  <li class="nav-item"><a class="nav-link fw-600" href="#preferences" data-bs-toggle="tab">Preferences</a></li>
  <li class="nav-item" id="users-tab-li"><a class="nav-link fw-600" href="#users" data-bs-toggle="tab">Users</a></li>
</ul>

<div class="tab-content">
  <!-- CLINIC -->
  <div class="tab-pane active" id="clinic">
    <form method="POST" action="<?= APP_URL ?>/settings.php?action=updateClinic">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-lg-8">
          <div class="cp-card mb-3">
            <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-hospital me-2 text-primary"></i>Clinic Information</span></div>
            <div class="cp-card-body">
              <div class="row g-3">
                <div class="col-md-6 cp-form-group"><label>Clinic Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= e($clinic['name']) ?>" required></div>
                <div class="col-md-6 cp-form-group"><label>Tagline</label><input type="text" name="tagline" class="form-control" value="<?= e($clinic['tagline'] ?? '') ?>"></div>
                <div class="col-md-6 cp-form-group"><label>Email</label><input type="email" name="email" class="form-control" value="<?= e($clinic['email'] ?? '') ?>"></div>
                <div class="col-md-6 cp-form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($clinic['phone'] ?? '') ?>"></div>
                <div class="col-12 cp-form-group"><label>Address</label><textarea name="address" class="form-control" rows="2"><?= e($clinic['address'] ?? '') ?></textarea></div>
                <div class="col-md-4 cp-form-group"><label>City</label><input type="text" name="city" class="form-control" value="<?= e($clinic['city'] ?? '') ?>"></div>
                <div class="col-md-4 cp-form-group"><label>State</label><input type="text" name="state" class="form-control" value="<?= e($clinic['state'] ?? '') ?>"></div>
                <div class="col-md-4 cp-form-group"><label>Pincode</label><input type="text" name="pincode" class="form-control" value="<?= e($clinic['pincode'] ?? '') ?>"></div>
                <div class="col-md-6 cp-form-group"><label>GSTIN</label><input type="text" name="gstin" class="form-control" value="<?= e($clinic['gstin'] ?? '') ?>"></div>
                <div class="col-md-6 cp-form-group"><label>Registration No</label><input type="text" name="registration_no" class="form-control" value="<?= e($clinic['registration_no'] ?? '') ?>"></div>
                <div class="col-md-6 cp-form-group"><label>Website</label><input type="url" name="website" class="form-control" value="<?= e($clinic['website'] ?? '') ?>"></div>
                <div class="col-md-6 cp-form-group mb-0"><label>Theme Color</label><input type="color" name="theme_color" class="form-control" value="<?= e($clinic['theme_color'] ?? '#1a6eff') ?>"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="cp-card mb-3">
            <div class="cp-card-header"><span class="cp-card-title">Billing Preferences</span></div>
            <div class="cp-card-body">
              <div class="cp-form-group"><label>Invoice Prefix</label><input type="text" name="invoice_prefix" class="form-control" value="<?= e($settings['invoice_prefix'] ?? 'INV') ?>"></div>
              <div class="cp-form-group"><label>Patient ID Prefix</label><input type="text" name="patient_prefix" class="form-control" value="<?= e($settings['patient_prefix'] ?? 'PT') ?>"></div>
              <div class="cp-form-group mb-0"><label>Invoice Terms</label><textarea name="invoice_terms" class="form-control" rows="3"><?= e($settings['invoice_terms'] ?? '') ?></textarea></div>
            </div>
          </div>
          <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-check-circle me-2"></i>Save Settings</button>
        </div>
      </div>
    </form>
  </div>

  <!-- PREFERENCES -->
  <div class="tab-pane" id="preferences">
    <div class="cp-card"><div class="cp-card-body text-center py-5 text-muted"><i class="bi bi-gear" style="font-size:40px"></i><p class="mt-2">Advanced preferences coming soon.</p></div></div>
  </div>

  <!-- USERS -->
  <div class="tab-pane" id="users">
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="cp-card">
          <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-people me-2 text-primary"></i>System Users</span></div>
          <div style="overflow-x:auto">
            <table class="cp-table">
              <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                  <td class="fw-600"><?= e($u['name']) ?></td>
                  <td><?= e($u['email']) ?></td>
                  <td><span class="badge bg-light text-dark"><?= e($u['role_name']) ?></span></td>
                  <td><span class="cp-badge" style="background:<?= $u['status']==='active'?'#e6faf6':'#ffeaea' ?>;color:<?= $u['status']==='active'?'var(--cp-success)':'var(--cp-danger)' ?>"><?= ucfirst($u['status']) ?></span></td>
                  <td><small><?= $u['last_login_at'] ? format_datetime($u['last_login_at']) : 'Never' ?></small></td>
                  <td>
                    <?php if ($u['id'] !== auth()['id']): ?>
                    <button onclick="toggleUser(<?= $u['id'] ?>)" class="btn btn-sm btn-outline-<?= $u['status']==='active'?'warning':'success' ?>" style="border-radius:8px;font-size:11px">
                      <?= $u['status']==='active' ? 'Disable' : 'Enable' ?>
                    </button>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="cp-card">
          <div class="cp-card-header"><span class="cp-card-title">Add User</span></div>
          <div class="cp-card-body">
            <form method="POST" action="<?= APP_URL ?>/settings.php?action=createUser">
              <?= csrf_field() ?>
              <div class="cp-form-group"><label>Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
              <div class="cp-form-group"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
              <div class="cp-form-group"><label>Phone</label><input type="tel" name="phone" class="form-control"></div>
              <div class="cp-form-group"><label>Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                  <?php foreach ($roles as $r): ?>
                  <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="cp-form-group mb-3"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="6"></div>
              <button type="submit" class="btn-cp-primary w-100"><i class="bi bi-person-plus me-2"></i>Create User</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
$scripts = <<<'JS'
<script>
// Check if #users hash in URL
if (window.location.hash === '#users') {
  document.querySelector('[href="#users"]').click();
}
function toggleUser(id) {
  const fd = new FormData();
  fd.append('_csrf', CSRF);
  fd.append('id', id);
  fetch('settings.php?action=toggleUser', { method:'POST', body:fd })
    .then(r=>r.json()).then(() => location.reload());
}
</script>
JS;
include VIEW_PATH . '/layouts/app.php';
?>
