<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — ClinicPro</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
<style>
:root {
  --primary: #1a6eff;
  --sidebar: #0b1120;
  --secondary: #0dcfb4;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DM Sans', sans-serif; background: #0b1120; min-height: 100vh; display: flex; }

.login-left {
  flex: 1; background: linear-gradient(135deg, #0b1120 0%, #1a2a4a 100%);
  display: flex; flex-direction: column; justify-content: center; align-items: flex-start;
  padding: 60px; position: relative; overflow: hidden;
}
.login-left::before {
  content: ''; position: absolute; top: -100px; right: -100px;
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(26,110,255,.25) 0%, transparent 70%);
  border-radius: 50%;
}
.login-left::after {
  content: ''; position: absolute; bottom: -80px; left: 100px;
  width: 300px; height: 300px;
  background: radial-gradient(circle, rgba(13,207,180,.15) 0%, transparent 70%);
  border-radius: 50%;
}
.login-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 48px; z-index: 1; }
.login-brand .brand-icon {
  width: 52px; height: 52px; background: var(--primary);
  border-radius: 14px; display: grid; place-items: center;
  font-size: 26px; color: #fff;
}
.login-brand .brand-name { font-family: 'Playfair Display', serif; font-size: 30px; color: #fff; }
.login-hero h1 { font-family: 'Playfair Display', serif; font-size: 44px; color: #fff; line-height: 1.15; margin-bottom: 16px; z-index: 1; position: relative; }
.login-hero h1 span { color: var(--secondary); }
.login-hero p { color: rgba(255,255,255,.55); font-size: 15px; line-height: 1.7; max-width: 380px; z-index: 1; position: relative; }
.features-list { margin-top: 40px; z-index: 1; position: relative; }
.feature-item { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; color: rgba(255,255,255,.75); font-size: 14px; }
.feature-item i { font-size: 18px; color: var(--secondary); }

.login-right {
  width: 480px; background: #f0f4fb; display: flex;
  align-items: center; justify-content: center; padding: 48px 44px;
}
.login-box { width: 100%; }
.login-box h2 { font-size: 26px; font-weight: 700; color: #1e2d45; margin-bottom: 6px; }
.login-box .subtitle { color: #6b7a99; font-size: 14px; margin-bottom: 32px; }
.form-label { font-size: 13px; font-weight: 600; color: #1e2d45; margin-bottom: 6px; }
.form-control {
  border: 1.5px solid #e2e8f5; border-radius: 10px; padding: 11px 14px;
  font-size: 14px; font-family: 'DM Sans', sans-serif; color: #1e2d45;
  background: #fff; transition: border-color .2s, box-shadow .2s;
}
.form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,110,255,.1); outline: none; }
.input-group .input-group-text {
  background: #fff; border: 1.5px solid #e2e8f5; border-left: none;
  border-radius: 0 10px 10px 0; cursor: pointer; color: #6b7a99;
}
.input-group .form-control { border-radius: 10px 0 0 10px; border-right: none; }
.btn-login {
  width: 100%; background: var(--primary); color: #fff; border: none;
  border-radius: 10px; padding: 13px; font-size: 15px; font-weight: 600;
  font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all .2s;
  margin-top: 8px;
}
.btn-login:hover { background: #0d4fc4; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,110,255,.3); }
.demo-box { background: #e8f0ff; border-radius: 10px; padding: 14px 16px; margin-top: 20px; font-size: 13px; color: #1a6eff; }
.alert-flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
.alert-flash.error { background: #ffeaea; color: #dc3545; border-left: 4px solid #dc3545; }

@media (max-width: 768px) {
  .login-left { display: none; }
  .login-right { width: 100%; padding: 32px 24px; background: #fff; }
}
</style>
</head>
<body>

<div class="login-left">
  <div class="login-brand">
    <div class="brand-icon"><i class="bi bi-hospital"></i></div>
    <div class="brand-name">ClinicPro</div>
  </div>
  <div class="login-hero">
    <h1>Complete Clinic<br>Management <span>System</span></h1>
    <p>Streamline your clinic operations with smart scheduling, billing, prescriptions, and analytics — all in one platform.</p>
    <div class="features-list">
      <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Smart Appointment Scheduling</div>
      <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Digital Prescriptions & OPD</div>
      <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Billing with GST & Payments</div>
      <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Lab & Pharmacy Management</div>
      <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Reports & Analytics</div>
    </div>
  </div>
</div>

<div class="login-right">
  <div class="login-box">
    <h2>Welcome Back</h2>
    <p class="subtitle">Sign in to your clinic account</p>

    <?php foreach (get_flash() as $f): ?>
    <div class="alert-flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="<?= APP_URL ?>/login.php" id="loginForm">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="admin@clinic.com" required autofocus value="<?= e($_GET['demo'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Enter your password" required>
          <span class="input-group-text" onclick="togglePwd()"><i class="bi bi-eye" id="eyeIcon"></i></span>
        </div>
      </div>
      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>

    <div class="demo-box">
      <i class="bi bi-info-circle me-1"></i>
      <strong>Demo Credentials:</strong><br>
      Email: <code>admin@democlinic.com</code><br>
      Password: <code>Admin@1234</code>
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const i = document.getElementById('passwordInput');
  const e = document.getElementById('eyeIcon');
  if (i.type === 'password') { i.type='text'; e.className='bi bi-eye-slash'; }
  else { i.type='password'; e.className='bi bi-eye'; }
}
</script>
</body>
</html>
