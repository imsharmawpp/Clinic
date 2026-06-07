<?php
ob_start();
$pageTitle = 'OPD Visit';
?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= APP_URL ?>/opd.php" class="btn btn-outline-secondary" style="border-radius:10px"><i class="bi bi-arrow-left"></i></a>
  <div><h4 class="fw-700 mb-0">New OPD Visit</h4><small class="text-muted">Record patient consultation</small></div>
</div>

<form method="POST" action="<?= APP_URL ?>/opd.php?action=store">
  <?= csrf_field() ?>
  <input type="hidden" name="appointment_id" value="<?= e($appointmentId) ?>">

  <div class="row g-3">
    <div class="col-lg-8">
      <!-- Patient & Doctor -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-person me-2 text-primary"></i>Patient & Doctor</span></div>
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
                <option value="<?= $d['id'] ?>" <?= $d['id']==$doctorId?'selected':'' ?>><?= e($d['name']) ?> — <?= e($d['specialization']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 cp-form-group mb-0">
              <label>Visit Date</label>
              <input type="date" name="visit_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Vitals -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-heart-pulse me-2 text-danger"></i>Vitals</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-3 cp-form-group mb-0"><label>BP (mmHg)</label><input type="text" name="vitals_bp" class="form-control" placeholder="120/80"></div>
            <div class="col-md-3 cp-form-group mb-0"><label>Pulse (bpm)</label><input type="number" name="vitals_pulse" class="form-control" placeholder="72" min="0" max="300"></div>
            <div class="col-md-3 cp-form-group mb-0"><label>Temperature (°C)</label><input type="number" name="vitals_temp" class="form-control" placeholder="98.6" step="0.1"></div>
            <div class="col-md-3 cp-form-group mb-0"><label>SpO2 (%)</label><input type="number" name="vitals_spo2" class="form-control" placeholder="98" min="0" max="100"></div>
            <div class="col-md-3 cp-form-group mb-0"><label>Weight (kg)</label><input type="number" name="vitals_weight" class="form-control" placeholder="70" step="0.1"></div>
            <div class="col-md-3 cp-form-group mb-0"><label>Height (cm)</label><input type="number" name="vitals_height" class="form-control" placeholder="170" step="0.1"></div>
            <div class="col-md-3 cp-form-group mb-0"><label>RR (/min)</label><input type="number" name="vitals_rr" class="form-control" placeholder="18" min="0" max="60"></div>
          </div>
        </div>
      </div>

      <!-- Clinical Notes -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-clipboard2-pulse me-2 text-success"></i>Clinical Notes</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-12 cp-form-group">
              <label>Chief Complaint</label>
              <textarea name="chief_complaint" class="form-control" rows="2" placeholder="Patient's main complaint..."></textarea>
            </div>
            <div class="col-12 cp-form-group">
              <label>Symptoms</label>
              <textarea name="symptoms" class="form-control" rows="2" placeholder="Present symptoms..."></textarea>
            </div>
            <div class="col-12 cp-form-group">
              <label>Examination Findings</label>
              <textarea name="examination" class="form-control" rows="3" placeholder="Clinical examination findings..."></textarea>
            </div>
            <div class="col-12 cp-form-group">
              <label>Diagnosis</label>
              <textarea name="diagnosis" class="form-control" rows="2" placeholder="Clinical diagnosis..."></textarea>
            </div>
            <div class="col-12 cp-form-group">
              <label>Treatment Plan</label>
              <textarea name="treatment_plan" class="form-control" rows="3" placeholder="Treatment plan and instructions..."></textarea>
            </div>
            <div class="col-12 cp-form-group mb-0">
              <label>Additional Notes</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Follow-up -->
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title"><i class="bi bi-calendar-check me-2 text-warning"></i>Follow-up</span></div>
        <div class="cp-card-body">
          <div class="row g-3">
            <div class="col-md-4 cp-form-group mb-0">
              <label>Follow-up Date</label>
              <input type="date" name="follow_up_date" class="form-control">
            </div>
            <div class="col-md-8 cp-form-group mb-0">
              <label>Follow-up Instructions</label>
              <input type="text" name="follow_up_notes" class="form-control" placeholder="Instructions for follow-up...">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <?php if ($patient): ?>
      <div class="cp-card mb-3">
        <div class="cp-card-header"><span class="cp-card-title">Patient Info</span></div>
        <div class="cp-card-body">
          <div class="fw-700"><?= e($patient['name']) ?></div>
          <div class="text-muted small"><?= e($patient['patient_id']) ?></div>
          <div class="mt-2 d-flex gap-2 flex-wrap">
            <span class="cp-badge" style="background:#e8f0ff;color:var(--cp-primary)"><?= ucfirst($patient['gender']) ?></span>
            <span class="cp-badge" style="background:#e6faf6;color:var(--cp-success)"><?= e($patient['blood_group']) ?></span>
            <span class="cp-badge" style="background:#fff8e6;color:#cc8800"><?= age_from_dob($patient['dob']) ?? $patient['age'] ?? '?' ?> yrs</span>
          </div>
          <div class="mt-2"><i class="bi bi-telephone me-1"></i><?= e($patient['phone']) ?></div>
        </div>
      </div>
      <?php endif; ?>

      <div class="cp-card">
        <div class="cp-card-header"><span class="cp-card-title">Actions</span></div>
        <div class="cp-card-body d-grid gap-2">
          <button type="submit" class="btn-cp-primary"><i class="bi bi-check-circle me-2"></i>Save OPD Visit</button>
          <a href="<?= APP_URL ?>/opd.php" class="btn-cp-outline text-center text-decoration-none">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php
$content = ob_get_clean();
$scripts = '<script>
const ps = document.getElementById("patientSearch");
const ph = document.getElementById("patientId");
if (ps) patientSearch(ps, ph);
</script>';
include VIEW_PATH . '/layouts/app.php';
?>
