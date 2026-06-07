<?php
ob_start();
$pageTitle = isset($patient) ? 'Edit Patient' : 'New Patient';
$isEdit    = isset($patient);
$action    = $isEdit ? APP_URL.'/patients.php?action=update&id='.$patient['id'] : APP_URL.'/patients.php?action=store';
?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/patients.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div>
    <h4 class="fw-700 mb-0"><?= $pageTitle ?></h4>
    <small class="text-muted"><?= $isEdit ? 'Update patient information' : 'Register a new patient' ?></small>
  </div>
</div>

<form method="POST" action="<?= $action ?>">
  <?= csrf_field() ?>

  <div class="row g-3">
    <!-- Personal Info -->
    <div class="col-lg-8">
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-person me-2 text-primary"></i>Personal Information</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-6 cp-form-group">
              <label>Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="<?= e($patient['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 cp-form-group">
              <label>Phone <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control" value="<?= e($patient['phone'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 cp-form-group">
              <label>Email</label>
              <input type="email" name="email" class="form-control" value="<?= e($patient['email'] ?? '') ?>">
            </div>
            <div class="col-md-3 cp-form-group">
              <label>Date of Birth</label>
              <input type="date" name="dob" class="form-control" value="<?= e($patient['dob'] ?? '') ?>">
            </div>
            <div class="col-md-3 cp-form-group">
              <label>Age (if DOB unknown)</label>
              <input type="number" name="age" class="form-control" value="<?= e($patient['age'] ?? '') ?>" min="0" max="150">
            </div>
            <div class="col-md-4 cp-form-group">
              <label>Gender <span class="text-danger">*</span></label>
              <select name="gender" class="form-select">
                <option value="male"   <?= ($patient['gender']??'')==='male'   ?'selected':'' ?>>Male</option>
                <option value="female" <?= ($patient['gender']??'')==='female' ?'selected':'' ?>>Female</option>
                <option value="other"  <?= ($patient['gender']??'')==='other'  ?'selected':'' ?>>Other</option>
              </select>
            </div>
            <div class="col-md-4 cp-form-group">
              <label>Blood Group</label>
              <select name="blood_group" class="form-select">
                <?php foreach (['unknown','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                <option value="<?= $bg ?>" <?= ($patient['blood_group']??'unknown')===$bg?'selected':'' ?>><?= $bg ==='unknown'?'Unknown':$bg ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Address -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-geo-alt me-2 text-success"></i>Address</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-12 cp-form-group">
              <label>Street Address</label>
              <textarea name="address" class="form-control" rows="2"><?= e($patient['address'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4 cp-form-group">
              <label>City</label>
              <input type="text" name="city" class="form-control" value="<?= e($patient['city'] ?? '') ?>">
            </div>
            <div class="col-md-4 cp-form-group">
              <label>State</label>
              <input type="text" name="state" class="form-control" value="<?= e($patient['state'] ?? '') ?>">
            </div>
            <div class="col-md-4 cp-form-group">
              <label>Pincode</label>
              <input type="text" name="pincode" class="form-control" value="<?= e($patient['pincode'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="cp-card">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-journal me-2 text-info"></i>Notes</span></div>
        <div class="cp-card-body cp-form-group mb-0">
          <textarea name="notes" class="form-control" rows="3" placeholder="Medical notes, allergies, special considerations..."><?= e($patient['notes'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Right sidebar -->
    <div class="col-lg-4">
      <!-- Emergency Contact -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-telephone-fill me-2 text-danger"></i>Emergency Contact</span></div>
        <div class="cp-card-body">
          <div class="cp-form-group">
            <label>Contact Name</label>
            <input type="text" name="emergency_name" class="form-control" value="<?= e($patient['emergency_name'] ?? '') ?>">
          </div>
          <div class="cp-form-group">
            <label>Relation</label>
            <input type="text" name="emergency_relation" class="form-control" placeholder="Spouse, Parent..." value="<?= e($patient['emergency_relation'] ?? '') ?>">
          </div>
          <div class="cp-form-group mb-0">
            <label>Phone</label>
            <input type="tel" name="emergency_phone" class="form-control" value="<?= e($patient['emergency_phone'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Insurance -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-shield-check me-2 text-warning"></i>Insurance</span></div>
        <div class="cp-card-body">
          <div class="cp-form-group">
            <label>Provider</label>
            <input type="text" name="insurance_provider" class="form-control" value="<?= e($patient['insurance_provider'] ?? '') ?>">
          </div>
          <div class="cp-form-group">
            <label>Policy Number</label>
            <input type="text" name="insurance_number" class="form-control" value="<?= e($patient['insurance_number'] ?? '') ?>">
          </div>
          <div class="cp-form-group mb-0">
            <label>Expiry Date</label>
            <input type="date" name="insurance_expiry" class="form-control" value="<?= e($patient['insurance_expiry'] ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="d-grid gap-2">
        <button type="submit" class="btn-cp-primary">
          <i class="bi bi-<?= $isEdit?'check-circle':'person-plus' ?> me-2"></i>
          <?= $isEdit ? 'Update Patient' : 'Register Patient' ?>
        </button>
        <a href="<?= APP_URL ?>/patients.php" class="btn-cp-outline text-center text-decoration-none">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php $content = ob_get_clean(); include VIEW_PATH . '/layouts/app.php'; ?>
